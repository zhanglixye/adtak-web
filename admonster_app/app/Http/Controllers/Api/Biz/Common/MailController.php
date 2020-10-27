<?php


namespace App\Http\Controllers\Api\Biz\Common;

use App\Http\Controllers\Api\Biz\BaseController;
use App\Models\CommonMailFrequency;
use App\Models\Queue;
use App\Models\RequestMail;
use App\Models\RequestWork;
use App\Models\SendMail;
use App\Models\SendMailAttachment;
use App\Models\Task;
use App\Models\TaskResult;
use App\Models\TaskResultFile;
use App\Services\CommonMail\CommonDownloader;
use App\Services\CommonMail\Replay\ReplyMailInterface;
use App\Services\UploadFileManager\Uploader;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Matrix\Exception;
use ReflectionClass;
use stdClass;
use Storage;

class MailController extends BaseController
{
    /** @var string ファイル名 */
    const ATTACH_FILE_NAME = 'file_name';
    /** @var string ファイルパス */
    const ATTACH_FILE_PATH = 'file_path';
    /** @var string ファイル内容 */
    const ATTACH_FILE_CONTENT = 'file_data';
    /** @var string ファイルseq */
    const ATTACH_FILE_SEQ_NO = 'file_seq_no';
    /** @var string  最後に表示していたページ */
    const CONTENT_NODE_KEY_LAST_DISPLAYED_PAGE = "lastDisplayedPage";

    /** @var string 邮件页面标识(用于在一个业务有多个页面时候定位页面使用，对应于jsonfile的lastDisplayedPage） */
    public $MAIL_PAGE_ID;

    /** @var array  客户端上传的邮件附件在请求报文中的key路径，多级路径定义为数组的多个元素 */
    public $MAIL_CLIENT_UPLOAD_ATTACH_KEY;

    /** @var array 客户端上传的作业时间在请求报文中的key路径，多级路径定义为数组的多个元素 */
    public $MAIL_CLIENT_TIME_MAIN;
    /** @var string 客户端上传的作业开始时间在请求报文中的key路径(相对于MAIL_CLIENT_TIME_MAIN) */
    public $MAIL_CLIENT_TIME_START_AT;
    /** @var string 客户端上传的作业完成时间在请求报文中的key路径(相对于MAIL_CLIENT_TIME_MAIN) */
    public $MAIL_CLIENT_TIME_FINISHED_AT;
    /** @var string 客户端上传的作业总时间在请求报文中的key路径(相对于MAIL_CLIENT_TIME_MAIN) */
    public $MAIL_CLIENT_TIME_TOTAL;

    /** @var array  邮件在作业实绩JsonFile中的根路径 */
    public $MAIL_CONTENT_MAIN;
    /** @var array  MAIL_TO在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素 */
    public $MAIL_CONTENT_TO;
    /** @var array  邮件cc在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素 */
    public $MAIL_CONTENT_CC;
    /** @var array  邮件标题在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素 */
    public $MAIL_CONTENT_SUBJECT;
    /** @var array  邮件本文在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素 */
    public $MAIL_CONTENT_BODY;
    /** @var array  邮件附件在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素 */
    public $MAIL_CONTENT_ATTACH_KEY;
    /** @var array  作業内容にCHECK_LIST在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素 */
    public $MAIL_CONTENT_CHECK_LIST;
    /** @var array 「不明あり」で処理します,担当者へのコメント在作业实绩JsonFile中的key路径(相对于MAIL_CONTENT_MAIN)，多级路径定义为数组的多个元素 */
    public $MAIL_CONTENT_UNKNOWN;

    /** @var array 其它页面的附件在作业实绩JsonFile中的key路径，多级路径定义为数组的多个元素 */
    public $MAIL_OTHER_PAGE_ATTACH_KEY;
    /** @var array 保存邮件后返回的客户端的附件列表 */
    public $MAIL_RETURN_ATTACH_KEY;
    /** @var string B00000 */
    public $BUSINESS_ID;
    /** @var string b00000 */
    public $business_id;


    public function __construct(Request $req)
    {
        parent::__construct($req);
        $ext_info_mixed = self::getExtInfoById($this->task_id);
        $this->business_id = sprintf("b%05d", $ext_info_mixed->business_id);
        $this->BUSINESS_ID = sprintf("B%05d", $ext_info_mixed->business_id);
        $businessId = $this->business_id;
        $stepId = sprintf("s%05d", $ext_info_mixed->step_id);
        $mail_config_root = "biz.${businessId}.MAIL_CONFIG.${stepId}";
        if ($req->page_code !== null) {
            //同一个stepId中包含多个邮件页面时，通过指定的页面标识获取邮件配置参数
            $mail_config_root = $mail_config_root . "." . $req->page_code;
        }
        $this->MAIL_PAGE_ID = \Config::get($mail_config_root . '.MAIL_PAGE_ID');

        $this->MAIL_CLIENT_UPLOAD_ATTACH_KEY = \Config::get($mail_config_root . '.MAIL_CLIENT_UPLOAD_ATTACH_KEY');

        $this->MAIL_CLIENT_TIME_MAIN = \Config::get($mail_config_root . '.MAIL_CLIENT_TIME_MAIN');
        $this->MAIL_CLIENT_TIME_START_AT = \Config::get($mail_config_root . '.MAIL_CLIENT_TIME_START_AT');
        $this->MAIL_CLIENT_TIME_FINISHED_AT = \Config::get($mail_config_root . '.MAIL_CLIENT_TIME_FINISHED_AT');
        $this->MAIL_CLIENT_TIME_TOTAL = \Config::get($mail_config_root . '.MAIL_CLIENT_TIME_TOTAL');

        $this->MAIL_CONTENT_MAIN = \Config::get($mail_config_root . '.MAIL_CONTENT_MAIN');
        $this->MAIL_CONTENT_TO = \Config::get($mail_config_root . '.MAIL_CONTENT_TO');
        $this->MAIL_CONTENT_CC = \Config::get($mail_config_root . '.MAIL_CONTENT_CC');
        $this->MAIL_CONTENT_SUBJECT = \Config::get($mail_config_root . '.MAIL_CONTENT_SUBJECT');
        $this->MAIL_CONTENT_BODY = \Config::get($mail_config_root . '.MAIL_CONTENT_BODY');
        $this->MAIL_CONTENT_ATTACH_KEY = \Config::get($mail_config_root . '.MAIL_CONTENT_ATTACH_KEY');
        $this->MAIL_CONTENT_CHECK_LIST = \Config::get($mail_config_root . '.MAIL_CONTENT_CHECK_LIST');
        $this->MAIL_CONTENT_UNKNOWN = \Config::get($mail_config_root . '.MAIL_CONTENT_UNKNOWN');

        $this->MAIL_OTHER_PAGE_ATTACH_KEY = \Config::get($mail_config_root . '.MAIL_OTHER_PAGE_ATTACH_KEY');
        $this->MAIL_RETURN_ATTACH_KEY = \Config::get($mail_config_root . '.MAIL_RETURN_ATTACH_KEY');
    }

