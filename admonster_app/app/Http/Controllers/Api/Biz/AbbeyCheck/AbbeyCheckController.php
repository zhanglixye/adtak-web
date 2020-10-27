<?php

namespace App\Http\Controllers\Api\Biz\AbbeyCheck;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Storage;
use App\Models\RequestMailAttachment;
use App\Models\Business;
use App\Models\Request as RequestModel;
use App\Models\RequestWork;
use App\Models\RequestMail;
use App\Services\Traits\RequestMailTrait;
use App\Models\TaskResult;
use App\Models\Abbey;
use App\Models\Task;
use App\Models\Queue;
use App\Models\SendMail;
use App\Models\TaskResultFile;
use App\Models\RequestMailAttachmentExtra;
use App\Models\SendMailAttachment;
use App\Models\Step;
use App\Services\Traits\RequestLogStoreTrait;
use App\Services\UploadFileManager\Uploader;
use League\Flysystem\Exception;
use function GuzzleHttp\json_encode;
use ZipArchive;

class AbbeyCheckController extends Controller
{
    use RequestLogStoreTrait;
    use RequestMailTrait;

    // Abbeyチェック画面
    public function create(Request $req)
    {
        $user = \Auth::user();

        $task_id = $req->task_id;

        $task = \DB::table('tasks')
            ->select(
                'status',
                'is_active',
                'message'
            )
            ->where('id', $task_id)
            ->first();

        $task_status = $task->status;
        $is_done_task = ($task_status == \Config::get('const.TASK_STATUS.DONE')) ? true : false;
        $is_inactive_task = ($task->is_active == \Config::get('const.FLG.INACTIVE')) ? true : false;
        $message = $task->message;

        $request_work = RequestWork::with('requestMails')->where('id', $req->request_work_id)->first();

        $started_at = Carbon::now();

        $task_result = TaskResult::where('task_id', $task_id)->where('step_id', $request_work->step_id)->orderBy('updated_at', 'desc')->first();
        $task_result_content = !is_null($task_result) ? json_decode($task_result->content) : '';

        // 関連ファイルの情報
        $file_data_list = [];

        // リクエストメールの添付ファイル情報の取得
        $request_mail = $request_work->requestMails->first();
        $request_mail_template_info = $this->tryCreateMailTemplateData($request_mail->id);
        foreach ($request_mail_template_info->mail_attachments as $file) {
            if ($this->specificFileCheck($file->name)) {
                // 依頼メールの添付（追加）と番号が重ならないように頭に1を付ける
                array_push($file_data_list, $this->createCheckFile('1'.$file->id, $file->name, $file->file_path));
            }
        }

        // 過去の作業内容で判断
        if ($task_result_content == null || $task_result_content == '') {
            // 未実施作業
            $task_result_content = new \stdClass();

            // 依頼メールの添付（追加）から取得
            $mail_attachment_extra_ids = [];
            foreach ($request_mail_template_info->mail_attachments as $mail_attachment) {
                array_push($mail_attachment_extra_ids, $mail_attachment->id);
            }
            $mail_attachment_extras = [];
            if (count($mail_attachment_extra_ids) > 0) {
                $mail_attachment_extras = RequestMailAttachmentExtra::getFile(null, $mail_attachment_extra_ids);
            }
            foreach ($mail_attachment_extras as $file) {
                $name = $file->name;
                if ($this->specificFileCheck($name)) {
                    $id  = $file->id;
                    $path = $file->file_path;
                    // 依頼メールの添付（追加）と番号が重ならないように頭に2を付ける
                    array_push($file_data_list, $this->createCheckFile('2'.$id, $name, $path));
                }
            }
            // Abbey結果画像は無いので空配列
            $task_result_content->result_files = [];
        } else {
            // 過去の作業あり

            // リセット
            $file_data_list = [];

            // 結果から素材のアップロードファイルの取得
            $check_files = $task_result_content->check_files;
            foreach ($check_files as $file) {
                $file->file_path = $file->file_path;
                $file->file_data = '';
                $file->type = '';
                array_push($file_data_list, $file);
            }


            $result_files = array();
            // 結果から結果キャプチャのアップロードファイルの取得
            $result_captures = $task_result_content->result_capture_file_seq_no;

            // Abbey結果画像がある場合のみ
            if (count($result_captures) > 0) {
                // 一度DBからseqを使いデータを取得
                $task_result_id = array($task_result->id);
                $task_result_files = TaskResultFile::getFile($task_result_id, $result_captures);
                foreach ($task_result_files as $task_result_file) {
                    $tmp = array(
                        'task_result_file_seq_no' => $task_result_file->seq_no,
                        'file_name' => $task_result_file->name,
                        'file_data'=> '',
                        'file_path'=> $task_result_file->file_path,
                    );
                    array_push($result_files, $tmp);
                }
                $task_result_content->result_files = $result_files;
            } else {
                $task_result_content->result_files = [];
            }
        }

        $task_result_content->check_files = $file_data_list;

        // get business
        $request = RequestModel::findOrFail($request_work->request_id);
        $business = Business::findOrFail($request->business_id);

        // get Step
        $step = Step::findOrFail($request_work->step_id);

        return response()->json([
            'request_work' => $request_work,
            'request_mail' => $request_mail_template_info,
            'task_id' => $task_id,
            'task_status' => $task_status,
            'started_at' => $started_at,
            'task_result_content' => $task_result_content,
            'is_done_task' => $is_done_task,
            'is_inactive_task' => $is_inactive_task,
            'message' => $message,
            'status' => $task_status,
            'business_name' => $business->name,
            'step_name' => $step->name,
            'task_info' => ['message' => $message]
        ]);
    }

