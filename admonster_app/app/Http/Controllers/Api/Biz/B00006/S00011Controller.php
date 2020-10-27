<?php


namespace App\Http\Controllers\Api\Biz\B00006;

use App\Http\Controllers\Api\Biz\BaseController;
use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Models\CompanyEmployee;
use App\Models\ExpenseCarfare;
use App\Models\Queue;
use App\Models\RequestWork;
use App\Models\SendMail;
use App\Models\TaskResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Matrix\Exception;
use Storage;

class S00011Controller extends BaseController
{
    /** @var string メール作成画面 */
    const CONTENT_NODE_KEY_MAIL_MAIN = 'G00000_27';
    /** @var string 作業時間登録 */
    const CONTENT_NODE_KEY_MAIL_WORKTIME = 'G00000_35';
    /** @var string 開始時間 */
    const CONTENT_NODE_KEY_MAIL_WORKTIME_STARTED_AT = 'C00700_36';
    /** @var string 終了時間 */
    const CONTENT_NODE_KEY_MAIL_WORKTIME_FINISHED_AT = 'C00700_37';
    /** @var string 作業時間_Hour */
    const CONTENT_NODE_KEY_MAIL_WORKTIME_TOTAL = 'C00100_38';
    /** @var string ファイルID */
    const CONTENT_NODE_KEY_MAIL_ATTACH_FILE_ID = 'C00800_32';
    /** @var string ファイル添付 */
    const CONTENT_NODE_KEY_MAIL_ATTACH = 'uploadFiles';

    /** @var string 経費処理画面 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN = 'G00000_1';
    /** @var string 『★交通費（AP）メール』のシートに記載がありますか？　はい：0, いいえ：1 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_EXPENSES_TYPE = 'C00500_2';
    /** @var string 社員番号 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_EMPLOYESS_ID = 'C00100_3';
    /** @var string フリガナ */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_SPELL = 'C00100_4';
    /** @var string 氏名 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_NAME = 'C00100_5';
    /** @var string 申請合計金額 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_PRICE = 'C00101_6';
    /** @var string 会計年月 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_DATE = 'C00100_7';
    /** @var string 金額明細の合計と合計金額欄（太枠欄）が合っていますか？　合致：0, 不一致：1 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_AP_ACCORD = 'C00500_8';
    /** @var string 不備 */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED = 'C00400_10';
    /** @var string 不備コメント */
    const CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED_STRING = 'C00200_11';

    /** @var string 交通費PDF upload */
    const CONTENT_NODE_KEY_EP_TWO_MAIN = 'C00800_24';
    /** @var string AP file seq no */
    const CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_ONE = 'C00800_25';
    /** @var string 常驻 file seq no */
    const CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_TWO = 'C00800_26';
    /** @var string AP file upload */
    const CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_ONE = 'C00800_25_uploadFiles';
    /** @var string 常驻 file upload */
    const CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_TWO = 'C00800_26_uploadFiles';

    /** @var string 录入页面标识 */
    const LAST_DISPLAYED_PAGE_INPUT = '1';
    /** @var string pdf上传页面标识 */
    const LAST_DISPLAYED_UPLOAD_INPUT = '1';//pdf上传页面,希望被无视