    /**
     * 获取默认的mailSetting
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getCommonMailSettingByTaskId(Request $req)
    {
        try {
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 取得默认mailSetting
            $mail_setting = $this->getMailImplementsByPrefix($task_id)->getMailSetting($task_id);
            return $this->success($mail_setting);
        } catch (\Exception $e) {
            report($e);
            return self::error('メール共通処理設定の取得に失敗しました。');
        }
    }

    /**
     * 取得默认的收件人
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getDefaultMailTo(Request $req)
    {
        try {
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 取得默认收件人
            $mail_to = $this->getMailImplementsByPrefix($task_id)->getDefaultMailTo($task_id);
            return $this->success(['mailTo' => $mail_to]);
        } catch (\Exception $e) {
            report($e);
            return self::error('mailToの取得に失敗しました。');
        }
    }

    /**
     * 取得默认的抄送人
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getDefaultMailCc(Request $req)
    {
        try {
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 取得默认抄送人
            $mail_cc = $this->getMailImplementsByPrefix($task_id)->getDefaultMailCc($task_id);
            return $this->success(['mailCc' => $mail_cc]);
        } catch (\Exception $e) {
            report($e);
            return self::error('mailCcの取得に失敗しました。');
        }
    }

    /**
     * 获取默认的标题.
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getDefaultSubject(Request $req)
    {
        try {
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取默认的标题
            $subject = $this->getMailImplementsByPrefix($task_id)->getDefaultSubject($task_id, $req->time_zone);
            return $this->success(['mailSubject' => $subject]);
        } catch (\Exception $e) {
            report($e);
            return self::error('件名情報の取得に失敗しました。');
        }
    }

    /**
     * 获取默认的本文.
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getDefaultBody(Request $req)
    {
        try {
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取默认的本文
            $body = $this->getMailImplementsByPrefix($task_id)->getDefaultBody($task_id);
            return $this->success(['mailBody' => $body]);
        } catch (\Exception $e) {
            report($e);
            return self::error('本文情報の取得に失敗しました。');
        }
    }

    /**
     * 获取默认的不明.
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getDefaultUnknown(Request $req)
    {
        try {
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取默认的不明
            $unknown = $this->getMailImplementsByPrefix($task_id)->getDefaultUnknown($task_id);
            return $this->success(['unknown' => $unknown]);
        } catch (\Exception $e) {
            report($e);
            return self::error('不明情報の取得に失敗しました。');
        }
    }

    /**
     * 获取默认的作業時間.
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getDefaultUseTime(Request $req)
    {
        try {
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取默认的作業時間
            $use_time = $this->getMailImplementsByPrefix($task_id)->getDefaultUseTime($task_id);
            return $this->success(['useTime' => $use_time]);
        } catch (\Exception $e) {
            report($e);
            return self::error('作業時間の取得に失敗しました。');
        }
    }

    /**
     * 获取チェックリスト項目
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getDefaultChecklistValues(Request $req)
    {
        try {
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取默认的作業時間
            $default_checklist_value_array = $this->getMailImplementsByPrefix($task_id)->getDefaultChecklistValues($task_id);
            return $this->success(['defaultChecklistValues' => $default_checklist_value_array]);
        } catch (\Exception $e) {
            report($e);
            return self::error('チェックリスト項目の取得に失敗しました。');
        }
    }

    /**
     * 获取default template.
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getDefaultBodyTemplates(Request $req)
    {
        try {
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取Template
            $templates = $this->getMailImplementsByPrefix($task_id)->getDefaultBodyTemplates($task_id);
            return $this->success($templates);
        } catch (\Exception $e) {
            report($e);
            return self::error('本文テンプレート情報の取得に失敗しました。');
        }
    }

    /**
     * 获取template.
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getBodyTemplates(Request $req)
    {
        try {
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取Template
            $templates = $this->getMailImplementsByPrefix($task_id)->getBodyTemplates($task_id);
            return $this->success($templates);
        } catch (\Exception $e) {
            report($e);
            return self::error('本文テンプレート情報の取得に失敗しました。');
        }
    }

    /**
     * 获取default署名テンプレート
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getDefaultSignTemplates(Request $req)
    {
        try {
            $user = \Auth::user();
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取Template
            $templates = $this->getMailImplementsByPrefix($task_id)->getDefaultSignTemplates($task_id, $user->id);
            return $this->success($templates);
        } catch (\Exception $e) {
            report($e);
            return self::error('署名テンプレート情報の取得に失敗しました。');
        }
    }

    /**
     * 获取署名テンプレート
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getSignTemplates(Request $req)
    {
        try {
            $user = \Auth::user();
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取Template
            $templates = $this->getMailImplementsByPrefix($task_id)->getSignTemplates($task_id, $user->id);
            return $this->success($templates);
        } catch (\Exception $e) {
            report($e);
            return self::error('署名情報の取得に失敗しました。');
        }
    }

    /**
     * 获取default添付メール
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getDefaultAttachments(Request $req)
    {
        try {
            $user = \Auth::user();
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取Template
            $attachments = $this->getMailImplementsByPrefix($task_id)->getDefaultAttachments($task_id, $user->id);
            return $this->success($attachments);
        } catch (\Exception $e) {
            report($e);
            return self::error('添付ファイルの取得に失敗しました。');
        }
    }

    /**
     * 获取チェックリスト項目
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function getChecklistItems(Request $req)
    {
        try {
            $user = \Auth::user();
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取Template
            $items = $this->getMailImplementsByPrefix($task_id)->getChecklistItems($task_id, $user->id);
            return $this->success($items);
        } catch (\Exception $e) {
            report($e);
            return self::error('チェックリストの取得に失敗しました。');
        }
    }

    /**
     * 获取MailTo頻度
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function searchMailToFrequencyList(Request $req)
    {
        try {
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取Template
            $items = $this->getMailImplementsByPrefix($task_id)->searchMailToFrequencyList($task_id, $req->keyword);

            return $this->success($items);
        } catch (\Exception $e) {
            report($e);
            return self::error('MailTo頻度情報の取得に失敗しました。');
        }
    }

    /**
     * 获取MailCc頻度
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function searchMailCcFrequencyList(Request $req)
    {
        try {
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取Template
            $items = $this->getMailImplementsByPrefix($task_id)->searchMailCcFrequencyList($task_id, $req->keyword);
            return $this->success($items);
        } catch (\Exception $e) {
            report($e);
            return self::error('MailCc頻度情報の取得に失敗しました。');
        }
    }

    /**
     * 临时保存邮件
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function tmpSaveMail(Request $req)
    {
        try {
            $data = json_decode($req->task_result_content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error("JSONファイルの形式に不備はあります。");
            }
            // 作業時間
            $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            $work_time['started_at'] = $this->arrayIfNull($data, array_merge($this->MAIL_CLIENT_TIME_MAIN, [$this->MAIL_CLIENT_TIME_START_AT]));
            $work_time['finished_at'] = $this->arrayIfNull($data, array_merge($this->MAIL_CLIENT_TIME_MAIN, [$this->MAIL_CLIENT_TIME_FINISHED_AT]));
            $work_time['total'] = $this->arrayIfNull($data, array_merge($this->MAIL_CLIENT_TIME_MAIN, [$this->MAIL_CLIENT_TIME_TOTAL]));
            // 增量处理用户提交的附件
            $final_file_po_array = $this->attachFileUpdate(
                $this->task_id,
                $this->BUSINESS_ID,
                $this->arrayIfNull($data, $this->MAIL_CLIENT_UPLOAD_ATTACH_KEY, [])
            );

            //将最终的文件SeqNo保存到Content中
            $final_file_seq_no_array = [];
            foreach ($final_file_po_array as $file) {
                array_push($final_file_seq_no_array, $file->seq_no);
            }
            self::arrayKeySet($data, $this->MAIL_CONTENT_ATTACH_KEY, $final_file_seq_no_array);

            // 在jsonfile中移除用户提交的附件的节点
            self::arrayKeyUnset($data, $this->MAIL_CLIENT_UPLOAD_ATTACH_KEY);

            $this->partStore($this->MAIL_PAGE_ID, $this->MAIL_CONTENT_MAIN, $data, $final_file_po_array, \Config::get('const.TASK_RESULT_TYPE.HOLD'), $work_time);
            return $this->success(self::getAttachFiles($req));
        } catch (\Exception $e) {
            report($e);
            return self::error('保存失敗しました。');
        }
    }

    /**
     * 入力検証
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function inputValidation(Request $req)
    {
        try {
            $data = json_decode($req->task_result_content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error("JSONファイル形式に不備はあります。");
            }
            $task_id = $req->task_id;
            // 获取Template
            $result = $this->getMailImplementsByPrefix($task_id)->inputValidation($task_id, \Auth::user()->id, $data);
            return response()->json($result);
        } catch (\Exception $e) {
            report($e);
            return self::error('入力の検証失敗しました。');
        }
    }

    /**
     * 保存邮件
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function saveMail(Request $req)
    {
        try {
            $data = json_decode($req->task_result_content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error("JSONファイル形式に不備はあります。");
            }
            $mail_to_account_array = [];
            $mail_cc_account_array = [];
            //有效性检查
            if (empty(self::arrayIfNull($data, $this->MAIL_CONTENT_UNKNOWN))) {
                // MAIL TO
                if ($this->arrayIfNull($data, $this->MAIL_CONTENT_TO) == null) {
                    return self::error('Mail toの入力は必須です。');
                }

                // MAIL Subject
                if ($this->arrayIfNull($data, $this->MAIL_CONTENT_SUBJECT) == null) {
                    return self::error('件名の入力は必須です。');
                }

                // MAIL body
                if ($this->arrayIfNull($data, $this->MAIL_CONTENT_BODY) == null) {
                    return self::error('本文の入力は必須です。');
                }

                //MAIL work_time
                $work_time['total'] = $this->arrayIfNull($data, array_merge($this->MAIL_CLIENT_TIME_MAIN, [$this->MAIL_CLIENT_TIME_TOTAL]));
                $work_time_info = (double)$work_time['total'];
                if ($work_time_info < 0) {
                    return self::error('作業時間のご入力に不備はあります。ご確認ください。');
                }

                // MAIL 作業内容に問題が無いか確認してください
                $result = $this->getMailImplementsByPrefix($req->task_id)->inputValidation($req->task_id, \Auth::user()->id, $data);
                if (self::arrayIfNull($result, ['result'], 'error') == 'error') {
                    return self::error(self::arrayIfNull($result, ['err_message']));
                }
//                $check_list_group_array = $this->arrayIfNull($data, $this->MAIL_CONTENT_CHECK_LIST, []);
//                if (!empty($check_list_group_array)) {
//                    foreach ($check_list_group_array as $check_list_group) {
//                        if (!empty($check_list_group) && is_array($check_list_group)) {
//                            $check_list_item_array = $this->arrayIfNull($check_list_group, ['items'], []);
//                            foreach ($check_list_item_array as $check_list_item) {
//                                if ($this->arrayIfNull($check_list_item, ['value'], null, true) === null) {
//                                    return self::error('チェックリストの入力を完了してから進めてください。');
//                                }
//                            }
//                        }
//                    }
//                }


                $mail_to_account_array = $this->arrayIfNullFail($data, $this->MAIL_CONTENT_TO, '[mail to]の入力は必須です。');
                $mail_cc_account_array = $this->arrayIfNull($data, $this->MAIL_CONTENT_CC, []);
            }
            // 作業時間
            $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            $work_time['started_at'] = $this->arrayIfNull($data, array_merge($this->MAIL_CLIENT_TIME_MAIN, [$this->MAIL_CLIENT_TIME_START_AT]));
            $work_time['finished_at'] = $this->arrayIfNull($data, array_merge($this->MAIL_CLIENT_TIME_MAIN, [$this->MAIL_CLIENT_TIME_FINISHED_AT]));
            $work_time['total'] = $this->arrayIfNull($data, array_merge($this->MAIL_CLIENT_TIME_MAIN, [$this->MAIL_CLIENT_TIME_TOTAL]));
            // 增量处理用户提交的附件
            $final_file_po_array = self::attachFileUpdate(
                $this->task_id,
                $this->BUSINESS_ID,
                $this->arrayIfNull($data, $this->MAIL_CLIENT_UPLOAD_ATTACH_KEY, [])
            );

            //将最终的文件SeqNo保存到Content中
            $final_file_seq_no_array = [];
            foreach ($final_file_po_array as $file) {
                array_push($final_file_seq_no_array, $file->seq_no);
            }
            self::arrayKeySet($data, $this->MAIL_CONTENT_ATTACH_KEY, $final_file_seq_no_array);
            self::arrayKeyUnset($data, $this->MAIL_CLIENT_UPLOAD_ATTACH_KEY);

            if (empty(self::arrayIfNull($data, $this->MAIL_CONTENT_UNKNOWN))) {
                $this->partStore($this->MAIL_PAGE_ID, $this->MAIL_CONTENT_MAIN, $data, $final_file_po_array, \Config::get('const.TASK_RESULT_TYPE.DONE'), $work_time);
                //更新邮件地址使用频率
                try {
                    $extInfo = self::getExtInfoById($this->task_id, \Auth::user()->id);
                    foreach ($mail_to_account_array as $mail_to_account) {
                        if (!empty($mail_to_account)) {
                            CommonMailFrequency::upTimes($extInfo->business_id, $extInfo->company_id, $extInfo->step_id, $mail_to_account, null, 1, 0, \Auth::user()->id);
                        }
                    }
                    foreach ($mail_cc_account_array as $mail_cc_account) {
                        if (!empty($mail_cc_account)) {
                            CommonMailFrequency::upTimes($extInfo->business_id, $extInfo->company_id, $extInfo->step_id, $mail_cc_account, null, 0, 1, \Auth::user()->id);
                        }
                    }
                } catch (\Throwable $e) {
                    //忽略邮件地址使用频率更新的异常
                }
            } else {
                //「不明あり」で処理します
                $this->partStore($this->MAIL_PAGE_ID, $this->MAIL_CONTENT_MAIN, $data, $final_file_po_array, \Config::get('const.TASK_RESULT_TYPE.CONTACT'), $work_time);
            }

            return $this->success(self::getAttachFiles($req));
        } catch (\Exception $e) {
            report($e);
            return self::error('保存失敗しました。');
        }
    }

    /**
     * 保存署名テンプレート
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function saveSignTemplates(Request $req)
    {
        try {
            $user = \Auth::user();
            $task_id = $req->task_id;
            $data = json_decode($req->task_result_content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error("JSONファイルの形式に不備はあります。");
            }

            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 保存署名テンプレート
            $templates = $this->getMailImplementsByPrefix($task_id)->saveSignTemplates($task_id, $user->id, $data);
            return $this->success($templates);
        } catch (\Exception $e) {
            report($e);
            return self::error('保存失敗しました。');
        }
    }

    /**
     * 保存本文テンプレート
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \ReflectionException
     */
    public function saveBodyTemplates(Request $req)
    {
        try {
            $user = \Auth::user();
            $task_id = $req->task_id;
            $data = json_decode($req->task_result_content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error("JSONファイルの形式に不備はあります。");
            }

            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 保存本体テンプレート
            $templates = $this->getMailImplementsByPrefix($task_id)->saveBodyTemplates($task_id, $user->id, $data);
            return $this->success($templates);
        } catch (\Exception $e) {
            report($e);
            return self::error('保存失敗しました。');
        }
    }