    private function createCheckFile($seq_no, $name, $path)
    {
        $td = new \stdClass();
        $td->task_result_file_seq_no = $seq_no;
        $td->file_name = $name;
        $td->check_file_name = '';
        $td->aspect_ratio = '';
        $td->file_path = $path;
        $td->file_size = 0;
        $td->menu_name = '';
        $td->abbey_id = '';
        $td->err_description = '';
        $td->file_data = '';
        $td->type = '';
        $td->is_success = null;
        $td->err_detail = '';
        return $td;
    }

    /**
     * データベースへの登録
     */
    public function store(Request $req, TaskResult $task_result)
    {
        $user = \Auth::user();

        // 排他チェック
        $inactivated = Task::checkIsInactivatedById($req->task_id);
        if ($inactivated) {
            return response()->json([
                'result' => 'inactivated',
                'request_work_id' => $req->request_work_id,
                'task_id' => $req->task_id,
            ]);
        }

        // DB登録
        \DB::beginTransaction();

        try {
            // タスク実績を登録し、IDを参照できるようにする。
            $task_result = new TaskResult;
            $task_result->task_id = $req->task_id;
            $task_result->step_id = $req->step_id;
            $task_result->started_at = $req->started_at;
            $task_result->finished_at = Carbon::now();
            $task_result->created_user_id = $user->id;
            $task_result->updated_user_id = $user->id;
            $task_result->save();

            // 実作業データ登録
            $check_file_list = array_values(array_filter($req->check_files));

            // 登録データ
            $check_file_results = [];

            $business_id = RequestModel::where('id', $req->request_id)->pluck('business_id')->first();
            // チェック対象の画像を登録
            foreach ($check_file_list as &$check_file) {
                $root_folder = substr($check_file['file_path'], 0, strlen('request_mail_attachments'));

                // アップロードされていないものだけを保存
                if ($check_file['file_path'] == null || $root_folder !== 'request_mail_attachments') {
                    // ファイルのアップロード
                    $upload_data = array(
                        'task_result_id' => $task_result->id,
                        'user' => $user,
                        'business_id'=> $business_id,
                        'file' => $check_file
                    );
                    $task_result_file = $this->uploadFile($upload_data, 'base64');
                    $check_file['file_path'] = $task_result_file->file_path;
                }

                // タスク実績の保存対象外なので削除
                unset($check_file['file_data']);
                unset($check_file['type']);

                array_push($check_file_results, $check_file);
            }
            unset($check_file);

            $req->check_files = $check_file_list;

            // Abbey結果画面の画像を登録
            $result_file = [];
            $result_files = $req->result_files;
            $total_task_result_files = [];
            foreach ($result_files as &$file) {
                $upload_data = array(
                    'task_result_id' => $task_result->id,
                    'user' => $user,
                    'business_id'=> $business_id,
                    'file' => $file
                );
                $task_result_file = $this->uploadFile($upload_data, 'base64');
                $task_result_file->download_url = $this->createDownloadUrl($task_result_file->file_path, $task_result_file->name);
                array_push($total_task_result_files, $task_result_file);
                array_push($result_file, $task_result_file->seq_no);
            }
            unset($file);

            // メールに結果画像に関する情報を記載する為
            $req->result_file_for_mail = $total_task_result_files;

            $task_result_data = [
                'check_files' => $check_file_results,
                'result_capture_file_seq_no' => $result_file,
                'client_name' => $req->client_name
            ];

            $task_result_type = $req->process_info['results']['type'];

            if ($task_result_type == \Config::get('const.TASK_RESULT_TYPE.CONTACT')) {
                /*
                * 不明あり
                */
                // タスクのステータスを完了に更新
                $task = Task::find($req->task_id);
                $task->status = \Config::get('const.TASK_STATUS.DONE');
                $task->updated_user_id = $user->id;
                $task->save();

                // メールの登録
                $send_mail_id = $this->registerMail($user, $req);

                // 添付ファイル
                // リクエストメールの添付ファイル情報の取得
                $request_work = RequestWork::with('requestMails')->where('id', $req->request_work_id)->first();
                $request_mail = $request_work->requestMails->first();
                $request_mail_attachments = RequestMailAttachment::where('request_mail_id', $request_mail->id)->get(['name', 'file_path']);
                foreach ($request_mail_attachments as $attachment) {
                    $send_mail_attachment = new SendMailAttachment;
                    $send_mail_attachment->send_mail_id = $send_mail_id;
                    $send_mail_attachment->name = $attachment->name;
                    $send_mail_attachment->file_path = $attachment->file_path;
                    $send_mail_attachment->created_user_id = $user->id;
                    $send_mail_attachment->updated_user_id = $user->id;
                    $send_mail_attachment->save();
                }

                // タスク実績の中に入れる
                $results = array_merge($req->process_info['results'], ['mail_id' => [$send_mail_id]]);
                $task_result_data = array_merge($task_result_data, ['results' => $results]);

                // 処理キュー登録（自動承認）
                $queue = new Queue;
                $queue->process_type = \Config::get('const.QUEUE_TYPE.APPROVE');
                $queue->argument = json_encode(['request_work_id' => $req->request_work_id]);
                $queue->queue_status = \Config::get('const.QUEUE_STATUS.PREVIOUS');
                $queue->created_user_id = $user->id;
                $queue->updated_user_id = $user->id;
                $queue->save();

                // ログ登録
                $request_log_attributes = [
                    'request_id' => $req->request_id,
                    'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_WITH_UNCLEAR_POINT'),
                    'task_id' => $req->task_id,
                    'request_work_id' => $req->request_work_id,
                    'created_user_id' => $user->id,
                    'updated_user_id' => $user->id,
                ];
                $this->storeRequestLog($request_log_attributes);
            } elseif ($task_result_type == \Config::get('const.TASK_RESULT_TYPE.HOLD')) {
                /*
                *  保留
                */


                // タスク実績の中に入れる
                $task_result_data = array_merge($task_result_data, $req->process_info);

                // タスクのステータスを完了に更新
                $task = Task::find($req->task_id);
                $task->status = \Config::get('const.TASK_STATUS.ON');
                $task->updated_user_id = $user->id;
                $task->save();
            } else {
                /*
                *  通常
                */
                // タスクのステータスを完了に更新
                $task = Task::find($req->task_id);
                $task->status = \Config::get('const.TASK_STATUS.DONE');
                $task->updated_user_id = $user->id;
                $task->save();

                // create excel file
                // excel file upload to s3

                $abbey_ids = [];
                foreach ($req->check_files as $file) {
                    array_push($abbey_ids, $file['abbey_id']);
                }

                $abbeys = Abbey::searchByAbbeyId($abbey_ids);

                foreach ($req->check_files as &$file) {
                    // 媒体資料のメニュー名をセット
                    $file['abbey_menu_name'] = null;

                    // 媒体規定をセット
                    $file['media_regulation'] = null;
                    foreach ($abbeys as $value) {
                        if ($value->abbey_id == $file['abbey_id']) {
                            $linefeed_code = "\n";
                            $regulation = '横幅:' . $value->width . $linefeed_code;
                            $regulation .= '高さ:'. $value->hight . $linefeed_code;
                            $regulation .= 'ファイルサイズ:' . $value->file_size . ($value->file_size_unit == 1 ? 'KB':'MB') . $linefeed_code;
                            $file['media_regulation'] = $regulation;
                            break;
                        }
                    }

                    $file['result_capture_file_url'] = "";
                    // 結果画像をセット
                    foreach ($total_task_result_files as $value) {
                        if (strcmp(explode('.', $value->name)[0], explode('.', $file['check_file_name'])[0]) == 0) {
                            if (mb_strlen($file['result_capture_file_url']) > 0) {
                                $file['result_capture_file_url'] .= "\n\n";
                            }
                            $file['result_capture_file_url'] .=  $value->name . "\n" . $value->download_url;
                        }
                    }

                    $url = $file['file_path'];

                    // 外部からのダウンロードが行えるURLを生成
                    $file['download_url'] = $this->createDownloadUrl($url, $file['check_file_name']);
                }
                unset($file);

                // 対応表の生成
                $excel_file_name = 'チェック結果_'. $req->client_name .'_'.$req->request_id.'.xlsx';

                $tmp_file_path = storage_path('biz/abbey_check/result_template.xlsx');
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
                $spreadsheet = $reader->load($tmp_file_path);
                $worksheet = $spreadsheet->getSheetByName("Worksheet");

                // テンプレート内の値のコピーに使用
                $template_sheet = clone $worksheet;
                $check_files = $req->check_files;

                // 基準となる行を探索
                $start_string = '%start_cell%';
                $start_cell = null;
                $start_row = null;
                $start_column_string = null;
                foreach ($template_sheet->getRowIterator() as $row) {
                    $cell_iterator = $row->getCellIterator();
                    $cell_iterator->setIterateOnlyExistingCells(false);//すべてのセルを確認
                    foreach ($cell_iterator as $cell) {
                        // 基準の文字列
                        if (strcmp($cell->getValue(), $start_string) === 0) {
                            $start_cell = $cell;
                            $start_column_string = $cell->getColumn();
                            $start_row = $row;
                            break;
                        }
                    }
                    if (isset($start_cell) && isset($start_row) && isset($start_column_string)) {
                        break;
                    }
                }

                if (is_null($start_cell) || is_null($start_row) || is_null($start_column_string)) {
                    throw new \Exception('not found start string "%start_cell%" in result_template.xlsx');
                }
                // 2行目に指定した行数分、行を挿入（セルの属性は引き継げるが、値はコピーできない）
                $worksheet->insertNewRowBefore($start_cell->getRow() + 1, count($check_files) - 1);

                // ワークシートの基準行から基準行+素材ファイル数まで回す
                $cell_iterator =  $start_row->getCellIterator();
                $cell_iterator->setIterateOnlyExistingCells(false);//すべてのセルを確認
                $row_index = $start_cell->getRow();
                foreach ($cell_iterator as $cell) {
                    foreach ($worksheet->getRowIterator()->resetStart($row_index)->resetEnd($row_index + count($check_files) - 1) as $row) {
                        $cell_position = $cell->getColumn() . $row->getRowIndex();
                        // 開始文字列は空にする
                        if (strcmp($cell->getValue(), $start_string) === 0) {
                            $worksheet->setCellValue($cell_position, '');
                        } else {
                            $worksheet->setCellValue($cell_position, $cell->getValue());
                        }
                    }
                }

                // 開始列を開始文字列に合わせるため、カラム名からの番号を取得
                $start_column_index = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($start_column_string);

                // 指定した行に値を設定
                for ($i = 0; $i < count($check_files); $i++) {
                    // 結果判定
                    $is_success = '';
                    if ($check_files[$i]['is_success'] === true) {
                        $is_success = '判定OK';
                    } elseif ($check_files[$i]['is_success'] === false) {
                        $is_success = '判定NG';
                    }

                    // セルにデータをセット
                    $cellNumber = $i + $start_row->getRowIndex();
                    $worksheet->setCellValueByColumnAndRow($start_column_index, $cellNumber, $i + 1);
                    $worksheet->setCellValueByColumnAndRow($start_column_index + 1, $cellNumber, $check_files[$i]['menu_name']);
                    $worksheet->setCellValueByColumnAndRow($start_column_index + 2, $cellNumber, $check_files[$i]['abbey_id']);
                    $worksheet->setCellValueByColumnAndRow($start_column_index + 3, $cellNumber, $check_files[$i]['file_name']);
                    $worksheet->setCellValueByColumnAndRow($start_column_index + 4, $cellNumber, $check_files[$i]['check_file_name']);
                    $worksheet->setCellValueByColumnAndRow($start_column_index + 5, $cellNumber, $is_success);
                    $worksheet->setCellValueByColumnAndRow($start_column_index + 6, $cellNumber, $check_files[$i]['err_detail']);
                    $worksheet->setCellValueByColumnAndRow($start_column_index + 7, $cellNumber, $check_files[$i]['err_description']);
                    $worksheet->setCellValueByColumnAndRow($start_column_index + 8, $cellNumber, $check_files[$i]['media_regulation']);
                    $worksheet->setCellValueByColumnAndRow($start_column_index + 9, $cellNumber, $check_files[$i]['result_capture_file_url']);
                    $worksheet->setCellValueByColumnAndRow($start_column_index + 10, $cellNumber, $check_files[$i]['download_url']);

                    // 折り返しで高さを自動にするため
                    $worksheet->getRowDimension($cellNumber)->SetRowHeight(-1);
                }

                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

                // ローカルに置く際の一時ファイル名
                $microtime_float = explode(".", (microtime(true)).'.');
                $tmp_file_name = $microtime_float[0]. $microtime_float[1] . $excel_file_name;

                // ファイルの出力
                $writer->save(storage_path() . '/app/public/' . $tmp_file_name);

                // ファイルのアップロード
                $local_disk = Storage::disk('public');
                $seq_no = count($check_files);
                if (count($task_result_data['result_capture_file_seq_no']) > 0) {
                    $seq_no = max($task_result_data['result_capture_file_seq_no']) + 1;
                }

                $file = array(
                    'file_name' => $excel_file_name,
                    'file_data' => $local_disk->get($tmp_file_name),
                    'task_result_file_seq_no' => $seq_no
                );
                $upload_data = array(
                    'task_result_id' => $task_result->id,
                    'user' => $user,
                    'business_id'=> $business_id,
                    'file' => $file
                );
                $task_result_file = $this->uploadFile($upload_data);

                // 一時ファイルの削除
                $local_disk->delete($tmp_file_name);

                $req->excel_file = array(
                    'file_name' => $excel_file_name,
                    'download_url' => $this->createDownloadUrl($task_result_file->file_path, $excel_file_name)
                );

                // メールの登録
                $req->task_result = $task_result;
                $send_mail_id = $this->registerMail($user, $req);

                // 添付ファイル
                // リクエストメールの添付ファイル情報の取得
                $request_work = RequestWork::with('requestMails')->where('id', $req->request_work_id)->first();
                $request_mail = $request_work->requestMails->first();
                $request_mail_attachments = RequestMailAttachment::where('request_mail_id', $request_mail->id)->get(['name', 'file_path']);
                foreach ($request_mail_attachments as $attachment) {
                    $send_mail_attachment = new SendMailAttachment;
                    $send_mail_attachment->send_mail_id = $send_mail_id;
                    $send_mail_attachment->name = $attachment->name;
                    $send_mail_attachment->file_path = $attachment->file_path;
                    $send_mail_attachment->created_user_id = $user->id;
                    $send_mail_attachment->updated_user_id = $user->id;
                    $send_mail_attachment->save();
                }

                // タスク実績の中に入れる
                $results = array_merge($req->process_info['results'], ['mail_id' => [$send_mail_id]]);
                $task_result_data = array_merge($task_result_data, ['results' => $results]);


                // 処理キュー登録（自動承認）
                $queue = new Queue;
                $queue->process_type = \Config::get('const.QUEUE_TYPE.APPROVE');
                $queue->argument = json_encode(['request_work_id' => $req->request_work_id]);
                $queue->queue_status = \Config::get('const.QUEUE_STATUS.PREVIOUS');
                $queue->created_user_id = $user->id;
                $queue->updated_user_id = $user->id;
                $queue->save();

                // ログ登録
                $request_log_attributes = [
                    'request_id' => $req->request_id,
                    'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_NORMALLY'),
                    'task_id' => $req->task_id,
                    'request_work_id' => $req->request_work_id,
                    'created_user_id' => $user->id,
                    'updated_user_id' => $user->id,
                ];
                $this->storeRequestLog($request_log_attributes);
            }