    /**
     * 页面初始化
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function create(Request $req)
    {
        try {
            $base_info = parent::create($req)->original;

            $task_result_info = MailController::arrayIfNull($base_info, ['task_result_info']);
            if ($task_result_info === null || $task_result_info->content === null) {
                // 1没有作业履历，创建新的作业履历
                // 1-1创建新的作业履历到数据库
                $task_result_po = new TaskResult();
                $task_result_po->task_id = $base_info['task_info']->id;
                $task_result_po->step_id = $base_info['request_info']->step->id;
                $task_result_po->created_user_id = \Auth::user()->id;
                $task_result_po->updated_user_id = \Auth::user()->id;
                // 构造content
                $content_array = [
                    'results' =>
                        [
                            'type' => config('const.TASK_RESULT_TYPE.NOT_WORKING'),
                            'comment' => '',
                            'mail_id' => []
                        ]
                ];
                $json_encode = json_encode($content_array);
                $task_result_po->content = $json_encode;
                $task_result_po->save();

                // 1-2 重新构造 task_result_info
                $base_info['task_result_info'] = $task_result_po;
            } else {
                // 1根据作业履历获取文件
                // 1-1反序列化作业履历的content，得到对象
                $content_array = json_decode($task_result_info->content, true);
                // 1-2获取作业实绩的Id
                $task_result_id = MailController::arrayIfNullFail($base_info, ['task_result_info'], 'task_result_info not exist!', true)->id;
                $mail_controller = new MailController($req);
                $attach_file_array = $mail_controller->getAttachFiles($req);
                // 1-3处理page2的文件
                if (MailController::arrayIfNull($content_array, [self::CONTENT_NODE_KEY_EP_TWO_MAIN,self::CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_ONE])) {
                    // 1-3-1取得『★交通費(AP) メール』 シート 的文件Seq_no集合
                    $ap_file_seq_no_array = MailController::arrayIfNull($content_array, [self::CONTENT_NODE_KEY_EP_TWO_MAIN, self::CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_ONE]);
                    // 1-3-2通过seq_no获取文件的信息
                    $ap_file_info_array = MailController::getTaskResultFile($task_result_id, $ap_file_seq_no_array);
                    // 1-3-3构造文件结点Jsono数据
                    $content_array[self::CONTENT_NODE_KEY_EP_TWO_MAIN][self::CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_ONE] = $ap_file_info_array;
                }
                if (MailController::arrayIfNull($content_array, [self::CONTENT_NODE_KEY_EP_TWO_MAIN,self::CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_TWO])) {
                    // 1-3-4取得『★交通費(常駐)メール』シート 的文件Seq_no集合
                    $permanent_file_seq_no_array = MailController::arrayIfNull($content_array, [self::CONTENT_NODE_KEY_EP_TWO_MAIN, self::CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_TWO]);
                    // 1-3-5通过seq_no获取文件的信息
                    $permanent_file_info_array = MailController::getTaskResultFile($task_result_id, $permanent_file_seq_no_array);
                    // 1-3-6构造文件结点Jsono数据
                    $content_array[self::CONTENT_NODE_KEY_EP_TWO_MAIN][self::CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_TWO] = $permanent_file_info_array;
                }
                $content_array[self::CONTENT_NODE_KEY_EP_TWO_MAIN][self::CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_ONE] = MailController::arrayIfNull($attach_file_array, [self::CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_ONE], []);
                $content_array[self::CONTENT_NODE_KEY_EP_TWO_MAIN][self::CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_TWO] = MailController::arrayIfNull($attach_file_array, [self::CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_TWO], []);
                // 1-4处理page3的文件
                if (MailController::arrayIfNull($content_array, [self::CONTENT_NODE_KEY_MAIL_MAIN,self::CONTENT_NODE_KEY_MAIL_ATTACH_FILE_ID])) {
                    // 1-4-1取得邮件 的文件Seq_no集合
                    $mail_file_seq_no_array = MailController::arrayIfNull($content_array, [self::CONTENT_NODE_KEY_MAIL_MAIN, self::CONTENT_NODE_KEY_MAIL_ATTACH_FILE_ID]);
                    // 1-4-2通过seq_no获取文件的信息
                    $mail_file_info_array = MailController::getTaskResultFile($task_result_id, $mail_file_seq_no_array);
                    // 1-4-3构造文件结点Jsono数据
                    $content_array[self::CONTENT_NODE_KEY_MAIL_MAIN][self::CONTENT_NODE_KEY_MAIL_ATTACH] = $mail_file_info_array;
                }
                $content_array[self::CONTENT_NODE_KEY_MAIL_MAIN][self::CONTENT_NODE_KEY_MAIL_ATTACH] = MailController::arrayIfNull($attach_file_array, [self::CONTENT_NODE_KEY_MAIL_ATTACH], []);
                // 1-6 重新构造 task_result_info
                $base_info['task_result_info']->content = json_encode($content_array);
            }
            return response()->json($base_info);
        } catch (\Exception $e) {
            report($e);
            return MailController::error('初期化失敗しました。');
        }
    }

    /**
     * 従業員情報 自动匹配
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function searchEmployees(Request $request)
    {
        try {
            $keyword = $request->keyword;
            //排他处理
            MailController::exclusiveTask($this->task_id, \Auth::user()->id);

            //获取Task的Company信息
            $request_work = RequestWork::findOrFail($this->request_work_id);
            $business_id = $request_work->request->business->id;

            // 模糊查询
            $query = \DB::table('company_employees')
                ->selectRaw(
                    'employees_id,' .
                    'spell,' .
                    'name'
                );
            $query->where('company_id', $business_id);
            if (!empty($keyword)) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('employees_id', 'like', "%{$keyword}%")
                        ->orWhere('spell', 'like', "%{$keyword}%")
                        ->orWhere('name', 'like', "%{$keyword}%");
                });
            }
            $query->OrderBy('spell', 'asc');
            $data = $query->get();

            return MailController::success($data);
        } catch (\Exception $e) {
            report($e);
            return MailController::error('従業員情報の曖昧検索に失敗しました。');
        }
    }

    /**
     * 保存经费数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveExpenseData(Request $request)
    {
        $this->user = \Auth::user();
        \DB::beginTransaction();
        try {
            $request_work_id = $this->request_work_id;
            $task_id = $this->task_id;
            $step_id = $this->step_id;
            $user_id = \Auth::user()->id;

            $data = $request->task_result_content;
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return MailController::error('task_result_content error');
            }

            // 『★交通費（AP）メール』のシートに記載がありますか？　はい：0, いいえ：1
            $expenses_type = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_EXPENSES_TYPE], 1, true);
            // 金額明細の合計と合計金額欄（太枠欄）が合っていますか？　有：1, 无：0
            $ap_accord = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_ACCORD], 1, true);


            //------------------------------------新输入项目检查--------------------------------
            if ($data[self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_ACCORD] === '') {
                // 入力情報、金額確認？　有：1, 无：0
                return MailController::error('入力情報、金額確認一致性のチェックは必須です。');
            }
            if ($ap_accord === 1) {
                // 入力情報、金額確認？　== 有
                if (!self::forArrayTrue($data[self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED])
                    && empty($data[self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED_STRING])) {
                    return MailController::error('入力情報、金額確認  不備・不明理由を選択して下さい。');
                }
                $check = $request->task_result_content;
                $check = json_decode($check, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return MailController::error('JSONファイルの形式に不備はあります。');
                }
                $check_text = MailController::arrayIfNull($check, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED_STRING]);
                $check_list = MailController::arrayIfNull($check, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED]);
                for ($i = 0; $i < count($check_list); $i++) {
                    if ($check_list[6] === true) {
                        if (empty($check_text)) {
                            return MailController::error('不備・不明理由の”その他”をチェックする時にコメントの入力は必須です。');
                        }
                    }
                }
            }

            // ------------------------------新データ準備--------------------------------
            if ($ap_accord === 0) {
                // 没有不一致，清空
                $data[self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED] = [false,false,false,false,false,false,false];
                $data[self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED_STRING] = "";
            }
            $has_unknown = self::forArrayTrue($data[self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED])
                || !empty($data[self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED_STRING]);

            // データ準備
            $employess_id = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_EMPLOYESS_ID], null, true);
            $spell = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_SPELL], null, true);
            $name = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_NAME], null, true);
            $price = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_PRICE], null, true);
            $date = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_DATE], null, true);

            $ap_unprepared_unknown = [
                // 不備check item （複数可）
                self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED => $data[self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED],
                // 不備コメント
                self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED_STRING => $data[self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED_STRING]
            ];

            if ($expenses_type === '') {
                // 没有交通费
                return MailController::error('交通費は必須です。');
            } else if ($expenses_type !=='' && $ap_accord !== 1) {
                // 有交通费
                //有效性检查
                if (empty($employess_id)) {
                    return MailController::error('社員番号の入力は必須です。');
                }

                if (empty($spell)) {
                    return MailController::error('フリガナの入力は必須です。');
                }

                if (empty($name)) {
                    return MailController::error('氏名の入力は必須です。');
                }

                if (empty($price)) {
                    return MailController::error('申請合計金額の入力は必須です。');
                }
                if (!preg_match(
                    '/(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/',
                    $price
                )) {
                    return MailController::error('申請合計金額の入力に不備はあります。');
                }

                if (empty($date)) {
                    return MailController::error('会計年月の入力は必須です。');
                }

                //，保存用户信息
                $company_info = DB::table('tasks')->select('companies.*')
                    ->leftJoin('request_works', 'tasks.request_work_id', '=', 'request_works.id')
                    ->leftJoin('requests', 'requests.id', '=', 'request_works.request_id')
                    ->leftJoin('businesses', 'businesses.id', '=', 'requests.business_id')
                    ->leftJoin('companies', 'companies.id', '=', 'businesses.company_id')->where('tasks.id', $task_id)->first();
                CompanyEmployee::updateOrCreate(
                    [
                        'company_id' => $company_info->id,
                        'employees_id' => $employess_id === null ? $employess_id : sprintf("%04d", $employess_id)
                    ],
                    [
                        'spell' => $spell,
                        'name' => $name,
                        'created_user_id' => $user_id,
                        'updated_user_id' => $user_id
                    ]
                );
            }

            // ---------------------------------新保存经费信息------------------------------------------
            ExpenseCarfare::updateOrCreate(
                ['task_id' => $task_id],
                [
                    'step_id' => $step_id,
                    'request_work_id' => $request_work_id,
                    'expenses_type' => $expenses_type,
                    'employees_id' => $employess_id === null ? $employess_id : sprintf("%04d", $employess_id),
                    'name' => $name,
                    'spell' => $spell,
                    'price' => is_null($price)? null : (int) $price,
                    'date' => $date,
                    'ap_accord' => $ap_accord,
                    'have_station' => null,
                    'station_accord' => null,
                    'ap_unprepared_unknown' => json_encode($ap_unprepared_unknown, JSON_UNESCAPED_UNICODE),
                    'unprepared_unknown' => null,
                    'created_user_id' => $user_id,
                    'updated_user_id' => $user_id,
                ]
            );

            $taskResultType = config('const.TASK_RESULT_TYPE.HOLD');

            if ($has_unknown === true) {
                $taskResultType = config('const.TASK_RESULT_TYPE.CONTACT');//有不明

                // 获取担当者
                $task_user = \DB::table('tasks')
                    ->select('users.name')
                    ->join('users', 'tasks.user_id', 'users.id')
                    ->where('tasks.id', $task_id)
                    ->where('users.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
                    ->first();
                $task_user = $task_user->name;

                // 获取担当者email
                $mail_address = \DB::table('tasks')
                    ->select('users.email')
                    ->join('users', 'tasks.user_id', 'users.id')
                    ->where('tasks.id', $task_id)
                    ->where('users.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
                    ->first();
                $mail_address = $mail_address->email;

                $check_lists = '';

                $check = $request->task_result_content;
                $check = json_decode($check, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return MailController::error('JSONファイルの形式に不備はあります。');
                }
                $check_list = MailController::arrayIfNull($check, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED]);
                for ($i = 0; $i < count($check_list); $i++) {
                    if ($check_list[$i] === true) {
                        if ($i == 0) {
                            $check_zero = '会計年月記入なし';
                            if (!empty($check_lists)) {
                                $check_lists .= ',';
                            }
                            $check_lists .= $check_zero;
                        }
                        if ($i == 1) {
                            $check_one = '社員番号 記入なし';
                            if (!empty($check_lists)) {
                                $check_lists .= ',';
                            }
                            $check_lists .= $check_one;
                        }
                        if ($i == 2) {
                            $check_two = 'フリガナ 記入なし';
                            if (!empty($check_lists)) {
                                $check_lists .= ',';
                            }
                            $check_lists .= $check_two;
                        }
                        if ($i == 3) {
                            $check_three = '氏名 記入なし';
                            if (!empty($check_lists)) {
                                $check_lists .= ',';
                            }
                            $check_lists .= $check_three;
                        }
                        if ($i == 4) {
                            $check_fore = '明細合計と合計金額が不一致';
                            if (!empty($check_lists)) {
                                $check_lists .= ',';
                            }
                            $check_lists .= $check_fore;
                        }
                        if ($i == 5) {
                            $check_five = 'APシートと常駐シートの両方に記載あり';
                            if (!empty($check_lists)) {
                                $check_lists .= ',';
                            }
                            $check_lists .= $check_five;
                        }
                        if ($i == 6) {
                            $check_six = 'その他';
                            if (!empty($check_lists)) {
                                $check_lists .= ',';
                            }
                            $check_lists .= $check_six;
                        }
                    }
                }

                $check_text = MailController::arrayIfNull($check, [self::CONTENT_NODE_KEY_EP_ONE_MAIN_AP_UNPREPARED_STRING]);

                $step_name = '登録作業';
                $page_url = 'https://ad-monster.jp';
                $content_type = 1;
                $to = config('biz.b00006.MAIL_SETTING.contact_mail_receiver');

                $mail_body_data = [
                    'check_list' => str_replace(',', "\r\n　　　　", $check_lists),
                    'check_text' => $check_text,
                    'mail_address' => $mail_address,
                    'task_user' => $task_user,
                    'step_name' => $step_name,
                    'page_url' => $page_url,
                ];

                // 本文生成
                $mail_body = \View::make('biz.b00006.s00011.emails')->with($mail_body_data);

                $send_mail = new SendMail;
                $send_mail->cc = null;
                $send_mail->request_work_id = $this->request_work_id;
                $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
                $send_mail->subject = '【登録作業_不備・不明】' . $check_lists;
                $send_mail->body = $mail_body;
                $send_mail->created_user_id = \Auth::user()->id;
                $send_mail->updated_user_id = \Auth::user()->id;
                $send_mail->content_type = $content_type;
                $send_mail->to = $to;
                $send_mail->save();

                // 処理キュー登録（承認）
                $queue = new Queue;
                $queue->process_type = config('const.QUEUE_TYPE.APPROVE');
                $queue->argument = json_encode(['request_work_id' => $this->request_work_id]);
                $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
                $queue->created_user_id = \Auth::user()->id;
                $queue->updated_user_id = \Auth::user()->id;
                $queue->save();

                // 処理キュー登録（send mail）
                $queue = new Queue;
                $queue->process_type = config('const.QUEUE_TYPE.MAIL_SEND');
                $queue->argument = json_encode(['mail_id' => (int)$send_mail->id]);
                $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
                $queue->created_user_id = \Auth::user()->id;
                $queue->updated_user_id = \Auth::user()->id;
                $queue->save();

                $data['results']['mail_id'] = [$send_mail->id];
            }

            // 作業時間
            $work_time=['started_at' => null, 'finished_at' => null, 'total' => null];
            $work_time['started_at'] = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_MAIL_WORKTIME,self::CONTENT_NODE_KEY_MAIL_WORKTIME_STARTED_AT]);
            $work_time['finished_at'] = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_MAIL_WORKTIME,self::CONTENT_NODE_KEY_MAIL_WORKTIME_FINISHED_AT]);
            $work_time['total'] = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_MAIL_WORKTIME,self::CONTENT_NODE_KEY_MAIL_WORKTIME_TOTAL]);
            unset($data[self::CONTENT_NODE_KEY_MAIL_WORKTIME]);
            $data[self::CONTENT_NODE_KEY_EP_ONE_MAIN_PRICE] = empty($data[self::CONTENT_NODE_KEY_EP_ONE_MAIN_PRICE]) ? null : (int) $data[self::CONTENT_NODE_KEY_EP_ONE_MAIN_PRICE];
            $mailController = new MailController($request);
            $mailController->partStore(self::LAST_DISPLAYED_PAGE_INPUT, [self::CONTENT_NODE_KEY_EP_ONE_MAIN], $data, [], $taskResultType, $work_time);
            \DB::commit();
            return MailController::success();
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return MailController::error($e->getMessage());
        }
    }

    /**
     * 临时保存经费数据
     * expenses retention
     * @param Request $request
     */
    public function tmpSaveExpenseData(Request $request)
    {
        try {
            $data = $request->task_result_content;
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return MailController::error('JSONファイルの形式に不備はあります。');
            }

            // 作業時間
            $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            $work_time['started_at'] = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_MAIL_WORKTIME, self::CONTENT_NODE_KEY_MAIL_WORKTIME_STARTED_AT]);
            $work_time['finished_at'] = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_MAIL_WORKTIME, self::CONTENT_NODE_KEY_MAIL_WORKTIME_FINISHED_AT]);
            $work_time['total'] = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_MAIL_WORKTIME, self::CONTENT_NODE_KEY_MAIL_WORKTIME_TOTAL]);
            unset($data[self::CONTENT_NODE_KEY_MAIL_WORKTIME]);

            $mailController = new MailController($request);
            $mailController->partStore(self::LAST_DISPLAYED_PAGE_INPUT, [self::CONTENT_NODE_KEY_EP_ONE_MAIN], $data, [], config('const.TASK_RESULT_TYPE.HOLD'), $work_time);
            $mail_controller = new MailController($request);
            return MailController::success($mail_controller->getAttachFiles($request));
        } catch (\Exception $e) {
            report($e);
            return MailController::error('保存失敗しました。');
        }
    }

    /**
     * 交通費PDF upload
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadPdf(Request $req)
    {
        try {
            $data = $req->task_result_content;
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return MailController::error('JSONファイルの形式に不備はあります。');
            }
            // AP file upload data
            $ap_file_upload_array = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_ONE]);
            // 常驻 file upload data
            $permanent_file_upload_array = MailController::arrayIfNull($data, [self::CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_TWO]);

            $maxSeqNo = 0;
            // 处理附件1
            $final_ap_file_po_array = MailController::attachFileUpdate($this->task_id, 'B00006', $ap_file_upload_array);
            //重置seqNo并将最终的文件SeqNo保存到Content中
            $ap_file_seq_no_array = [];
            foreach ($final_ap_file_po_array as $file) {
                $maxSeqNo++;
                $file->seq_no = $maxSeqNo;
                array_push($ap_file_seq_no_array, $maxSeqNo);
            }
            $data[self::CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_ONE] = $ap_file_seq_no_array;
            // 数据库中不保存文件Base64数据，移除节点
            unset($data[self::CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_ONE]);

            // 处理附件2
            $final_permanent_file_po_array = MailController::attachFileUpdate($this->task_id, 'B00006', $permanent_file_upload_array);
            //重置seqNo并将最终的文件SeqNo保存到Content中
            $permanent_file_seq_no_array = [];
            foreach ($final_permanent_file_po_array as $file) {
                $maxSeqNo++;
                $file->seq_no = $maxSeqNo;
                array_push($permanent_file_seq_no_array, $maxSeqNo);
            }
            $data[self::CONTENT_NODE_KEY_EP_TWO_MAIN_SEQ_TWO] = $permanent_file_seq_no_array;
            // 数据库中不保存文件Base64数据，移除节点
            unset($data[self::CONTENT_NODE_KEY_EP_TWO_MAIN_FILES_TWO]);

            //保存
            $mail_controller = new MailController($req);
            $mail_controller->partStore(self::LAST_DISPLAYED_UPLOAD_INPUT, [self::CONTENT_NODE_KEY_EP_TWO_MAIN], $data, array_merge($final_ap_file_po_array, $final_permanent_file_po_array), config('const.TASK_RESULT_TYPE.HOLD'));

            return MailController::success($mail_controller->getAttachFiles($req));
        } catch (\Exception $e) {
            report($e);
            return MailController::error('保存失敗しました。');
        }
    }

    /*
     * 数组中的值判断是否有true
     * @param $array 数组
     * @return bool 存在true，返回true
     */
    private function forArrayTrue($array)
    {
        $flg = 0;
        foreach ($array as $item) {
            if ($item == true) {
                $flg = 1;
                break;
            }
        }
        if ($flg == 1) {
            return true;
        }
        return false;
    }
}