    /**
     * 下载文件
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function downloadFile(Request $req)
    {
        try {
            // 最新の作業履歴
            Task::where('id', $req->task_id)
                ->where('user_id', \Auth::user()->id)
                ->firstOrFail();
            $task_result_po = TaskResult::with('taskResultFiles')
                ->where('task_id', $req->task_id)
                ->orderBy('id', 'desc')
                ->firstOrFail();
            foreach ($task_result_po->taskResultFiles as $file) {
                if ($file->seq_no === (int)$req->file_seq_no) {
                    $fileData = CommonDownloader::base64FileFromS3($file->file_path, $file->name);
                    $result = [self::ATTACH_FILE_CONTENT => $fileData[0], self::ATTACH_FILE_NAME => $file->name];
                    return $this->success($result);
                }
            }
            return $this->error("ファイルは存在しません。");
        } catch (\Exception $e) {
            report($e);
            return self::error('失敗しました。');
        }
    }

    public static function downloadPdf(Request $req)
    {
        $file_path = $req->file_path;
        $file_name = $req->file_name;
        $disk = Storage::disk('biz');

        // ファイルのURLを取得
        $url = $disk->url($file_path);

        // 指定ファイルが存在するか確認
        if (!$disk->exists($file_path)) {
            throw new \Exception('Download is failed. File not exists');
        }

        $file_name = isset($file_name) ? $file_name : basename($url);

        // ローカルに一時保存
        $tmp_disk = Storage::disk('public');
        $tmp_file_name = time() . $file_name;
        $tmp_disk->put($tmp_file_name, $disk->get($file_path));
        $tmp_file_path = storage_path() . '/app/public/' . $tmp_file_name;
        $mime_type = \File::mimeType($tmp_file_path);

        mb_http_output("pass");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . $file_name);
        header("Content-Type: " . "application/octet-stream");
        // header("Content-Type: " . $mime_type);

        // ファイルの内容を出力する前に入力バッファの中身をクリアする
        ob_end_clean();

        // ダウンロード
        readfile($tmp_file_path);

        // 一時ファイルを削除
        $tmp_disk->delete($tmp_file_name);

        exit;
    }

    public function getWorktime(Request $req)
    {
        // 作業時間
        $work_time = ['started_at' => null, 'finished_at' => null, 'work_time' => null];
        // 最新の作業履歴
        $task_result_info = TaskResult::where('task_id', $this->task_id)
            ->orderBy('id', 'desc')
            ->first();
        if ($task_result_info !== null) {
            $work_time['started_at'] = $task_result_info->started_at;
            $work_time['finished_at'] = $task_result_info->finished_at;
            $work_time['work_time'] = $task_result_info->work_time;
        }
        return self::success($work_time);
    }

    /**
     * 添付ファイルに許可されているファイルタイプを取得する
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommonMailAttachmentTypeByTaskId(Request $req)
    {
        try {
            $task_id = $req->task_id;
            // 取得路由前缀
            $prefix = $req->route()->getPrefix();
            // 获取Template
            $templates = $this->getMailImplementsByPrefix($task_id)->getCommonMailAttachmentTypeByTaskId($task_id);
            return $this->success($templates);
        } catch (\Exception $e) {
            report($e);
            return self::error('本文テンプレート情報の取得に失敗しました。');
        }
    }

    //--------------------------------static Common  ----------------------------------------------//

    /**
     * 上传文件到s3
     * @param array $data 包含所有要上传文件的数组
     * @param string $type base64:文件内容以base64方式获取, content:文件内容以byte方式获取
     * @return string file path
     * @throws Exception
     */
    public static function uploadFile(array $data, string $type = 'content'): string
    {
        $business_id = $data['business_id'];
        $file = $data['file'];

        $file_name = $file[self::ATTACH_FILE_NAME];
        $file_path = 'task_result_files/' . $business_id . '/' . Carbon::now()->format('Ymd') . '/' . md5(microtime()) . '/' . $file_name;


        switch ($type) {
            case 'base64':
                // file data is decode to base64
                list(, $fileData) = explode(';', $file[self::ATTACH_FILE_CONTENT]);
                list(, $fileData) = explode(',', $fileData);
                $file_contents = base64_decode($fileData);
                break;
            case 'content':
                $file_contents = $file[self::ATTACH_FILE_CONTENT];
                break;
            default:
                throw new Exception('not_type');
        }

        Uploader::uploadToS3($file_contents, $file_path);

        return $file_path;
    }