            // 確定した実績内容で更新
            $task_result->content = json_encode($task_result_data, JSON_UNESCAPED_UNICODE);
            $task_result->save();

            \DB::commit();

            return response()->json([
                'result' => 'success',
                'request_work_id' => $req->request_work_id,
                'task_id' => $req->task_id,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
                'request_work_id' => $req->request_work_id,
                'task_id' => $req->task_id,
                'obj'=> $e
            ]);
        }
    }
    // メールの件名を生成
    public function generateSendMailSubject($data)
    {
        $subject = $data['is_check_decision'] . '【'.$data['business_name'].'】'.$data['mail_type'].' Re:'.$data['request_name'];

        return $subject;
    }

    public function makeMailBody($mail_body_data)
    {
        $mail_body = '';
        if ($mail_body_data['mail_type'] == \Config::get('const.TASK_RESULT_TYPE.CONTACT')) {
            $mail_body = \View::make('biz.abbey_check.abbey_check.emails.contact')
                            ->with($mail_body_data)
                            ->render();
        } elseif ($mail_body_data['mail_type'] == \Config::get('const.TASK_RESULT_TYPE.DONE')) {
            $mail_body = \View::make('biz.abbey_check.abbey_check.emails.done')
                            ->with($mail_body_data)
                            ->render();
        }

        return $mail_body;
    }

    private function registerMail($user, Request $req)
    {
        // メール送信者のアドレスを取得
        $business_id = RequestModel::where('id', $req->request_id)->pluck('business_id')->first();
        $business = Business::with('users')->where('id', $business_id)->first();

        $request_mail = RequestMail::where('id', $req->request_mail_id)->first();
        $request_mail_subject = isset($request_mail->subject) ? $request_mail->subject : '';

        $task_result_type = $req->process_info['results']['type'];

        $mail_body_data = [
            'mail_type' => $task_result_type,
            'business_name' => $business->name,
            'comment' => $req->process_info['results']['comment'],
            'request_mail' => $request_mail,
            'check_files' => $req->check_files,
            'check_file_zip' => (count($req->check_files) > 4) ? ($this->createZipFile($user, $req)) : null,
            'excel_file' => $req->excel_file,
            'result_files' => $req->result_file_for_mail
        ];

        // 判定
        $check_files = $req->check_files;
        $is_check_ok = true;
        for ($i = 0; $i < count($check_files); $i++) {
            // 結果判定
            if ($check_files[$i]['is_success'] === false) {
                $is_check_ok = false;
                break;
            }
        }

        $mail_subject_data = [
            'is_check_decision' => $is_check_ok? '': '【NG】',
            'business_name' => $business->name,
            'mail_type' => __('biz/abbey_check.common.task_result_type_text.prefix'.$task_result_type),
            'request_name' => $request_mail_subject,
        ];

        // 本文生成
        $mail_body = $this->makeMailBody($mail_body_data);

        $send_mail = new SendMail;
        $send_mail->request_work_id = $req->request_work_id;
        $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));

        if ($mail_body_data['mail_type'] == \Config::get('const.TASK_RESULT_TYPE.CONTACT')) {
            $send_mail->to = config('biz.abbey_check.contact_mail_receiver');
        } elseif ($mail_body_data['mail_type'] == \Config::get('const.TASK_RESULT_TYPE.DONE')) {
            $remove_name_in_mail_address = function ($mailAddress) {
                // 名前'<mailAddress>' のパターン場合 "名前'<"以前の削除
                $address = preg_replace('/.*</u', '', $mailAddress);
                // 名前'<mailAddress>' のパターン場合 "名前>'"以降の削除
                $address = preg_replace('/>.*/u', '', $address);
                return $address;
            };

            // メールの送り主
            $send_mail->to = $remove_name_in_mail_address($request_mail->from);

            // メールCC
            // ない場合は空白文字列
            $send_mail->cc = '';
            foreach (explode(',', $request_mail->cc) as $key => $value) {
                if (mb_strlen($send_mail->cc) > 0) {
                    $send_mail->cc .= ',';
                }
                $send_mail->cc .= $remove_name_in_mail_address($value);
            }
        }
        $send_mail->subject = $this->generateSendMailSubject($mail_subject_data);
        $send_mail->content_type = $request_mail->content_type;
        $send_mail->body = $mail_body;
        $send_mail->created_user_id = $user->id;
        $send_mail->updated_user_id = $user->id;
        $send_mail->save();
        return $send_mail->id;
    }

    private function createZipFile($user, Request $req)
    {
        // 管理用配列
        $manage_array = [];
        try {
            // ファイルをダウンロード
            foreach ($req->check_files as $file) {
                $file_path = $file['file_path'];
                $file_name = $file['check_file_name'];
                $disk = \Storage::disk(\Config::get('filesystems.cloud'));
                $file = $disk->get($file_path);

                // S3の完全URLを取得
                $url = $disk->url($file_path);
                // S3上に指定ファイルが存在するか確認
                if (!$disk->exists($file_path)) {
                    throw new \Exception('S3Download is failed. File not exists');
                }
                $file_name =  isset($file_name) ? $file_name : basename($url);

                // ローカルに一時保存
                $local_disk = Storage::disk('public');
                $microtime_float = explode('.', (microtime(true)).'.');
                $tmp_file_name = $microtime_float[0]. $microtime_float[1] . $file_name;
                $local_file_path = 'tmp/' . $tmp_file_name;
                $local_disk->put($local_file_path, $disk->get($file_path));
                $tmp_file_path = storage_path() . '/app/public/' . $local_file_path;
                array_push($manage_array, ['file_name' => $file_name, 'file_full_path' => $tmp_file_path, 'local_disk_path' => $local_file_path]);
            }

            // zipファイルの作成
            $zip = new ZipArchive();
            // ZIPファイルをオープン
            $microtime_float = explode('.', (microtime(true)).'.');
            $zip_file_name = 'material.zip';
            $tmp_zip_file_name = $microtime_float[0]. $microtime_float[1] . $zip_file_name;
            $tmp_local_file_path = 'tmp/' . $tmp_zip_file_name;
            $tmp_zip_file_path = storage_path() . '/app/public/' . $tmp_local_file_path;
            $res = $zip->open($tmp_zip_file_path, ZipArchive::CREATE);

            // zipファイルのオープンに成功した場合
            if ($res === true) {
                foreach ($manage_array as $file) {
                    // 圧縮するファイルを指定する
                    $zip->addFile($file['file_full_path'], $file['file_name']);
                }
                // ZIPファイルをクローズ
                $zip->close();
            } else {
                throw new \Exception("Failed to open zip file");
            }

            $local_disk = Storage::disk('public');
            $file_content = $local_disk->get($tmp_local_file_path);

            // ファイルアップロード
            Uploader::uploadToS3($file_content, $tmp_zip_file_name);

            $file = [
                'file_name' => $zip_file_name,
                'file_data' => $file_content,
                // task_result内のseq_noの最大値 + 1
                'task_result_file_seq_no' => TaskResultFile::where('task_result_id', $req->task_result->id)->max('seq_no') + 1
            ];

            $upload_data = array(
                'task_result_id' => $req->task_result->id,
                'user' => $user,
                'business_id'=> RequestModel::where('id', $req->request_id)->pluck('business_id')->first(),
                'file' => $file
            );
            $task_result_file = $this->uploadFile($upload_data);
            $task_result_file->download_url = $this->createDownloadUrl($task_result_file->file_path, $task_result_file->name);

            array_push($manage_array, ['file_name' => $tmp_zip_file_name, 'file_full_path' => $tmp_zip_file_path, 'local_disk_path' => $tmp_local_file_path]);

            // 一時ファイルを削除
            foreach ($manage_array as $file) {
                $local_disk = Storage::disk('public');
                $local_disk->delete($file['local_disk_path']);
            }
            return $task_result_file;
        } catch (\Throwable $th) {
            // 一時ファイルを削除
            foreach ($manage_array as $file) {
                $local_disk = Storage::disk('public');
                $local_disk->delete($file['local_disk_path']);
            }
            throw $th;
        }
    }

    /**
     * データベース検索
     */
    public function search(Request $req)
    {
        $user = \Auth::user();

        // 検索文字列が空文字の場合は条件指定をしない
        $search_word = $this->createNgramString($req->search_word);

        // AbbeyDB
        $result = Abbey::searchBySearchWord($search_word);
        return response()->json([
            'result' => 'success',
            '$search_word' => $result
        ]);
    }

    public function convert(Request $req)
    {
        // 各ファイルのファイル名からチェック名を取得
        // gifデータの縦横サイズを取得
        $list = $req->list;

        for ($i = 0; $i < count($list); $i++) {
            $list[$i]['check_file_name'] = $this->shortHash($list[$i]['name'], $list[$i]['size']).substr($list[$i]['name'], strrpos($list[$i]['name'], '.'));
        }

        return response()->json([
            'file_list' => $list
        ]);
    }

    /**
     * 引数で受け取った文字列の配列の値をつなげ、crc32->16進数でハッシュ化
     */
    private function shortHash(...$arg)
    {
        $data = '';
        foreach ($arg as $key => $value) {
            $data = $data . $value;
        }

        return dechex(crc32($data));
    }

    /**
     * 特定のファイルのチェック
     */
    private function specificFileCheck($file_name)
    {
        // Exclusion hidden file name
        if (mb_substr($file_name, 0, 1) == '.') {
            return false;
        }

        return $this->specificExtensionCheck($file_name);
    }


    private function specificExtensionCheck($file_name)
    {
        $tmp_list = explode('.', $file_name);
        $extension = array_pop($tmp_list);

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'mp4':
                return true;
            default:
                return false;
        }
    }

    /**
     * 検索用の文字列を作成
     */
    private function createNgramString($keyword)
    {
        $keyword_length = mb_strlen($keyword);

        $ngram_byte_length = 2; // bi-gram

        if ($keyword_length < $ngram_byte_length) {
            // 文字数が分割数より少ない場合、前方一致検索にするため * をつけて返す
            return sprintf('%s*', str_replace('"', '\"', $keyword));
        }

        $grams = [];

        // ngarmの数だけずれた文字列の配列を作成
        for ($start = 0; $start <= ($keyword_length - $ngram_byte_length); $start += 1) {
            $target = mb_substr($keyword, $start, $ngram_byte_length);
            $grams[] = $target;
            // $grams[] = "+{$target}";
        }

        // フレーズ検索
        return sprintf('%s', implode(' ', $grams));
        // return sprintf('%s', implode(' ', $grams));
    }

    /**
     * upload file is save to s3
     * @param mixed $data {task_result_id: int, user: User, business_id: int, file: {file_name: string, file_data: base64 | content, task_result_file_seq_no: int}}
     * @param string $type content | base64
     * @return TaskResultFile
     */
    private function uploadFile($data, $type = 'content'): TaskResultFile
    {
        $task_result_id = $data['task_result_id'];
        $user = $data['user'];
        $business_id = $data['business_id'];
        $file = $data['file'];

        $file_name = $file['file_name'];
        $file_path = 'task_result_files/'. $business_id .'/'. Carbon::now()->format('Ymd') .'/'. md5(microtime()) .'/'. $file_name;
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
                throw new Exception('not_type');
        }

        Uploader::uploadToS3($file_contents, $file_path);

        // 登録
        $task_result_file = new TaskResultFile;
        $task_result_file->task_result_id = $task_result_id;
        $task_result_file->name = $file_name;
        $task_result_file->seq_no = $file['task_result_file_seq_no'];
        $task_result_file->file_path = $file_path;
        $task_result_file->created_user_id = $user->id;
        $task_result_file->updated_user_id = $user->id;
        $task_result_file->save();

        return $task_result_file;
    }

    /**
     * 外部からダウンロードするためのURLを生成
     */
    private function createDownloadUrl($url, $file_name)
    {

        $uri = url('utilities/download_allow_file?file_path='.urlencode($url).'&file_name='. urlencode($file_name));

        return $uri;
    }

    public function downloadFromS3(Request $req)
    {
        $tmp_file_name = null;
        try {
            $file_path = $req->file_path;
            $file_name = $req->file_name;
            $disk = \Storage::disk(\Config::get('filesystems.cloud'));
            $file = $disk->get($file_path);

            // S3の完全URLを取得
            $url = $disk->url($file_path);
            // S3上に指定ファイルが存在するか確認
            if (!$disk->exists($file_path)) {
                throw new \Exception('S3Download is failed. File not exists');
            }
            $file_name =  isset($file_name) ? $file_name : basename($url);

            // ローカルに一時保存
            $tmp_disk = Storage::disk('public');
            // (float)1.0 -> 1 になるので配列の2番目を作成するために '.'を付ける
            $microtime_float = explode('.', (microtime(true)).'.');
            $tmp_file_name = $microtime_float[0]. $microtime_float[1] . $file_name;
            $tmp_disk->put($tmp_file_name, $disk->get($file_path));
            $tmp_file_path = storage_path() . '/app/public/'. $tmp_file_name;
            $mime_type = \File::mimeType($tmp_file_path);
            $file_size = filesize($tmp_file_path);

            $data =  base64_encode(file_get_contents($tmp_file_path));
            $src = 'data:' . $mime_type . ';base64,' . $data;

            // 一時ファイルを削除
            $tmp_disk->delete($tmp_file_name);

            return response()->json([
                'result' => 'success',
                'data' => $src,
                'mime_type' => $mime_type,
                'file_size' => $file_size,
            ]);
        } catch (\Exception $e) {
            // 一時ファイルを削除
            $tmp_disk = Storage::disk('public');
            if (isset($tmp_file_name)) {
                $tmp_disk->delete($tmp_file_name);
            }

            report($e);

            return response()->json([
                'result' => 'error',
            ]);
        }
    }
}
