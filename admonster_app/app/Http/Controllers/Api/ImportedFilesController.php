<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequestFile;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\OrderDetail;
use App\Models\OrderFileImportMainConfig;
use App\Models\OrderFileImportColumnConfig;
use App\Models\OrderDetailValue;
use App\Models\User;
use App\Models\Business;
use App\Models\Step;
use App\Models\Request as RequestModel;
use App\Models\RequestAdditionalInfo;
use App\Models\RequestAdditionalInfoAttachment;
use App\Models\RequestWork;
use App\Models\Task;
use App\Models\FileImportMainConfig;
use App\Models\Label;
use App\Models\Language;
use Carbon\Carbon;
use Storage;
use Illuminate\Http\JsonResponse;
use App\Services\Traits\RequestLogStoreTrait;
use App\Services\UploadFileManager\Uploader;
use App\Services\FormatCheckManager\ItemType\TextFormatCheck;
use App\Services\FormatCheckManager\ItemType\DateFormatCheck;
use App\Services\FormatCheckManager\ItemType\NumFormatCheck;
use App\Imports\RequestFileImport;
use App\Exceptions\ExclusiveException;
use DB;
use Validator;

class ImportedFilesController extends Controller
{
    use RequestLogStoreTrait;

    public function index(Request $req)
    {
        $user = \Auth::user();

        // 検索条件の取得
        $form = [
            'imported_file_name' => $req->input('imported_file_name'),
            'imported_file_id' => $req->input('imported_file_id'),
            'business_name' => $req->input('business_name'),
            'from' => $req->input('from'),
            'to' => $req->input('to'),
            'importer' => $req->input('importer'),
            'status' => $req->get('status'),
            'page' => $req->input('page'),
            'sort_by' => $req->get('sort_by'),
            'descending' => $req->get('descending') ? 'desc' : 'asc',
            'rows_per_page' => $req->get('rows_per_page'),
        ];

        $list = RequestFile::getList($user->id, $form);

        // 全ユーザ情報を保持
        $candidates = User::select(
            'users.id',
            'users.name',
            'users.user_image_path'
        )->get();

        return response()->json([
            'list' => $list,
            'candidates' => $candidates,
        ]);
    }

    public function latest(Request $req)
    {
        $user = \Auth::user();

        // TODO 最新ファイル一覧での上限数を確定させる。
        $latest_request_files = RequestFile::getLatestListByUserId($user->id, 5);

        return response()->json([
            'latest_request_files' => $latest_request_files
        ]);
    }

    public function store(Request $req)
    {
        $user = \Auth::user();

        // form
        $business_id = $req->business_id;
        $step_id = $req->step_id;
        $file_name = $req->tmp_file_info['file_name'];
        $tmp_file_dir = $req->tmp_file_info['tmp_file_dir'];
        $tmp_file_path = $req->tmp_file_info['tmp_file_path'];
        $rows = $req->rows;

        // 取込設定取得
        $main_config = FileImportMainConfig::with('fileImportColumnConfigs')->where('step_id', $step_id)->first();
        $column_configs = $main_config->fileImportColumnConfigs;

        // DB登録
        \DB::beginTransaction();
        try {
            // 一時ファイルをS3に保存
            $file_path = 'request_files/'. $step_id .'/'. Carbon::now()->format('Ymd') .'/'. md5(microtime()) .'/'. $file_name;
            $tmp_file = Storage::drive()->get($tmp_file_path);
            $s3_file_path = Uploader::uploadToS3($tmp_file, $file_path);

            // request_filesに登録
            $request_file = new RequestFile;
            $request_file->step_id = $step_id;
            $request_file->create_status = 9; // TODO: confで定数管理
            $request_file->name = $file_name;
            $request_file->file_path = $file_path;
            $request_file->created_user_id = $user->id;
            $request_file->updated_user_id = $user->id;
            $request_file->save();

            if ($s3_file_path && $request_file->file_path) {
                // 一時ファイルをディレクトリごと削除
                Storage::deleteDirectory($tmp_file_dir);
            }

            // 1行ずつを依頼として登録
            foreach ($rows as $row) {
                if ($row['is_org_header_row']) {
                    continue;
                }

                $request_info = [
                    'client_name' => '',
                    'deadline' => '',
                ];

                $items = $row['data'];
                $content = [];
                $subject_parts = [];
                foreach ($items as $item) {
                    foreach ($item as $key => $value) {
                        $item_key = '';
                        foreach ($column_configs as $column_config) {
                            if ($column_config['id'] === $key) {
                                $item_key = $column_config['item_key'];
                                break;
                            }
                        }

                        $content[$item_key] = $value['value'];

                        // アイテムから依頼情報を取得
                        if ($value['request_info_type']) {
                            $request_info = $this->getRequestInfoByType($request_info, $value['request_info_type'], $value['value']);
                        }

                        if ($value['subject_part_no']) {
                            $subject_parts[] = [
                                'no' => $value['subject_part_no'],
                                'value' => $value['value'],
                            ];
                        }
                    }
                }

                // 依頼の件名を生成
                $request_name = '';
                if (count($subject_parts) > 0) {
                    $sort = [];
                    foreach ((array) $subject_parts as $key => $value) {
                        $sort[$key] = $value['no'];
                    }
                    array_multisort($sort, SORT_ASC, $subject_parts);
                    $subject_parts = array_column($subject_parts, 'value');
                    $request_name = implode($main_config->subject_delimiter, $subject_parts);
                } else {
                    $request_name = $file_name.'_'.$row['row_num'].'行目';
                }

                // 納期：ファイル内に納期となる列の指定がなければマスタの納期を設定
                $deadline = '';
                if ($request_info['deadline']) {
                    $deadline = $request_info['deadline'];
                } else {
                    $deadline_limit = Step::find($step_id)->deadline_limit;
                    $deadline = new Carbon('+'.Step::find($step_id)->deadline_limit.' days');
                }

                // requestsに登録
                $request = new RequestModel;
                $request->name = $request_name;  // TODO: rowの対象データから設定のマッピングを経由して取得?
                $request->business_id = $business_id;
                $request->client_name = $request_info['client_name'] ? $request_info['client_name'] : $user->name;
                $request->deadline = $deadline;
                $request->system_deadline = $deadline;
                $request->status = \Config::get('const.REQUEST_STATUS.DOING');
                $request->created_user_id = $user->id;
                $request->updated_user_id = $user->id;
                $request->save();

                // request_worksに登録
                $request_work = new RequestWork;
                // $request_work->code = ; // TODO: rowの対象データから設定のマッピングを経由して取得
                $request_work->name = $request->name;
                $request_work->request_id = $request->id;
                $request_work->step_id = $step_id;
                $request_work->client_name = $request->client_name;
                // $request_work->from = ; // TODO: rowの対象データから設定のマッピングを経由して取得
                // $request_work->to = ; // TODO: rowの対象データから設定のマッピングを経由して取得
                $request_work->deadline = $deadline;
                $request_work->system_deadline = $deadline;
                $request_work->created_user_id = $user->id;
                $request_work->updated_user_id = $user->id;
                $request_work->save();

                // request_work_filesに登録
                // 行データからjsonを生成
                $content = json_encode($content, JSON_UNESCAPED_UNICODE);
                $request_work->requestFiles()->attach($request_file->id, [
                    'content' => $content,
                    'row_no' => $row['row_num'],
                    'created_at' => Carbon::now(),
                    'created_user_id' => $user->id,
                    'updated_at' => Carbon::now(),
                    'updated_user_id' => $user->id,
                ]);

                // 依頼の補足情報を登録
                $request_additional_info = new RequestAdditionalInfo;
                $request_additional_info->request_id = $request->id;
                $request_additional_info->content = __('requests.detail.additional_info.auto_comments.imported_by_file').'<a href="/imported_files?imported_file_id='.$request_file->id.'">'.__('requests.detail.additional_info.auto_comments.confirm_at_imported_file_list').'</a>';
                $request_additional_info->created_user_id = $user->id;
                $request_additional_info->updated_user_id = $user->id;
                $request_additional_info->save();

                // 補足情報ファイルを登録
                $request_additional_info_attachment = new RequestAdditionalInfoAttachment;
                $request_additional_info_attachment->request_additional_info_id = $request_additional_info->id;
                $request_additional_info_attachment->name = $request_file->name;
                $request_additional_info_attachment->file_path = $request_file->file_path;
                $request_additional_info_attachment->created_user_id = $user->id;
                $request_additional_info_attachment->updated_user_id = $user->id;
                $request_additional_info_attachment->save();

                // ログ登録(取込)
                $request_log_attributes = [
                    'request_id' => $request->id,
                    'type' => \Config::get('const.REQUEST_LOG_TYPE.IMPORT_COMPLETED'),
                    'request_work_id' => $request_work->id,
                    'created_user_id' => $user->id,
                    'updated_user_id' => $user->id,
                ];
                $this->storeRequestLog($request_log_attributes);
            }

            \DB::commit();

            return response()->json([
                'result' => 'success',
                'request_file_id' => $request_file->id,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
            ]);
        }
    }

