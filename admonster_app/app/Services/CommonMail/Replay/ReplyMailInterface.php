<?php


namespace App\Services\CommonMail\Replay;

/**
 * Interface ReplyMailInterface
 * @package App\Services\CommonMail
 */
interface ReplyMailInterface
{

    /**
     * 获取默认的mailSetting.
     * @param int $task_id 作業ID
     * @return mixed 默认的mailSetting
     */
    public function getMailSetting(int $task_id);

    /**
     * 获取默认的收件人.
     * @param int $task_id 作業ID
     * @return mixed 默认的收件人
     */
    public function getDefaultMailTo(int $task_id);

    /**
     * 获取默认的抄送人.
     * @param int $task_id 作業ID
     * @return mixed 默认的抄送人
     */
    public function getDefaultMailCc(int $task_id);

    /**
     * 获取默认的标题.
     * @param int $task_id 作業ID
     * @param float $time_zone 客户端的时区
     * @return mixed 默认的标题
     */
    public function getDefaultSubject(int $task_id, float $time_zone = null);

    /**
     * 获取默认的本文.
     * @param int $task_id 作業ID
     * @return mixed 默认的本文
     */
    public function getDefaultBody(int $task_id);

    /**
     * 获取默认的不明.
     * @param int $task_id 作業ID
     * @return mixed 默认的不明
     */
    public function getDefaultUnknown(int $task_id);

    /**
     * 获取默认的作業時間.
     * @param int $task_id 作業ID
     * @return mixed 默认的作業時間
     */
    public function getDefaultUseTime(int $task_id);

    /**
     * 获取默认的CheckList选项.
     * @param int $task_id 作業ID
     * @return mixed 默认的CheckList选项
     */
    public function getDefaultChecklistValues(int $task_id);

    /**
     * 获取CheckList的项目列表
     * @param int $task_id 作業ID
     * @return mixed 默认的CheckList的项目列表
     */
    public function getChecklistItems(int $task_id, int $user_id);

    /**
     * 获取本体テンプレート
     * @param int $task_id 作業ID
     * @return mixed 本体テンプレート
     */
    public function getDefaultBodyTemplates(int $task_id);

    /**
     * 获取本体テンプレート
     * @param int $task_id 作業ID
     * @return mixed 本体テンプレート
     */
    public function getBodyTemplates(int $task_id);

    /**
     * 获取default署名テンプレート
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @return mixed default署名テンプレート
     */
    public function getDefaultSignTemplates(int $task_id, int $user_id);

    /**
     * 获取署名テンプレート
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @return mixed 署名テンプレート
     */
    public function getSignTemplates(int $task_id, int $user_id);

    /**
     * 获取defaultファイル添付
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @return mixed ファイル添付
     */
    public function getDefaultAttachments(int $task_id, int $user_id);

    /**
     * 获取MailTo頻度
     * @param int $task_id 作業ID
     * @param String $keyword キーワード
     * @return mixed MailTo頻度
     */
    public function searchMailToFrequencyList(int $task_id, String $keyword = null);

    /**
     * 获取MailCc頻度
     * @param int $task_id 作業ID
     * @param String $keyword キーワード
     * @return mixed MailTo頻度
     */
    public function searchMailCcFrequencyList(int $task_id, String $keyword = null);

    /**
     * 保存署名テンプレート
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $data 数据['title' => '','content' => '']
     */
    public function saveSignTemplates(int $task_id, int $user_id, array $data);

    /**
     * 保存本体テンプレート
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $data 数据[['id' => '','condition_cd' => '', 'title' => '', 'content' => '']]
     */
    public function saveBodyTemplates(int $task_id, int $user_id, array $data);

    /**
     * 不明あり
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $content 実作業内容
     * @return mixed 実作業内容
     */
    public function doUnknown(int $task_id, int $user_id, array &$content);

    /**
     * mail to for 不明あり
     * @param int $business_id BusinessID
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $content 実作業内容
     * @return string mail to
     */
    public function getUnknownMailTo(int $business_id, int $task_id, int $user_id, array &$content);

    /**
     * 复制邮件共通页面以外的其它页面文件的定制方法(在复杂业务场景时,邮件共通的复制其它页面附件的方法无法满足需求时,在业务层面重写该方法)
     * @param array $task_result_content task_result表的content对应的数组表示,直接修改此数组,邮件共通会将结果序列化为json保存到数据库中
     * @param array $task_result_file_array task_result_file的数组,将当前处理页面要保存的task_result_file放入此数组,邮件共通会统一保存到数据库中(邮件共通处理中已经放入了邮件附件文件)
     * @param int $task_id 当前操作的task的task_id
     * @param int $task_result_id 当前操作的ask_result_id
     * @param int $max_seq_no 当前最大的附件文件序号(邮件共通处理中已经处理了部分文件)
     * @return int 本处理结束后,附件文件的序号的最大值
     */
    public function otherPageAttachFile(array &$task_result_content, array &$task_result_file_array, int $task_id, int $task_result_id, int $max_seq_no);

    /**
     * 添付ファイルに許可されているファイルタイプを取得する
     * @param int $task_id 作業ID
     * @return array 添付ファイルに許可されているファイルタイプ
     */
    public function getCommonMailAttachmentTypeByTaskId(int $task_id): array;

    /**
     * メール送信者を取得する
     * @param int $task_id 作業ID
     * @return string 添付ファイルに許可されているファイルタイプ
     */
    public function getCommonMailSendFrom(int $task_id): string;

    /**
     * 入力の検証
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $content_array コンテンツ配列
     * @return array ['result' => 'success|error', 'err_message' => '', 'data' => null];
     */
    public function inputValidation(int $task_id, int $user_id, array $content_array) : array;
}