    /**
     * ファイルをs3にアップロードする.
     * @param array $data アップロードするすべてのファイルを含む配列
     * @param string $type base64:ファイルコンテンツはbase64で取得されます, content:ファイルの内容はバイト単位で取得されます
     * @param bool $return_detail_info 詳細を返すかどうか(file_size,mini_type,src)
     * @return array (file_name, file_path, url, file_size, mime_type) ファイル情報
     * @throws \Exception アップロードに失敗しました
     */
    public static function uploadFileDetail(array $data, string $type = 'content', bool $return_detail_info = false): array
    {
        $business_id = $data['business_id'];
        $file = $data['file'];

        $file_name = $file['file_name'];
        $file_path = 'task_result_files/' . $business_id . '/' . Carbon::now()->format('Ymd') . '/' . md5(microtime()) . '/' . $file_name;
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

        $url = Uploader::uploadToS3($file_contents, $file_path);
        if ($return_detail_info) {
            list($src, $mime_type, $file_size, $data) = CommonDownloader::base64FileFromS3($file_path);
        } else {
            $mime_type = null;
            $file_size = null;
            $src = null;
        }
        return array(
            'file_name' => $file_name,
            'file_path' => $file_path,
            'url' => $url,
            'display_size' => '', //TODO 課題ADTAKT_PF-4
            'file_size' => $file_size,
            'mime_type' => $mime_type,
            'src' => $src
        );
    }