    /**
     * 案件ファイルを取り込む
     *
     * @param Request $req リクエスト
     * @return JsonResponse
     */
    public function orderStore(Request $req): JsonResponse
    {
        $user = \Auth::user();

        try {
            // form
            $subjects = json_decode($req->input('subjects'), true) ?? [];
            $tmp_file_info = json_decode($req->input('tmp_file_info'), true) ?? [];
            $items = json_decode($req->input('items'), true) ?? [];
            $file_name = $tmp_file_info['file_name'];
            $tmp_file_dir = $tmp_file_info['tmp_file_dir'];
            $tmp_file_path = $tmp_file_info['tmp_file_path'];
            $rows = json_decode($req->input('rows'), true) ?? [];
            $header_row_no = $req->input('header_row_no') ?? 1;
            $data_start_row_no = $req->input('data_start_row_no') ?? 2;
            $setting_rules = json_decode($req->input('setting_rules'), true) ?? [];
            $order_name = json_decode($req->input('order_name'), true) ?? [];
            $sheet_name = json_decode($req->input('sheet_name'), true) ?? 0;

            \DB::beginTransaction();

            // 一時ファイルをS3に保存
            $file_path = 'order_files/' . Carbon::now()->format('Ymd') . '/' . md5(microtime()) . '/' . $file_name;
            $tmp_file = Storage::drive()->get($tmp_file_path);
            $order_file = Uploader::tryUploadAndSave($tmp_file, $file_path, OrderFile::class);

            $register_time = $order_file->created_at;

            // orderに登録
            $order = new Order;
            $order->name = $order_name;
            $order->created_at = $register_time;
            $order->created_user_id = $user->id;
            $order->updated_at = $register_time;
            $order->updated_user_id = $user->id;
            $order->save();

            // OrderFileImportMainConfig
            $disk = Storage::disk('local');
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader = $reader->load($disk->path($tmp_file_path));
            $sheet = $sheet_name ? $reader->getSheetByName($sheet_name) : $reader->getSheet(0);
            $title = $sheet->getTitle();

            $order_file_import_main_config = new OrderFileImportMainConfig;
            $order_file_import_main_config->sheet_name = $title;
            $order_file_import_main_config->header_row_no = $header_row_no;
            $order_file_import_main_config->data_start_row_no = $data_start_row_no;
            $order_file_import_main_config->start_column = 'A';
            $order_file_import_main_config->created_at = $register_time;
            $order_file_import_main_config->created_user_id = $user->id;
            $order_file_import_main_config->updated_at = $register_time;
            $order_file_import_main_config->updated_user_id = $user->id;
            $order_file_import_main_config->save();

            // OrdersOrderFiles
            Order::find($order->id)
                ->orderFiles()
                ->attach($order_file->id, [
                    'import_type' => \Config::get('const.FILE_IMPORT_TYPE.NEW'),
                    'created_at' => $register_time,
                    'created_user_id' => $user->id,
                    'updated_at' => $register_time,
                    'updated_user_id' => $user->id
                ]);

            // OrdersAdministrators
            Order::find($order->id)
                ->administrators()
                ->attach($user->id, [
                    'created_at' => $register_time,
                    'created_user_id' => $user->id,
                    'updated_at' => $register_time,
                    'updated_user_id' => $user->id
                ]);

            // OrderFilesOrderFileImportMainConfigs
            OrderFile::find($order_file->id)
                ->orderFileImportMainConfigs()
                ->attach($order_file_import_main_config->id, [
                    'created_at' => $register_time,
                    'created_user_id' => $user->id,
                    'updated_at' => $register_time,
                    'updated_user_id' => $user->id
                ]);

            if ($order_file->file_path) {
                // 一時ファイルをディレクトリごと削除
                Storage::delete($tmp_file_path);
                Storage::deleteDirectory($tmp_file_dir);
            }

            $language = DB::table('languages')
                ->where('languages.code', 'ja')
                ->first();

            $sort = 1;
            $order_file_import_column_config_ids = [];
            foreach ($items as $key => $item) {
                // label
                $label = new Label;
                $label->language_id = $language->id;
                $label->name = $item['display'];
                $label->created_at = $register_time;
                $label->created_user_id = $user->id;
                $label->updated_at = $register_time;
                $label->updated_user_id = $user->id;
                $label->save();

                $subject_part_no = null;
                foreach ($subjects as $subject_key => $subject) {
                    if ($subject === $item['row']) {
                        $subject_part_no = $subject_key + 1;
                    }
                }

                $display_format = array_key_exists($key, $setting_rules) &&
                    array_key_exists('displayRule', $setting_rules[$key]) &&
                    intval($setting_rules[$key]['inputRule']['selectType']) === intval($item['dataType']) ? array_sum($setting_rules[$key]['displayRule']) : 0;

                // OrderFileImportColumnConfig
                $order_file_import_column_config = new OrderFileImportColumnConfig;
                $order_file_import_column_config->order_file_import_main_config_id = $order_file_import_main_config->id;
                $order_file_import_column_config->column = $item['row'];
                $order_file_import_column_config->item = $item['itemName'];
                $order_file_import_column_config->label_id = $label->id;
                $order_file_import_column_config->item_type = $item['dataType'];
                $order_file_import_column_config->rule = array_key_exists($key, $setting_rules) && !is_null($setting_rules[$key]['inputRule']) &&
                    intval($setting_rules[$key]['inputRule']['selectType']) === intval($item['dataType']) ? json_encode($setting_rules[$key]['inputRule']) : null;
                $order_file_import_column_config->display_format = $display_format;
                $order_file_import_column_config->sort = $sort;
                $order_file_import_column_config->subject_part_no = $subject_part_no;
                $order_file_import_column_config->created_at = $register_time;
                $order_file_import_column_config->created_user_id = $user->id;
                $order_file_import_column_config->updated_at = $register_time;
                $order_file_import_column_config->updated_user_id = $user->id;
                $order_file_import_column_config->save();
                $order_file_import_column_config_ids[$item['row']] = $order_file_import_column_config->id;

                $sort += 1;
            }

            foreach ($rows as $row) {
                // OrderDetail
                $order_detail = new OrderDetail;
                $order_detail->order_id = $order->id;
                $order_detail->name = $row['subject'];
                $order_detail->created_at = $register_time;
                $order_detail->created_user_id = $user->id;
                $order_detail->updated_at = $register_time;
                $order_detail->updated_user_id = $user->id;
                $order_detail->save();

                foreach ($row['data'] as $data) {
                    foreach ($data as $data_key => $data_value) {
                        // OrderDetailValue
                        $order_detail_value = new OrderDetailValue;
                        $order_detail_value->order_detail_id = $order_detail->id;
                        $order_detail_value->value = $data_value['value'];
                        $order_detail_value->created_at = $register_time;
                        $order_detail_value->created_user_id = $user->id;
                        $order_detail_value->updated_at = $register_time;
                        $order_detail_value->updated_user_id = $user->id;
                        $order_detail_value->save();

                        // OrderFileImportColumnConfigsOrderDetailValues
                        OrderFileImportColumnConfig::find($order_file_import_column_config_ids[$data_key])
                            ->orderDetailValues()
                            ->attach($order_detail_value->id, [
                                'created_at' => $register_time,
                                'created_user_id' => $user->id,
                                'updated_at' => $register_time,
                                'updated_user_id' => $user->id
                            ]);
                    }
                }
            }
            \DB::commit();

            return response()->json([
                'state' => 200,
                'order_id' => $order->id,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 案件明細を追加する案件ファイルを取り込む
     *
     * @param Request $req リクエスト
     * @return JsonResponse
     */
    public function addOrderDetail(Request $req): JsonResponse
    {
        $user = \Auth::user();

        try {
            // form
            $tmp_file_info = json_decode($req->input('tmp_file_info'), true) ?? [];
            $items = json_decode($req->input('items'), true) ?? [];
            $file_name = $tmp_file_info['file_name'];
            $tmp_file_dir = $tmp_file_info['tmp_file_dir'];
            $tmp_file_path = $tmp_file_info['tmp_file_path'];
            $rows = json_decode($req->input('rows'), true) ?? [];
            $order_id = $req->input('order_id');

            \DB::beginTransaction();

            // 一時ファイルをS3に保存
            $file_path = 'order_files/' . Carbon::now()->format('Ymd') . '/' . md5(microtime()) . '/' . $file_name;
            $tmp_file = Storage::drive()->get($tmp_file_path);
            $order_file = Uploader::tryUploadAndSave($tmp_file, $file_path, OrderFile::class);

            $register_time = $order_file->created_at;

            /** @var Order|null */
            $order = Order::find($order_id);
            if (is_null($order)) {
                throw new \Exception('Not found Order id: ' . $order_id);
            }

            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }

            // Associate "Order" with "OrderFile"
            $order->orderFiles()
                ->attach($order_file->id, [
                    'import_type' => \Config::get('const.FILE_IMPORT_TYPE.ADD'),
                    'created_at' => $register_time,
                    'created_user_id' => $user->id,
                    'updated_at' => $register_time,
                    'updated_user_id' => $user->id
                ]);

            if ($order_file->file_path) {
                // 一時ファイルをディレクトリごと削除
                Storage::delete($tmp_file_path);
                Storage::deleteDirectory($tmp_file_dir);
            }

            $order_file_import_column_config_ids = [];
            foreach ($items as $item) {
                /** @var OrderFileImportColumnConfig|null */
                $order_file_import_column_config = OrderFileImportColumnConfig::find($item['id']);
                if (is_null($order_file_import_column_config)) {
                    throw new \Exception('Not found OrderFileImportColumnConfig id: ' . $item['id']);
                }
                $order_file_import_column_config_ids[$item['row']] = $order_file_import_column_config->id;
            }

            // add OrderDetail
            foreach ($rows as $row) {
                $order_detail = new OrderDetail;
                $order_detail->order_id = $order->id;
                $order_detail->name = $row['subject'];
                $order_detail->created_at = $register_time;
                $order_detail->created_user_id = $user->id;
                $order_detail->updated_at = $register_time;
                $order_detail->updated_user_id = $user->id;
                $order_detail->save();

                foreach ($row['data'] as $data) {
                    foreach ($data as $data_key => $data_value) {
                        $order_detail_value = new OrderDetailValue;
                        $order_detail_value->order_detail_id = $order_detail->id;
                        $order_detail_value->value = $data_value['value'];
                        $order_detail_value->created_at = $register_time;
                        $order_detail_value->created_user_id = $user->id;
                        $order_detail_value->updated_at = $register_time;
                        $order_detail_value->updated_user_id = $user->id;
                        $order_detail_value->save();

                        OrderFileImportColumnConfig::find($order_file_import_column_config_ids[$data_key])
                            ->orderDetailValues()
                            ->attach($order_detail_value->id, [
                                'created_at' => $register_time,
                                'created_user_id' => $user->id,
                                'updated_at' => $register_time,
                                'updated_user_id' => $user->id
                            ]);
                    }
                }
            }
            \DB::commit();

            return response()->json([
                'state' => 200,
                'order_id' => $order->id,
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'no_admin_permission',
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function delete(Request $req)
    {
        $user = \Auth::user();

        $tasks = DB::table('tasks')
            ->select(
                'tasks.*'
            )
            ->join('request_works', 'tasks.request_work_id', '=', 'request_works.id')
            ->join('request_work_files', 'request_work_files.request_work_id', '=', 'request_works.id')
            ->join('request_files', 'request_work_files.request_file_id', '=', 'request_files.id')
            ->where('request_files.id', $req->request_file_id)
            ->where('tasks.is_education', \Config::get('const.FLG.INACTIVE'))
            ->get();

        // 発生した依頼が進行していたら削除不可
        foreach ($tasks as $task) {
            if ($task->status !== \Config::get('const.TASK_STATUS.NONE')) {
                return response()->json([
                    'result' => 'not_allowed',
                    'error_text' => '進行中のタスクが存在するため削除できません',
                ]);
            }
        }

        // DB登録
        \DB::beginTransaction();
        try {
            //ファイル削除
            $request_file = RequestFile::find($req->request_file_id);
            $request_file->is_deleted = \Config::get('const.FLG.ACTIVE');
            $request_file->updated_at = Carbon::now();
            $request_file->updated_user_id = $user->id;
            $request_file->save();

            $request_works = $request_file->requestWorks;
            foreach ($request_works as $request_work) {
                $request_work->is_deleted = \Config::get('const.FLG.ACTIVE');
                $request_work->updated_at = Carbon::now();
                $request_work->updated_user_id = $user->id;
                $request_work->save();

                $request_work->load('task');
                $tasks = $request_work->task;
                foreach ($tasks as $task) {
                    $task->is_active = \Config::get('const.FLG.INACTIVE');
                    $task->updated_at = Carbon::now();
                    $task->updated_user_id = $user->id;
                    $task->save();
                }
            }

            $request = $request_works[0]->request;

            $request->status = \Config::get('const.REQUEST_STATUS.FINISH');
            $request->is_deleted = \Config::get('const.FLG.ACTIVE');
            $request->updated_at = Carbon::now();
            $request->updated_user_id = $user->id;
            $request->save();

            \DB::commit();

            return response()->json([
                'result' => 'success',
                'request_file_id' => $req->request_file_id,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
                'request_file_id' => $req->request_file_id,
            ]);
        }
    }

    public function tmpUpload(Request $req)
    {
        // ファイル保存
        $file = $req->upload_file;
        $root_folder = substr($file['file_path'], 0, strlen('request_files'));

        $tmp_file_info = '';
        // アップロードされていないものだけを保存
        if ($file['file_path'] == null || $root_folder !== 'request_files') {
            // ファイルのアップロード
            $upload_data = [
                'file' => $file
            ];
            $tmp_file_info = $this->tmpFileStore($upload_data, 'base64');
        }

        $result = '';
        if (is_array($tmp_file_info) && !empty($tmp_file_info)) {
            $result = 'success';
        } else {
            $result = 'error';
        }

        return response()->json([
            'result' => $result,
            'tmp_file_info' => $tmp_file_info,
        ]);
    }

    // ローカルの一時ファイルを削除
    public function tmpFileDelete(Request $req)
    {
        $tmp_file_dir = $req->tmp_file_info['file_dir'];
        $tmp_file_path = $req->tmp_file_info['file_path'];

        $tmp_file = Storage::drive()->get($tmp_file_path);
        if (empty($tmp_file)) {
            // 一時ファイルが見つからない場合はエラーを返す
        }

        // 一時ファイルをディレクトリごと削除
        $result = Storage::deleteDirectory($tmp_file_dir);

        return response()->json([
            'result' => $result
        ]);
    }

    // 確認画面用ファイルデータ取得
    public function readFile(Request $req)
    {
        $step_id = Step::getFirstStepInBusinnessByBusinnessId($req->business_id)->id;
        $tmp_file_info = $req->tmp_file_info;
        $tmp_file_path = $tmp_file_info['tmp_file_path'];

        if (!Storage::exists($tmp_file_path)) {
            // 一時ファイルが見つからない場合はエラーを返す
        }

        $disk = Storage::disk('local');
        $mime = $disk->mimeType($tmp_file_path);
        $ext_to_mimes = [
            "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        ];

        // ファイルタイプ別に読込処理
        $data = [];
        if ($mime === $ext_to_mimes['xlsx']) {
            $data = $this->readExcel($disk->path($tmp_file_path), $step_id);
        } else {
            //
        }

        return response()->json([
            'data' => $data,
            'step_id' => $step_id,
        ]);
    }

    /**
     * 確認画面の初期表示に必要となる情報を取得
     *
     * @param Request $req リクエスト
     * @return JsonResponse
     */
    public function getOrderFileInfo(Request $req): JsonResponse
    {
        try {
            $tmp_file_info = json_decode($req->input('tmp_file_info'), true) ?? [];
            $tmp_file_path = $tmp_file_info['tmp_file_path'];
            $setting_rules = json_decode($req->input('setting_rules'), true) ?? [];
            $items = json_decode($req->input('items'), true) ?? [];
            $header_row_no = $req->input('header_row_no') ?? 1;
            $data_start_row_no = $req->input('data_start_row_no') ?? 2;
            $subjects = json_decode($req->input('subjects'), true) ?? [];
            $sheet_name = json_decode($req->input('sheet_name'), true) ?? 0;
            $file_import_type = $req->input('file_import_type') ?? \Config::get('const.FILE_IMPORT_TYPE.NEW');

            if (!Storage::exists($tmp_file_path)) {
                // 一時ファイルが見つからない場合はエラーを返す
                throw new \Exception('File not exists.');
            }

            $disk = Storage::disk('local');
            $mime = $disk->mimeType($tmp_file_path);
            $ext_to_mimes = [
                "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            ];

            // ファイルタイプ別に読込処理
            $data = [];
            if ($mime === $ext_to_mimes['xlsx']) {
                $data = $this->readOrderExcel($disk->path($tmp_file_path), $setting_rules, $header_row_no, $data_start_row_no, $items, $subjects, $sheet_name, $file_import_type);
            }

            return response()->json([
                'data' => $data,
                'status' => 200,
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * エクセルファイルの内容を読み取る
     * @param string $path ファイルのパス
     * @param array $setting_rules データ入力ルール
     * @param int $header_row_no ヘッダー行
     * @param int $data_start_row_no データ開始行
     * @param array $items 設定画面の情報
     * @param array $subjects 設定画面で指定した件名
     * @param string $sheet_name 取り込みたいシートの名前
     * @param int $import_type ファイルの取込タイプ
     * @return array $data エクセルファイルの内容
     */
    private function readOrderExcel(string $path, array $setting_rules, int $header_row_no, int $data_start_row_no, array $items, array $subjects, string $sheet_name, int $import_type): array
    {
        /*
         * データ読込
         */
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader = $reader->load($path);

        // シート名の指定がなければ先頭のシートを読み込む
        $sheet = $sheet_name ? $reader->getSheetByName($sheet_name) : $reader->getSheet(0);
        $font = $sheet->getParent()->getDefaultStyle()->getFont();

        $headers = [];   // 表頭項目
        $rows = [];      // データ行
        $errors = [];    // エラー一覧用
        $row_nums = [];

        $row_iterators = $sheet->getRowIterator($data_start_row_no);

        /** @var array [itemName => cellColumn] */
        $itemNameToCellColumnArray = [];
        if ($import_type === \Config::get('const.FILE_IMPORT_TYPE.ADD')) {
            // エクセルから項目名とカラムの関係を作成
            $order_file_info = $this->readHeaderOrderFile($path, $header_row_no);
            $itemNameToCellColumnArray = array_column($order_file_info['data'], 'row', 'itemName');
        }

        // 行
        foreach ($row_iterators as $row) {
            $row_data = [];
            $row_data['row_num'] = $row->getRowIndex();
            $row_data['subject'] = '';
            $row_data['has_error'] = false;  // 初期値
            $row_nums[] = $row->getRowIndex();

            // 列：定義された列データを取得
            foreach ($items as $key => $column_config) {
                $column_width = $sheet->getColumnDimension($column_config['row'])->getWidth();
                $column_width = \PhpOffice\PhpSpreadsheet\Shared\Drawing::cellDimensionToPixels($column_width, $font);
                // 表頭用データをゲット（最初のデータ行から全部取っておく）
                if ($row->getRowIndex() === $data_start_row_no) {
                    $headers[] = [
                        'display' => $column_config['display'],
                        'row' => $column_config['row'],
                        'error' => false,
                        'width' => $column_width
                    ];
                }

                $rule = '';
                foreach (\Config::get('const.ITEM_TYPE') as $data_format) {
                    if (intval($column_config['dataType']) === $data_format['ID']) {
                        $rule = $data_format['RULE'];
                    }
                }

                // 表頭以外をバリデーション
                $isError = false;
                $cell_value = null;
                /** @var null|\PhpOffice\PhpSpreadsheet\Cell\Cell */
                $cell = null;
                switch ($import_type) {
                    case \Config::get('const.FILE_IMPORT_TYPE.NEW'):
                        $cell = $sheet->getCell($column_config['row'] . strval($row->getRowIndex()));
                        break;
                    case \Config::get('const.FILE_IMPORT_TYPE.ADD'):
                        if (array_key_exists($column_config['itemName'], $itemNameToCellColumnArray)) {
                            $cell = $sheet->getCell($itemNameToCellColumnArray[$column_config['itemName']] . strval($row->getRowIndex()));
                        }
                        break;
                }

                // 文字のバリデーションチェック
                if ($rule === \Config::get('const.ITEM_TYPE.STRING.RULE')) {
                    $cell_value = is_null($cell) ? '' : $cell->getFormattedValue();
                    if (array_key_exists($key, $setting_rules) && !is_null($setting_rules[$key]['inputRule'])) {
                        $isError = TextFormatCheck::isError($cell_value, $setting_rules[$key]['inputRule']);
                    } else {
                        $setting_rule['selectType'] = null;
                        $isError = TextFormatCheck::isError($cell_value, $setting_rule);
                    }
                } elseif ($rule === \Config::get('const.ITEM_TYPE.NUM.RULE')) {
                    // 数値のバリデーションチェック
                    $cell_value = is_null($cell) ? null : $cell->getCalculatedValue();
                    if (is_null($cell_value)) {
                        $cell_value = '';
                    }
                    if (array_key_exists($key, $setting_rules) && !is_null($setting_rules[$key]['inputRule'])) {
                        $isError = NumFormatCheck::isError($cell_value, $setting_rules[$key]['inputRule']);
                    } else {
                        $setting_rule['selectType'] = null;
                        $isError = NumFormatCheck::isError($cell_value, $setting_rule);
                    }
                } elseif ($rule === \Config::get('const.ITEM_TYPE.DATE.RULE')) {
                    // 日付のバリデーションチェック
                    $cell_value = is_null($cell) ? null : $cell->getValue();
                    if (is_numeric($cell_value)) {
                        // シリアル値をtimestampsに変換
                        $cell_value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cell_value)->format('Y/m/d');
                    } elseif (strptime($cell_value, '%Y-%m-%d') || strptime($cell_value, '%Y/%m/%d')) {
                        $strToTime = strtotime($cell_value);
                        $cell_value = date('Y/m/d', $strToTime);
                    }
                    if (is_null($cell_value)) {
                        $cell_value = '';
                    }
                    if (array_key_exists($key, $setting_rules) && !is_null($setting_rules[$key]['inputRule'])) {
                        $isError = DateFormatCheck::isError($cell_value, $setting_rules[$key]['inputRule']);
                    } else {
                        $setting_rule['selectType'] = null;
                        $isError = DateFormatCheck::isError($cell_value, $setting_rule);
                    }
                } else {
                    $cell_value = is_null($cell) ? '' : $cell->getFormattedValue();
                }

                if ($isError) {
                    $errors[] = [
                        'row_num' => $row_data['row_num'],
                        'display' => $column_config['display'],
                        'row' => $column_config['row'],
                    ];
                    $row_data['has_error'] = $isError;
                }

                $data = [
                    $column_config['row'] => [
                        'value' => $cell_value,
                        'error' => $isError,
                        'display' => $column_config['display'],
                        'item_type' => $column_config['dataType'],
                        'row_num' => $row->getRowIndex(),
                        'width' => $column_width,
                        'row' => $column_config['row'],
                    ]
                ];

                $row_data['data'][] = $data;
            }

            // 案件明細の件名を作成
            foreach ($subjects as $subject) {
                foreach ($row_data['data'] as $data) {
                    foreach ($data as $row_key => $row) {
                        if ($subject === $row_key && $row['value'] !== '') {
                            $row_data['subject'] .= $row['value'] . '_';
                        }
                    }
                }
            }

            $row_data['subject'] = rtrim($row_data['subject'], '_');
            $row_data['subject'] = str_replace(array("\r\n", "\r", "\n"), '', $row_data['subject']);// 改行コードの削除
            if (mb_strlen($row_data['subject']) > 256) {
                $row_data['subject'] = mb_substr($row_data['subject'], 0, 256);
            }

            $rows[] = $row_data;
        }

        // ヘッダーにもエラーフラグを持たせておく
        $error_row = array_column($errors, 'row');
        if (!empty($headers)) {
            foreach ($headers as $key => $header) {
                if (in_array($header['row'], $error_row)) {
                    $headers[$key]['error'] = true;
                }
            }
        }

        // 行番号が表頭のテーブル用データを作成
        $loop_num = 0;
        $header_key_rows = [];
        if (!empty($headers)) {
            foreach ($headers as $key => $header) {
                $header_key_rows[$loop_num] = $header;
                foreach ($rows as $key => $row) {
                    $cell_in_row = $row;
                    foreach ($row['data'] as $data_key => $data_value) {
                        $collection = collect($data_value);
                        if (count($collection->where('row', $header['row'])) > 0) {
                            $cell_in_row['data'] = $row['data'][$data_key];
                        }
                    }
                    $header_key_rows[$loop_num]['row_data'][] = $cell_in_row;
                }
                $loop_num += 1;
            }
        }

        // 取込設定情報
        $import_conf_info = [
            'header_row_num' => $header_row_no,
            'data_start_row_num' => $data_start_row_no,
        ];

        $data = [
            'headers' => $headers,  // 項目名が表頭のテーブル用表頭データ
            'rows' => $rows,  // 項目名が表頭のテーブル用行データ
            'row_nums' => $row_nums, // 行番号が表頭のテーブル用表頭データ
            'header_key_rows' => $header_key_rows, // 行番号が表頭のテーブル用行データ
            'errors' => $errors,
            'import_conf_info' => $import_conf_info,
        ];

        return $data;
    }

    /**
     * 設定画面の初期表示に必要となる情報を取得
     *
     * @param Request $req リクエスト
     * @return JsonResponse
     */
    public function readSettingOrderFile(Request $req): JsonResponse
    {
        try {
            $tmp_file_info = json_decode($req->input('tmp_file_info'), true) ?? [];
            $tmp_file_path = $tmp_file_info['tmp_file_path'];
            $data_start_row_no = $req->input('data_start_row_no') ?? 2;

            if (!Storage::exists($tmp_file_path)) {
                // 一時ファイルが見つからない場合はエラーを返す
                throw new \Exception('File not exists.');
            }

            $disk = Storage::disk('local');
            $mime = $disk->mimeType($tmp_file_path);
            $ext_to_mimes = [
                "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            ];

            // ファイルタイプ別に読込処理
            $order_file_info = [];
            if ($mime === $ext_to_mimes['xlsx']) {
                $order_file_info = $this->readHeaderOrderFile($disk->path($tmp_file_path), $data_start_row_no);
            }

            return response()->json([
                'items' => $order_file_info['data'],
                'content_count' => $order_file_info['content_count'],
                'status' => 200,
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 取り込んだ案件の設定情報を取得
     *
     * @param Request $req リクエスト
     * @return JsonResponse
     */
    public function getSettingInformation(Request $req): JsonResponse
    {
        try {
            $tmp_file_info = json_decode($req->input('tmp_file_info'), true) ?? [];
            $tmp_file_path = $tmp_file_info['tmp_file_path'];
            $data_start_row_no = $req->input('data_start_row_no') ?? 2;

            if (!Storage::exists($tmp_file_path)) {
                // 一時ファイルが見つからない場合はエラーを返す
                throw new \Exception('File not exists.');
            }

            $disk = Storage::disk('local');
            $mime = $disk->mimeType($tmp_file_path);
            $ext_to_mimes = [
                "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            ];

            // ファイルタイプ別に読込処理
            $order_file_info = [];
            if ($mime === $ext_to_mimes['xlsx']) {
                $order_file_info = $this->readHeaderOrderFile($disk->path($tmp_file_path), $data_start_row_no);
                $order_file_info['file_data'] = $order_file_info['data'];
            }

            $error_messages = [];
            if (array_key_exists('data', $order_file_info)) {
                $item_names = array_column($order_file_info['data'], 'itemName');
                $error_messages = $this->checkErrorItemName($item_names); // 項目名のチェック
            }

            // 取り込んだ際の設定した情報の取得
            $order_id = $req->input('order_id');
            $order = Order::findOrFail($order_id);

            $order_file_import_main_config_id = $order
                ->orderFiles()
                ->wherePivot('import_type', \Config::get('const.FILE_IMPORT_TYPE.NEW'))
                ->first()
                ->orderFileImportMainConfigs()->first()->id;
            $language_id = Language::where('code', 'ja')->first()->id;

            $order_file_info['data'] = OrderFileImportColumnConfig::
                join(
                    'labels',
                    function ($join) use ($language_id) {
                        $join->on('order_file_import_column_configs.label_id', '=', 'labels.label_id')
                            ->where('language_id', $language_id);
                    }
                )
                ->select(
                    'order_file_import_column_configs.id AS id',
                    'order_file_import_column_configs.column AS row',
                    'order_file_import_column_configs.item AS itemName',
                    'labels.name AS display',
                    'order_file_import_column_configs.item_type AS dataType'
                )
                ->where('order_file_import_column_configs.order_file_import_main_config_id', '=', $order_file_import_main_config_id)
                ->get()
                ->toArray();

            $subject_part_no = OrderFileImportColumnConfig::where('order_file_import_main_config_id', '=', $order_file_import_main_config_id)
                ->whereNotNull('subject_part_no')
                ->orderBy('subject_part_no', 'asc')
                ->pluck('column');

            $setting_rules = OrderFileImportColumnConfig::select('rule', 'display_format')
                ->where('order_file_import_main_config_id', '=', $order_file_import_main_config_id)
                ->get();

            return response()->json([
                'items' => $order_file_info['data'],
                'file_items' => $order_file_info['file_data'],
                'content_count' => $order_file_info['content_count'],
                'order_name' => $order->name,
                'setting_rules' => $setting_rules,
                'subject_part_no' => $subject_part_no,
                'error_messages' => $error_messages,
                'status' => 200,
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    // 取込対象業務リスト
    public function targetBusinessList(Request $req)
    {
        $user = \Auth::user();

        $target_step_ids = FileImportMainConfig::pluck('step_id');
        $list = Business::whereHas('users', function ($query) use ($user) {
            $query->where('id', $user->id);
        })
        ->whereHas('steps', function ($query) use ($target_step_ids) {
            $query->whereIn('steps.id', $target_step_ids);
        })
        ->get();

        return response()->json([
            'list' => $list
        ]);
    }

    // ローカルに一時保存
    private function tmpFileStore($data, $type = 'content')
    {
        $file = $data['file'];
        $file_name = $file['file_name'];
        $file_dir = 'tmp/request_files/' . Carbon::now()->format('Ymd') . md5(strval(time()));
        $file_path = $file_dir .'/'. $file_name;
        $file_contents = '';

        switch ($type) {
            case 'base64':
                // file data is decode to base64
                list(, $fileData) = explode(';', $file['file_data']);
                list(, $fileData) = explode(',', $fileData);
                $file_contents = base64_decode($fileData);
                break;
            case 'content':
                $file_contents = $file['file_data'];
                break;
            default:
                throw new \Exception('not_type');
        }

        // ローカルに一時保存
        $disk = Storage::disk('local');
        $disk->put($file_path, $file_contents, '');

        // 一時ファイルのパスを取得
        $tmp_file_path = $disk->path($file_path);

        // 一時ファイルが見つからない場合はエラーを返す
        if (!$disk->exists($file_path)) {
            // TODO
        }

        $file_info = [
            'file_name' => $file_name,
            'file_dir' => $file_dir,
            'file_path' => $file_path,
        ];

        return $file_info;
    }

    /**
     * エクセルファイルの内容を読み取る
     * @param string $path ファイルのパス
     * @param int $data_start_row_no データ開始行
     * @return array $order_file_info エクセルファイルの内容
     */
    private function readHeaderOrderFile(string $path, int $data_start_row_no): array
    {
        /*
         * データ読込
         */
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader = $reader->load($path);

        $sheet = $reader->getSheet(0);

        $data = [];

        $column_iterators = $sheet->getColumnIterator();
        $row_iterators = $sheet->getRowIterator($data_start_row_no);
        $content_count = 0;

        // 行
        foreach ($column_iterators as $column) {
            // デフォルトでは表示フォーマットで取得
            $cell = $sheet->getCell(strval($column->getColumnIndex()) . 1)->getFormattedValue();
            // セル値が空白の場合まで取得
            if ($cell === '') {
                break;
            }

            $data[] = [
                'row' => $column->getColumnIndex(),
                'itemName' => $cell,
                'display' => str_replace(array("\r\n", "\r", "\n"), '', $cell),
                'dataType' => \Config::get('const.ITEM_TYPE.STRING.ID'),
            ];

            foreach ($row_iterators as $row) {
                $cell = $sheet->getCell($column->getColumnIndex() . strval($row->getRowIndex()))->getFormattedValue();
                if ($cell !== '') {
                    $content_count += 1;
                }
            }
        }

        $order_file_info = [
            'data' => $data,
            'content_count' => $content_count,
        ];

        return $order_file_info;
    }

    // エクセルファイルデータ読み込み
    private function readExcel($path, $step_id)
    {
        /*
         * 取込設定取得
        */
        $main_config = FileImportMainConfig::with('fileImportColumnConfigs')->where('step_id', $step_id)->first();
        $column_configs = $main_config->fileImportColumnConfigs;
        $valid_rule_conf = $this->getValidRuleConf();

        // 必要なラベルデータをまとめて取得しておく
        $label_ids = [];
        foreach ($column_configs as $column_config) {
            $label_ids[] = $column_config->label_id;
        }
        $label_data = Label::getLangKeySetByIds($label_ids);

        /*
         * データ読込
         */
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader = $reader->load($path);

        // シート取得
        $sheet = '';
        if (isset($main_config->sheet_name) && $main_config->sheet_name) {
            $sheet = $reader->getSheetByName($main_config->sheet_name);
        } else {
            // シート名の指定がなければ先頭のシートを読み込む
            $sheet = $reader->getSheet(0);
        }
        $font = $sheet->getParent()->getDefaultStyle()->getFont();

        // 表頭行と取込データ行の間の不要な行番号を取得
        $org_header_row_num = $main_config->header_row_no;

        // 取得不要な行番号を保持
        $not_read_row_nums = [];
        if ($org_header_row_num) {
            if (($main_config->data_start_row_no - $org_header_row_num) > 1) {
                for ($i = $org_header_row_num + 1; $i < $main_config->data_start_row_no; $i++) {
                    $not_read_row_nums[] = $i;
                }
            }
        }

        $headers = [];   // 表頭項目
        $rows = [];      // データ行
        $errors = [];    // エラー一覧用
        $row_nums = [];
        $loop_num = 0;

        $first_row_num = $org_header_row_num ? $org_header_row_num : $main_config->data_start_row_no;
        $row_iterators = $sheet->getRowIterator($first_row_num);

        // 行
        foreach ($row_iterators as $row) {
            // 取得不要な行はスルー
            if (in_array($row->getRowIndex(), $not_read_row_nums)) {
                continue;
            }
            $row_data = [];
            $row_data['row_num'] = $row->getRowIndex();
            $row_data['is_org_header_row'] = ($row->getRowIndex() === $org_header_row_num) ? true : false;
            $row_data['has_error'] = false;  // 初期値
            $row_nums[] = $row->getRowIndex();

            // 列：定義された列データを取得
            foreach ($column_configs as $column_config) {
                $column_width = $sheet->getColumnDimension($column_config->column)->getWidth();
                $column_width = \PhpOffice\PhpSpreadsheet\Shared\Drawing::cellDimensionToPixels($column_width, $font);
                // 表頭ラベル用データをゲット（最初のデータ行から全部取っておく）
                if ($loop_num === 0) {
                    $headers[] = [
                        'label_id' => $column_config->label_id,
                        'error' => false,
                        'width' => $column_width,
                    ];
                }

                // デフォルトでは表示フォーマットで取得
                $cell = $sheet->getCell($column_config->column . strval($row->getRowIndex()))->getFormattedValue();
                $is_org_header_cell = $row_data['is_org_header_row'] ? true : false;

                // 表頭以外をバリデーション
                $error = false;
                if (!$is_org_header_cell) {
                    $valid_rules = $this->makeValidRules($valid_rule_conf, $column_config);

                    // 日付の場合はフォーマットなしでシリアル値で取得する
                    if ($column_config->item_type === \Config::get('const.ITEM_TYPE.DATE.ID')) {
                        $cell = $sheet->getCell($column_config->column . strval($row->getRowIndex()))->getValue();
                        if (!is_null($cell) && is_numeric($cell)) {
                            // シリアル値をtimestampsに変換
                            $timestamp = ($cell - \Config::get('const.UNIX_TIME_SERIAL_VALUE')) * 60 * 60 * 24;
                            $cell = date('Y/m/d H:i:s', $timestamp);
                        }
                    }

                    $check_arr = ['value' => $cell];
                    $validator = Validator::make($check_arr, [
                        'value' => $valid_rules,
                    ]);

                    if ($validator->fails()) {
                        $error = true;
                        $errors[] = [
                            'row_num' => $row_data['row_num'],
                            'label_id' => $column_config->label_id,
                        ];

                        $row_data['has_error'] = $error;
                    }
                }

                $data = [
                    $column_config->id => [
                        'value' => $cell,
                        'error' => $error,
                        'label_id' => $column_config->label_id,
                        'item_type' => $column_config->item_type,
                        'subject_part_no' => $column_config->subject_part_no,
                        'request_info_type' => $column_config->request_info_type,
                        'row_num' => $row->getRowIndex(),
                        'is_org_header_cell' => $is_org_header_cell ? true : false,
                        'width' => $column_width,
                    ]
                ];

                $row_data['data'][] = $data;
            }
            $rows[] = $row_data;
            $loop_num += 1;
        }

        // ヘッダーにもエラーフラグを持たせておく
        $error_label_ids = array_column($errors, 'label_id');
        foreach ($headers as $key => $header) {
            if (in_array($header['label_id'], $error_label_ids)) {
                $headers[$key]['error'] = true;
            }
        }

        // 行番号が表頭のテーブル用データを作成
        $loop_num = 0;
        $header_key_rows = [];
        foreach ($headers as $key => $header) {
            $header_key_rows[$loop_num] = $header;
            foreach ($rows as $key => $row) {
                $cell_in_row = $row;
                foreach ($row['data'] as $data_key => $data_value) {
                    $collection = collect($data_value);
                    if (count($collection->where('label_id', $header['label_id'])) > 0) {
                        $cell_in_row['data'] = $row['data'][$data_key];
                    }
                }
                $header_key_rows[$loop_num]['row_data'][] = $cell_in_row;
            }
            $loop_num += 1;
        }

        // 取込設定情報
        $import_conf_info = [
            'header_row_num' => $main_config->header_row_no,
            'data_start_row_num' => $main_config->data_start_row_no,
        ];

        $data = [
            'org_header_row_num' => $org_header_row_num,
            'headers' => $headers,  // 項目名が表頭のテーブル用表頭データ
            'rows' => $rows,  // 項目名が表頭のテーブル用行データ
            'row_nums' => $row_nums, // 行番号が表頭のテーブル用表頭データ
            'header_key_rows' => $header_key_rows, // 行番号が表頭のテーブル用行データ
            'errors' => $errors,
            'label_data' => $label_data,
            'import_conf_info' => $import_conf_info,
        ];

        return $data;
    }

    private function getValidRuleConf()
    {
        // \Config::get('const.ITEM_TYPE')を元に、「ID => 'RULE'」の形にしたバリデーション用の連想配列を作成
        $valid_rule_collection = collect(\Config::get('const.ITEM_TYPE'));
        $keyed = $valid_rule_collection->mapWithKeys(function ($item) {
            return [$item['ID'] => $item['RULE']];
        });
        $valid_rule_conf = $keyed->all();

        return $valid_rule_conf;
    }

    private function makeValidRules($rule_conf, $column_conf)
    {
        // バリデーション用のルールセットを作成
        $rules = [];
        if ($column_conf->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        if ($column_conf->item_type) {
            $rules[] = $rule_conf[$column_conf->item_type];
        }

        return implode('|', $rules);
    }

    private function getRequestInfoByType($request_info, $request_info_type, $val)
    {
        if ($request_info_type === \Config::get('const.REQUEST_INFO_TYPE.CLIENT_NAME')) {
            $request_info['client_name'] = $val;
        } else if ($request_info_type === \Config::get('const.REQUEST_INFO_TYPE.DEADLINE')) {
            $request_info['deadline'] = $val;
        }

        return $request_info;
    }

    /**
     * 項目名のエラーチェック
     * @param string[] $item_names 項目名の配列
     * @return string[] エラーメッセージ
     */
    private function checkErrorItemName(array $item_names): array
    {
        $error_messages = [];
        // 改行チェック
        $newline_characters = preg_grep("/\r\n|\r|\n/u", $item_names);
        if (count($newline_characters) > 0) {
            $error_messages[] = 'item_name_has_a_line_break';
        }

        // 重複チェック
        $counts = array_count_values($item_names);
        $errors = array_filter($counts, function ($v) {
            return $v > 1;// 重複している場合はtrue
        });
        if (count($errors) > 0) {
            $error_messages[] = 'duplicate_item_name';
        }

        return $error_messages;
    }
}