    /**
     * s3サーバーへのファイルのアップロードとDBへの保存
     * @param int $task_id 作業ID
     * @param string $business_id 業務ID
     * @param array $fileArray ファイル配列,(例: [['file_name'=>'xxx','file_data' => 'data:image/jpg;base64,xxxxxx']])
     * @return array タスク実績（ファイル）の配列
     * @throws \Throwable
     */
    public static function uploadFileAndSave(int $task_id, string $business_id, array $fileArray): array
    {
        \DB::beginTransaction();
        try {
            // 排他处理
            self::exclusiveTask($task_id, \Auth::user()->id);

            // ファイル添付処理の結果
            $resultArray = [];

            //ファイル添付 存在する
            if (!empty($fileArray)) {
                // 最大のファイルシリアル番号
                $max_seq_no = 0;
                // 最新の作業履歴
                $taskResult = TaskResult::with('taskResultFiles')
                    ->where('task_id', $task_id)
                    ->orderBy('id', 'desc')
                    ->first();
                $task_result_file_array = $taskResult->taskResultFiles->toArray();
                foreach ($task_result_file_array as $task_result_file) {
                    if ($max_seq_no < $task_result_file['seq_no']) {
                        $max_seq_no = $task_result_file['seq_no'];
                    }
                }

                // アップロードされたすべてのファイルを処理する
                foreach ($fileArray as $file) {
                    self::arrayIfNull($file, [self::ATTACH_FILE_NAME], null, true);
                    $max_seq_no++;
                    $file_name = self::arrayIfNullFail($file, [self::ATTACH_FILE_NAME], "Invalid file_name in json", true);
                    $file_path = 'task_result_files/' . $business_id . '/' . Carbon::now()->format('Ymd') . '/' . md5(microtime()) . '/' . $file_name;
                    $file_content = self::arrayIfNullFail($file, [self::ATTACH_FILE_CONTENT], "Invalid file_data in json", true);
                    // base64 decode
                    list(, $file_content_tmp) = explode(';', $file_content);
                    list(, $file_data) = explode(',', $file_content_tmp);
                    $file_content_bytes = base64_decode($file_data);
                    // upload to S3 and save to DB
                    $task_result_file_po = Uploader::tryUploadAndSave($file_content_bytes, $file_path, 'App\Models\TaskResultFile', ['seq_no' => $max_seq_no, 'task_result_id' => $taskResult->id]);
                    //push to result arrays
                    array_push($resultArray, $task_result_file_po);
                }
            }

            \DB::commit();
            return $resultArray;
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * 通过TaskId和userId取得Task的关联信息
     * @param int $task_id タスクId
     * @param int $current_user_id ユーザーID
     * @return mixed
     */
    public static function getExtInfoById(int $task_id, int $current_user_id = -1)
    {
        $query = \DB::table('businesses')
            ->selectRaw(
                'businesses.id business_id,' .
                'businesses.company_id,' .
                'request_works.step_id'
            )->join('requests', 'businesses.id', '=', 'requests.business_id')
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->join('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            ->where('tasks.id', $task_id)
            ->where('businesses.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('requests.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('request_works.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'))
            ->where('tasks.is_active', \Config::get('const.FLG.ACTIVE'));
        if ($current_user_id >= 0) {
            $query = $query->where('tasks.user_id', $current_user_id);
        }
        return $query->first();
    }

    //--------------------------------private common ----------------------------------------------//

    /**
     * 通过作业履历ID获取指定的文件
     * @param int $taskResultId 作业履历ID
     * @param array $seqNoArray 文件序号的数组
     * @return array 文件数组
     */
    public static function getTaskResultFile(int $taskResultId, array $seqNoArray): array
    {
        $fileArray = [];
        foreach ($seqNoArray as $seqNo) {
            $taskResultFile = TaskResultFile::where('task_result_id', $taskResultId)
                ->where('seq_no', $seqNo)
                ->firstOrFail();
            $fileData = CommonDownloader::base64FileFromS3($taskResultFile[self::ATTACH_FILE_PATH], $taskResultFile['name'])[0];
            $file[self::ATTACH_FILE_NAME] = $taskResultFile['name'];
            $file[self::ATTACH_FILE_PATH] = $taskResultFile[self::ATTACH_FILE_PATH];
            $file[self::ATTACH_FILE_SEQ_NO] = $seqNo;
            array_push($fileArray, $file);
        }
        return $fileArray;
    }

    /**
     * タスク実績（ファイル）を取得
     * @param int $task_result_id タスク実績ID
     * @param array $seq_no_array seqNo配列
     * @return mixed|null|array|object タスク実績（ファイル）
     */
    public static function getTaskResultFileByIdArray(int $task_result_id, array $seq_no_array)
    {
        $task_result_file = TaskResultFile::where('task_result_id', $task_result_id)
            ->whereIn('seq_no', $seq_no_array)
            ->get();
        return $task_result_file;
    }

    /**
     * タスク実績（ファイル）を取得
     * @param int $task_result_id タスク実績ID
     * @param int $seq_no seqNo
     * @return mixed タスク実績（ファイル）
     */
    public static function getTaskResultFileById(int $task_result_id, int $seq_no)
    {
        $task_result_file = TaskResultFile::where('task_result_id', $task_result_id)
            ->where('seq_no', $seq_no)
            ->firstOrFail();
        return $task_result_file;
    }



    /**
     * 配列のNULL処理.
     * @param array $array 配列
     * @param array $key_array キー配列，例（['top','sec'] 対応 ['top' => ['sec' => 'aaaa']])
     * @param mixed|null|array|object $default キーが存在しない場合に使用するデフォルト値
     * @param bool $empty_check null値を確認するかどうか
     * @return mixed|null|array|object キーが存在する場合、対応する値が返されます。それ以外の場合、指定されたデフォルト値が返されます
     */
    public static function arrayIfNull(array $array, array $key_array, $default = null, bool $empty_check = false)
    {
        if (empty($array)) {
            return $default;
        }
        $value = $array;
        foreach ($key_array as $key) {
            if (!is_array($value)) {
                return $default;
            }
            if (array_key_exists($key, $value)) {
                $value = $value[$key];
                if ($empty_check) {
                    if (empty($value)) {
                        if ($value === 0 || $value === 0.0 || $value === '0') {
                            //0 is not empty
                            continue;
                        }
                        return $default;
                    }
                }
            } else {
                return $default;
            }
        }
        return $value;
    }

    /**
     * 配列のNULL処理,キーが存在しない場合、例外をスローします.
     * @param array $array 配列
     * @param array $key_array キー配列，例（['top','sec'] 対応 ['top' => ['sec' => 'aaaa']])
     * @param string $fail_msg キーが存在しない場合の例外メッセージ
     * @param bool $empty_check null値を確認するかどうか
     * @return array|mixed キーが存在する場合、対応する値が返されます。それ以外の場合、指定された例外メッセージが返されます
     * @throws \Exception キーが存在しないか、値が空です
     */
    public static function arrayIfNullFail(array $array, array $key_array, string $fail_msg = 'null point exception', bool $empty_check = false)
    {
        if (empty($array)) {
            throw new \Exception($fail_msg);
        }
        $value = $array;
        foreach ($key_array as $key) {
            if (!is_array($value)) {
                throw new \Exception($fail_msg);
            }
            if (array_key_exists($key, $value)) {
                $value = $value[$key];
                if ($empty_check) {
                    if (empty($value)) {
                        if ($value === 0 || $value === 0.0 || $value === '0') {
                            //0 is not empty
                            continue;
                        }
                        throw new \Exception($fail_msg);
                    }
                }
            } else {
                throw new \Exception($fail_msg);
            }
        }
        return $value;
    }

    public static function arrayKeyUnset(&$array, $key_array)
    {
        if (empty($array) || empty($key_array)) {
            return;
        }
        $value = &$array;
        for ($i = 0; $i < count($key_array) - 1; $i++) {
            if (array_key_exists($key_array[$i], $value)) {
                $value = &$value[$key_array[$i]];
            } else {
                return;
            }
        }
        unset($value[$key_array[count($key_array) - 1]]);
        return;
    }

    public static function arrayKeySet(&$array, $key_array, $val)
    {
        if (empty($key_array)) {
            return;
        }
        $value = &$array;
        for ($i = 0; $i < count($key_array) - 1; $i++) {
            if (array_key_exists($key_array[$i], $value)) {
                $value = &$value[$key_array[$i]];
            } else {
                $value[$key_array[$i]] = [];
                $value = &$value[$key_array[$i]];
            }
        }
        $value[$key_array[count($key_array) - 1]] = $val;
    }

    /**
     * 保存タスク実績（ファイル）
     * @param int $task_result_id タスク実績ID
     * @param array $task_result_file_po_array タスク実績（ファイル）
     */
    public static function taskFilePersistence(int $task_result_id, array $task_result_file_po_array)
    {
        // 数据库中插入新生成的文件
        foreach ($task_result_file_po_array as $taskResultFile) {
            $taskResultFile->task_result_id = $task_result_id;
            $taskResultFile->created_user_id = \Auth::user()->id;
            $taskResultFile->updated_user_id = \Auth::user()->id;
            $taskResultFile->save();
        }
    }

    /**
     * 通过路由前缀取得逻辑实现类.
     * @param int $task_id 路由前缀
     * @return ReplyMailInterface 逻辑实现类
     * @throws \ReflectionException
     */
    public static function getMailImplementsByPrefix(int $task_id): ReplyMailInterface
    {
//        $stepId = substr(strrchr($prefix, "/"), 1);
//        list($type, $biz, $businessId, $stepId) = explode('/', $task_id);
        $ext_info_mixed = self::getExtInfoById($task_id);
        $businessId = sprintf("B%05d", $ext_info_mixed->business_id);
        $stepId = sprintf("S%05d", $ext_info_mixed->step_id);
        // 通过作業Id取得逻辑实现类
        $impl_class = 'App\Services\CommonMail\Replay\Biz\\' . $businessId . '\\' . $stepId . 'Impl';
        $class = new ReflectionClass($impl_class);
        $instance = $class->newInstance();
        return $instance;
    }

    /**
     * 排他处理
     * @param int $task_id タスクID
     * @param int $user_Id ユーザーID
     * @throws \Exception 排他失败
     */
    public static function exclusiveTask(int $task_id, int $user_Id)
    {
        $query = Task::where('id', $task_id)
            ->where('user_id', $user_Id)
            ->where('status', '<>', \Config::get('const.TASK_STATUS.DONE'))
            ->where('is_active', \Config::get('const.FLG.ACTIVE'))
//            ->where('updated_at', '<', $this->task_started_at)
            ->lockForUpdate();
        $task = $query->first();
        if ($task === null) {
            throw new \Exception("The task does not exist or is completed, task_id:$task_id user_id:$user_Id");
        }
    }

    /**
     * 排他制御.
     * @param int $task_id タスクID
     * @param int $user_id ユーザーID
     * @return mixed
     * @throws \Exception 排他失败
     */
    public static function exclusiveTaskByUserId(int $task_id, int $user_id, bool $lock = true)
    {
        $ext_info_mixed = self::getExtInfoByTeskId($task_id);
        $is_business_admin = self::isBusinessAdmin($ext_info_mixed->business_id, $user_id);
        $query = Task::where('id', $task_id);
        if (!$is_business_admin) {
            $query = $query->where('user_id', $user_id);
        }
        if ($lock) {
            $query = $query->lockForUpdate();
        }
        $task = $query->first();
        if ($task === null) {
            throw new \Exception("The task does not exist or does not belong to you, task_id:$task_id user_id:$user_id");
        }
        return $task;
    }

    /**
     * TaskIdでタスク関連情報を取得する.
     * @param int $task_id タスクId
     * @return mixed
     */
    public static function getExtInfoByTeskId(int $task_id)
    {
        $result = \DB::table('businesses')
            ->selectRaw(
                'businesses.id business_id,' .
                'businesses.company_id,' .
                'request_works.step_id,' .
                'tasks.request_work_id'
            )->join('requests', 'businesses.id', '=', 'requests.business_id')
            ->join('request_works', 'requests.id', '=', 'request_works.request_id')
            ->join('tasks', 'request_works.id', '=', 'tasks.request_work_id')
            ->where('tasks.id', $task_id)
            ->where('businesses.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('requests.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('request_works.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->where('request_works.is_active', \Config::get('const.FLG.ACTIVE'))
            ->where('tasks.is_active', \Config::get('const.FLG.ACTIVE'))->first();
        return $result;
    }

    /**
     * 現在のユーザーが業務の管理者であるかどうかを確認します.
     * @param int $business_id 業務Id
     * @param int $user_id ユーザーId
     * @return bool true or false
     */
    public static function isBusinessAdmin(int $business_id, int $user_id): bool
    {
        $result = \DB::table('businesses_admin')
            ->where('business_id', $business_id)
            ->where('user_id', $user_id)
            ->count();
        return $result > 0;
    }

    public static function defaultWorkTime(array &$work_time, int $task_id)
    {
        $started_at = self::arrayIfNull($work_time, ['started_at']);
        $finished_at = self::arrayIfNull($work_time, ['finished_at']);
        $total = self::arrayIfNull($work_time, ['total']);
        //开始时间的默认值处理
        if ($started_at === null && $finished_at === null && $total < 0.00001) {
            // 最新の作業履歴
            $query = TaskResult::where('task_id', $task_id)
                ->where(function ($or_query) {
                    $or_query->whereNotNull('started_at')
                        ->orWhereNotNull('finished_at')
                        ->orWhereNotNull('work_time');
                })->orderBy('id', 'desc');
            $task_result_info = $query->first();
            if ($task_result_info === null) {
                //没有设置过作业开始时间,使用系统当前时间
                $work_time['started_at'] = date('Y-m-d h:i:00', time());
            } else {
                //有设置过作业时间,取最近的一条记录的开始时间
                $task_result_info = TaskResult::where('task_id', $task_id)
                    ->orderBy('id', 'desc')
                    ->first();
                $work_time['started_at'] = $task_result_info->started_at;
            }
        }
    }

    /**
     * generate a successful json context
     * @param null|array|mixed $data data
     * @param null|array|mixed $message message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = null, $message = '')
    {
        return response()->json(self::successResult($message, $data));
    }

    /**
     * generate a failed json context
     * @param string $errorMsg 错误消息
     * @param null|array|mixed $data data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error(string $errorMsg, $data = null)
    {
        return response()->json(self::errorResult($errorMsg, $data));
    }

    /**
     * generate a failed result array
     * @param string $message message
     * @param null|array|mixed $data data
     * @return array result array
     */
    public static function errorResult($message, $data = null)
    {
        return ['result' => 'error', 'err_message' => $message, 'data' => $data];
    }

    /**
     * generate a successfly result array
     * @param string $message message
     * @param null|array|mixed $data data
     * @return array result array
     */
    public static function successResult($message = '', $data = null)
    {
        return ['result' => 'success', 'err_message' => $message, 'data' => $data];
    }

    /**
     * 保存 作業履歴
     * @param string $last_displayed_page 最後に表示していたページ
     * @param array $key page key in json content
     * @param array $data page data as json fromat
     * @param array $task_result_file_array resultFileArray
     * @param string $taskResultType task result type
     * @param array|null $work_time 作業時間
     * @throws \Exception
     */
    public function partStore(string $last_displayed_page, array $key, $data, array $task_result_file_array, string $taskResultType, array $work_time = null)
    {
        \DB::beginTransaction();

        try {
            // ========================================
            // 排他チェック
            // ========================================
            $this->exclusiveTask($this->task_id, \Auth::user()->id);

            // ========================================
            // 保存処理
            // ========================================
            if ($work_time === null) {
                $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            }
            self::defaultWorkTime($work_time, $this->task_id);
            // 最新の作業履歴
            $task_result_info = TaskResult::with('taskResultFiles')
                ->where('task_id', $this->task_id)
                ->orderBy('id', 'desc')
                ->first();
            if ($task_result_info === null) {
                $task_result_content = [];
            } else {
                $task_result_content = json_decode($task_result_info->content, true);  // 作業内容
            }
            self::arrayKeySet($task_result_content, $key, $data);
            $task_result_content['results']['type'] = (int)$taskResultType;
            $task_result_content[self::CONTENT_NODE_KEY_LAST_DISPLAYED_PAGE] = $last_displayed_page;
            //构造邮件附件列表
            $request_mail_attachments = [];
            foreach ($task_result_file_array as $task_result_file) {
                array_push($request_mail_attachments, $task_result_file);
            }
            //================================复制其它页面的文件================================

            $max_seq_no = count($task_result_file_array);
            foreach ($this->MAIL_OTHER_PAGE_ATTACH_KEY as $item) {
                $other_page_attach_key_str = implode(',', $item);
                $current_page_attach_key_str = implode(',', $key);
                if (strpos($other_page_attach_key_str, $current_page_attach_key_str) === 0) {
                    // 跳过当前页面的文件
                    continue;
                }
                $other_page_file_seq_no_array = $this->arrayIfNull($task_result_content, $item, []);
                if (!empty($other_page_file_seq_no_array)) {
                    $file_array = $this->getTaskResultFile($task_result_info->id, $other_page_file_seq_no_array);
                    $new_seq_no_array = [];
                    foreach ($file_array as $value) {
                        $max_seq_no++;
                        $task_result_file = new TaskResultFile;
                        $task_result_file->seq_no = $max_seq_no;
                        $task_result_file->name = $value[self::ATTACH_FILE_NAME];
                        $task_result_file->file_path = $value[self::ATTACH_FILE_PATH];
                        array_push($task_result_file_array, $task_result_file);
                        array_push($new_seq_no_array, $max_seq_no);
                    }
                    if (count($item) === 1) {
                        $task_result_content[$item[0]] = $new_seq_no_array;
                    } else {
                        $task_result_content[$item[0]][$item[1]] = $new_seq_no_array;
                    }
                }
            }
            // 回调复制其它页面文件的定制方法(在复杂业务场景时,上面的复制方法无法满足需求时,在业务层面重写该方法)
            try {
                $max_seq_no = $this->getMailImplementsByPrefix($this->task_id)->otherPageAttachFile($task_result_content, $task_result_file_array, $this->task_id, $task_result_info->id, $max_seq_no);
            } catch (\Throwable $e) {
            }

            $result_type = (int)$task_result_content['results']['type'];
            $task_result_content['results']['type'] = $result_type;
            if ($result_type === \Config::get('const.TASK_RESULT_TYPE.DONE')) {
                // 完了
                $request_work = RequestWork::findOrFail($this->request_work_id);
                // 送信メール登録
                $mail_to_array = self::arrayIfNull($data, $this->MAIL_CONTENT_TO, []);
                $mail_to_str = null;
                foreach ($mail_to_array as $mail_to) {
                    if ($mail_to_str !== null) {
                        $mail_to_str = $mail_to_str . ',' . $mail_to;
                    } else {
                        $mail_to_str = $mail_to;
                    }
                }
                $mail_cc_array = self::arrayIfNull($data, $this->MAIL_CONTENT_CC, []);
                $mail_cc_str = null;
                foreach ($mail_cc_array as $mail_cc) {
                    if ($mail_cc_str !== null) {
                        $mail_cc_str = $mail_cc_str . ',' . $mail_cc;
                    } else {
                        $mail_cc_str = $mail_cc;
                    }
                }

                $send_mail = new SendMail;
                $send_mail->request_work_id = $this->request_work_id;
                $send_mail->from = $this->getMailImplementsByPrefix($this->task_id)->getCommonMailSendFrom($this->task_id);
                $send_mail->to = $mail_to_str;
                $send_mail->cc = $mail_cc_str;
                $send_mail->subject = self::arrayIfNull($data, $this->MAIL_CONTENT_SUBJECT);
                $request_mail_array = $request_work->requestMails;
                if (empty($request_mail_array) || empty($request_mail_array[0]->content_type)) {
                    $send_mail->content_type = \Config::get('const.CONTENT_TYPE.TEXT');
                } else {
                    $send_mail->content_type = $request_mail_array[0]->content_type;
                }
                $content = self::arrayIfNull($data, $this->MAIL_CONTENT_BODY, '');

                $content = str_replace('<p><br></p>', '<br>', $content);
                $content = str_replace('<p>', '', $content);
                $content = str_replace('</p>', '<br>', $content);

                //20200815 ID 1001272 begin
                // chrome版本 84.0.4147.105（正式版本） （64 位）操作系统xubuntu20.04 ltd 下，邮件模板编辑组件的段落标签由<p> 变成了 <div>
                $content = str_replace('<div><br></div>', '<br>', $content);
                $content = str_replace('<div>', '', $content);
                $content = str_replace('</div>', '<br>', $content);
                //20200815 ID 1001272 end

                if (\Config::get('const.CONTENT_TYPE.TEXT') == $send_mail->content_type) {
                    // html2text
                    $content = strip_tags($content, '<br>');
                    $content = preg_replace('/<br\\s*?\/??>/i', "\r\n", $content);
                    $content = htmlspecialchars_decode($content);
                }



                $send_mail->body = $content;

                $send_mail->created_user_id = \Auth::user()->id;
                $send_mail->updated_user_id = \Auth::user()->id;
                $send_mail->save();

                // 添付ファイル
                foreach ($request_mail_attachments as $attachment) {
                    $send_mail_attachment = new SendMailAttachment;
                    $send_mail_attachment->send_mail_id = $send_mail->id;
                    $send_mail_attachment->name = $attachment->name;
                    $send_mail_attachment->file_path = $attachment->file_path;
                    $send_mail_attachment->created_user_id = \Auth::user()->id;
                    $send_mail_attachment->updated_user_id = \Auth::user()->id;
                    $send_mail_attachment->save();
                }

                // 納品後にメール送付したいのでメールIDを指定パスに追加
                $task_result_content['results']['mail_id'] = array($send_mail['id']);

                $this->taskSave($task_result_content, $task_result_file_array, $work_time);
            } elseif ($result_type === \Config::get('const.TASK_RESULT_TYPE.CONTACT')) {
                // 問い合わせ（不明あり）
                $this->taskContact($task_result_content, $task_result_file_array, $work_time);
            } elseif ($result_type === \Config::get('const.TASK_RESULT_TYPE.HOLD')) {
                // 一時保存（対応中）
                $this->taskTemporarySave($task_result_content, $task_result_file_array, $work_time);
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * 保存タスク実績
     * @param array $content 実作業内容
     * @param array $task_result_file_po_array タスク実績（ファイル）
     * @param array|null $work_time 作業時間
     */
    public function taskSave(array $content, array $task_result_file_po_array, array $work_time = null)
    {
        // タスクのステータスを完了に更新
        $task = Task::findOrFail($this->task_id);
        $task->status = config('const.TASK_STATUS.DONE');
        $task->updated_user_id = \Auth::user()->id;
        $task->save();

        // タスク実績
        $task_result = new TaskResult;
        $task_result->task_id = $this->task_id;
        $task_result->step_id = $this->step_id;
        // 作業時間は手入力値
        // TODO: 手入力する枠が画面にない場合
        $task_result->started_at = is_null($work_time) ? null : $work_time['started_at'];
        $task_result->finished_at = is_null($work_time) ? null : $work_time['finished_at'];
        $task_result->work_time = is_null($work_time) ? null : $work_time['total'];
        $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $task_result->created_user_id = \Auth::user()->id;
        $task_result->updated_user_id = \Auth::user()->id;
        $task_result->save();

        $this->taskFilePersistence($task_result->id, $task_result_file_po_array);

        // 処理キュー登録（承認）
        $queue = new Queue;
        $queue->process_type = config('const.QUEUE_TYPE.APPROVE');
        $queue->argument = json_encode(['request_work_id' => (int)$this->request_work_id]);
        $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
        $queue->created_user_id = \Auth::user()->id;
        $queue->updated_user_id = \Auth::user()->id;
        $queue->save();

        // ログ登録
        $request_log_attributes = [
            'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_NORMALLY'),
            'request_id' => $this->request_id,
            'request_work_id' => $this->request_work_id,
            'task_id' => $this->task_id,
            'created_user_id' => \Auth::user()->id,
            'updated_user_id' => \Auth::user()->id,
        ];
        $this->storeRequestLog($request_log_attributes);
    }

    /**
     * 保存タスク実績
     * @param array $content 実作業内容
     * @param array $task_result_file_po_array タスク実績（ファイル）
     * @param array|null $work_time 作業時間
     */
    public function taskContact(array $content, array $task_result_file_po_array, array $work_time = null)
    {
        $this->getMailImplementsByPrefix($this->task_id)->doUnknown($this->task_id, \Auth::user()->id, $content);

        // タスクのステータスを完了に更新
        $task = Task::findOrFail($this->task_id);
        $task->status = config('const.TASK_STATUS.DONE');
        //ADPORTER_PF-252 各作業画面) 不明点あり時の処理追加 不備・不明（1:あり）
        $task->is_defective = config('const.FLG.ACTIVE');
        $task->updated_user_id = \Auth::user()->id;
        $task->save();

        // タスク実績
        $task_result = new TaskResult;
        $task_result->task_id = $this->task_id;
        $task_result->step_id = $this->step_id;
        // 作業時間は手入力値
        // TODO: 手入力する枠が画面にない場合
        $task_result->started_at = is_null($work_time) ? null : $work_time['started_at'];
        $task_result->finished_at = is_null($work_time) ? null : $work_time['finished_at'];
        $task_result->work_time = is_null($work_time) ? null : $work_time['total'];
        $content['results']['comment'] = self::arrayIfNull($content, array_merge($this->MAIL_CONTENT_MAIN, $this->MAIL_CONTENT_UNKNOWN), null);
        $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $task_result->created_user_id = \Auth::user()->id;
        $task_result->updated_user_id = \Auth::user()->id;
        $task_result->save();

        $this->taskFilePersistence($task_result->id, $task_result_file_po_array);

        // ログ登録
        $request_log_attributes = [
            'type' => \Config::get('const.REQUEST_LOG_TYPE.TASK_COMPLETED_WITH_UNCLEAR_POINT'),
            'request_id' => $this->request_id,
            'request_work_id' => $this->request_work_id,
            'task_id' => $this->task_id,
            'created_user_id' => \Auth::user()->id,
            'updated_user_id' => \Auth::user()->id,
        ];
        $this->storeRequestLog($request_log_attributes);
    }

    /**
     * 一時保存（対応中）
     * @param array $content 実作業内容
     * @param array $task_result_file_po_array タスク実績（ファイル）
     * @param array|null $work_time 作業時間
     */
    public function taskTemporarySave(array $content, array $task_result_file_po_array, array $work_time = null)
    {
        // タスクのステータスを対応中に更新
        $task = Task::findOrFail($this->task_id);
        $task->status = config('const.TASK_STATUS.ON');
        $task->updated_user_id = \Auth::user()->id;
        $task->save();

        // タスク実績
        $task_result = new TaskResult;
        $task_result->task_id = $this->task_id;
        $task_result->step_id = $this->step_id;
        // 作業時間は手入力値
        // TODO: 手入力する枠が画面にない場合
        $task_result->started_at = is_null($work_time) ? null : $work_time['started_at'];
        $task_result->finished_at = is_null($work_time) ? null : $work_time['finished_at'];
        $task_result->work_time = is_null($work_time) ? null : $work_time['total'];
        $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $task_result->created_user_id = \Auth::user()->id;
        $task_result->updated_user_id = \Auth::user()->id;
        $task_result->save();

        $this->taskFilePersistence($task_result->id, $task_result_file_po_array);
    }

    /**
     * 增量处理用户提交的附件
     * @param int $task_id 作业ID
     * @param string $businessId 业务ID，用于生成保存文件时的目录
     * @param array $fileArray 包含用户提交的文件内容的数组
     * @return array 增量更新处理后的所有附件文件
     * @throws \Exception 排他或者访问权限异常
     */
    public static function attachFileUpdate(int $task_id, string $businessId, array $fileArray): array
    {
        \DB::beginTransaction();
        try {
            // 排他处理
            self::exclusiveTask($task_id, \Auth::user()->id);

            // ファイル添付
            $finalFileArray = [];
            $max_seq_no = 0;
            // 最新の作業履歴
            $taskResult = TaskResult::with('taskResultFiles')
                ->where('task_id', $task_id)
                ->orderBy('id', 'desc')
                ->first();

            //ファイル添付 存在する
            if (!empty($fileArray)) {
                // 遍历所有file
                foreach ($fileArray as $file) {
                    //seqNo存在
                    $fileSeqNo = self::arrayIfNull($file, [self::ATTACH_FILE_SEQ_NO], null, true);
                    if ($fileSeqNo !== null) {
                        //      检查seqNo正确性
                        $resultFile = TaskResultFile::where('task_result_id', $taskResult->id)
                            ->where('seq_no', $fileSeqNo)
                            ->first();
                        if ($resultFile === null) {
                            // 不正确，终止处理
                            throw new \Exception("Invalid file_seq_no in json");
                        }
                        //正确，增加到既存文件数组
                        $max_seq_no++;
                        $task_result_file_po = new TaskResultFile;
                        $task_result_file_po->seq_no = $max_seq_no;
                        $task_result_file_po->name = $resultFile->name;
                        $task_result_file_po->file_path = $resultFile->file_path;
                        array_push($finalFileArray, $task_result_file_po);
                    } else {
                        //seqNo不存在
                        self::arrayIfNull($file, [self::ATTACH_FILE_NAME], null, true);
                        //上传文件
                        $upload_data = array(
                            'business_id' => $businessId,
                            'file' => array(
                                self::ATTACH_FILE_NAME => self::arrayIfNullFail($file, [self::ATTACH_FILE_NAME], "Invalid file_name in json", true)
                            , self::ATTACH_FILE_CONTENT => self::arrayIfNullFail($file, [self::ATTACH_FILE_CONTENT], "Invalid file_data in json", true)
                            )
                        );
                        $file_path = self::uploadFile($upload_data, 'base64');

                        //生成，增加到新文件数组
                        $max_seq_no++;
                        $task_result_file_po = new TaskResultFile;
                        $task_result_file_po->seq_no = $max_seq_no;
                        $task_result_file_po->name = $file[self::ATTACH_FILE_NAME];
                        $task_result_file_po->file_path = $file_path;
                        array_push($finalFileArray, $task_result_file_po);
                    }
                }
            }

            \DB::commit();
            return $finalFileArray;
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * 根据作业履历获取文件信息
     * @param Request $req 请求对象
     * @return array 文件信息数组
     * @throws \Exception
     */
    public function getAttachFiles(Request $req): array
    {
        $all_file_array = [];
        // 1根据作业履历获取文件
        $base_info = parent::create($req)->original;
        $task_result_info = self::arrayIfNull($base_info, ['task_result_info']);
        // 1-1反序列化作业履历的content，得到对象
        $content_array = json_decode($task_result_info->content, true);
        // 1-2获取作业实绩的Id
        $task_result_id = self::arrayIfNullFail($base_info, ['task_result_info'], 'task_result_info not exist!', true)->id;
        // 1-3 处理文件
        foreach ($this->MAIL_RETURN_ATTACH_KEY as $return_key => $file_no_array_key) {
            if (self::arrayIfNull($content_array, $file_no_array_key)) {
                // 1-3-1取得文件Seq_no集合
                $ap_file_seq_no_array = self::arrayIfNull($content_array, $file_no_array_key);
                // 1-3-2通过seq_no获取文件的信息
                $ap_file_info_array = self::getTaskResultFile($task_result_id, $ap_file_seq_no_array);
                // 1-3-3构造文件结点Jsono数据
                $all_file_array[$return_key] = $ap_file_info_array;
            }
        }
        return $all_file_array;
    }

    /**
     * リクエストメールの添付ファイル情報の取得.
     * @param int $task_id タスクID
     * @param int $user_id ユーザーID
     * @return array リクエストメールの添付ファイル情報
     * @throws \Exception 添付ファイルの取得に失敗しました
     */
    public static function getMailAttachments(int $task_id, int $user_id): array
    {
        $task = self::exclusiveTaskByUserId($task_id, $user_id, false);
        $request_work = RequestWork::findOrFail($task->request_work_id);
        $mail_id = $request_work->requestMails[0]->id;
        // 添付ファイルの取得
        $select_column = ['id', 'name', 'file_path', 'size', 'width', 'height'];
        $mail_attachments = RequestMail::find($mail_id)->requestMailAttachments()->select($select_column)->get();
        $attachment_files = []; //依頼メールの添付
        $attachment_extra_files = []; //依頼メールの添付（追加）
        $attachment_unzipped_files = []; //依頼メールの添付（解凍済み）
        $attachment_not_unzipped_files = []; //依頼メールの添付（解凍されていません）
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        foreach ($mail_attachments as $attachment) {
            $attachment_info = new stdClass();
            $attachment_info->attachment_id = $attachment->id; //ID
            $attachment_info->id = $attachment->id; //ID
            $attachment_info->name = $attachment->name; //ファイル名
            $attachment_info->file_path = $attachment->file_path; //ファイルパス
            $attachment_info->width = $attachment->width; //幅(px)
            $attachment_info->height = $attachment->height; //高さ(px)
            $attachment_info->file_size = $disk->size($attachment->file_path); //ファイルサイズ TODO 課題ADTAKT_PF-4
            $extension = pathinfo($attachment->file_path, PATHINFO_EXTENSION);
            if ($extension === 'zip') {
                array_push($attachment_not_unzipped_files, $attachment_info);
            }
            array_push($attachment_files, $attachment_info);
        }
        // 依頼メールの添付（追加）から取得
        $size = count($attachment_not_unzipped_files);
        for ($i = 0; $i < $size; $i++) {
            //依頼メールの添付（解凍されていません）キューの最初の要素をポップします
            $zip_file = array_shift($attachment_not_unzipped_files);
            //依頼メールの添付（追加）から取得
            $mail_attachment_extras = \DB::table('request_mail_attachment_extra')
                ->select('*')
                ->where('mail_attachment_id', $zip_file->id)
                ->get();
            if (empty($mail_attachment_extras->all())) {
                //依頼メールの添付（追加）が存在しません,依頼メールの添付（解凍されていません）の最後までプッシュする
                array_push($attachment_not_unzipped_files, $zip_file);
            } else {
                //依頼メールの添付（追加）が存在します,依頼メールの添付（解凍済み）キューに追加
                array_push($attachment_unzipped_files, $zip_file);
                //依頼メールの添付（追加）
                foreach ($mail_attachment_extras as $attachment_extra) {
                    $attachment_info = new stdClass();
                    $attachment_info->attachment_id = $zip_file->id; //ID
                    $attachment_info->id = $attachment_extra->id; //ID
                    $attachment_info->name = $attachment_extra->name; //ファイル名
                    $attachment_info->file_path = $attachment_extra->file_path; //ファイルパス
                    $attachment_info->width = $attachment_extra->width; //幅(px)
                    $attachment_info->height = $attachment_extra->height; //高さ(px)
                    $attachment_info->file_size = $disk->size($attachment_extra->file_path); //ファイルサイズ TODO 課題ADTAKT_PF-4
                    array_push($attachment_extra_files, $attachment_info);
                }
            }
        }
        return [
            'attachment_files' => $attachment_files, //依頼メールの添付
            'attachment_extra_files' => $attachment_extra_files, //依頼メールの添付（追加）
            'attachment_unzipped_files' => $attachment_unzipped_files, //依頼メールの添付（解凍済み）
            'attachment_not_unzipped_files' => $attachment_not_unzipped_files //依頼メールの添付（解凍されていません）
        ];
    }

    /**
     * 获取文件路径和文件名称
     * @param int $task_result_id タスク実績Id
     * @param int $seq_no タスク実績（ファイル）SeqNo
     * @return array タスク実績（ファイル）
     * @throws \Exception
     */
    public static function getFileInfoByTaskResultIdAndSeqNO(int $task_result_id, int $seq_no)
    {

        $task_reuslt_file = TaskResultFile::where('task_result_id', $task_result_id)->where('seq_no', $seq_no)->first();
        if ($task_reuslt_file) {
            return [
                'file_name' => $task_reuslt_file->name,
                'file_path' => $task_reuslt_file->file_path,
                'seq_no' => $task_reuslt_file->seq_no,
                'size' => $task_reuslt_file->size,
                'width' => $task_reuslt_file->width,
                'height' => $task_reuslt_file->height
            ];
        } else {
            throw new \Exception('file not exists, task_result_id:' . $task_result_id . ' seq_no:' . $seq_no);
        }
        return [];
    }

    /**
     * 最新の作業履歴
     * @param int $task_id taskId
     * @return TaskResult
     */
    public static function getTaskResult(int $task_id): TaskResult
    {
        // 最新の作業履歴
        return $task_result_po = TaskResult::with('taskResultFiles')
            ->where('task_id', $task_id)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * タスク実績（ファイル）を削除する
     * @param int $task_result_id タスク実績ID
     * @param int $seq_no SeqNo
     */
    public static function removeTaskResultFile(int $task_result_id, int $seq_no)
    {
        \DB::delete('delete from task_result_files where task_result_id = ? and seq_no = ?', [$task_result_id, $seq_no]);
    }
}
