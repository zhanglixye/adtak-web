<?php


namespace App\Services\CommonMail\Replay\Biz\B00012;

use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Models\CommonMailBodyTemplate;
use App\Services\CommonMail\Replay\GenericImpl;
use App\Services\CommonMail\Template\TemplateEngine;

class S00018Impl extends GenericImpl
{

    /**
     * 获取默认的收件人.
     * @param int $task_id 作業ID
     * @return mixed 默认的收件人
     */
    public function getDefaultMailTo(int $task_id)
    {
        return ['ap-ag@dac.co.jp'];
    }

    /**
     * 获取默认的标题.
     * @param int $task_id 作業ID
     * @param float $time_zone 客户端的时区
     * @return mixed 默认的标题
     */
    public function getDefaultSubject(int $task_id, float $time_zone = null)
    {
        $task_ext_info_po = parent::getExtInfoById($task_id, \Auth::user()->id);
        return '【チェック依頼】' . $this->getMediaInfoByItemId($task_ext_info_po->request_id, 'サイト名');
    }

    /**
     * 获取默认的本文.
     * @param int $task_id 作業ID
     * @return mixed 默认的本文
     */
    public function getDefaultBody(int $task_id)
    {

        // 获取模板
        $template_config_array = \Config::get("biz.b00012.MAIL_CONFIG.s00018.MAIL_REPLAY_BODY_TEMPLATE");
        $template_step_id = $template_config_array['step_id'];
        $template_condition_cd = $template_config_array['condition_cd'];
        $task_ext_info_po = parent::getExtInfoById($task_id, \Auth::user()->id);
        $template = CommonMailBodyTemplate::selectBySelective(
            $task_ext_info_po->company_id,
            $task_ext_info_po->business_id,
            $template_step_id,
            $template_condition_cd
        );
        // 获取担当者
        $task_user = \DB::table('tasks')
            ->select('users.name')
            ->join('users', 'tasks.user_id', 'users.id')
            ->where('tasks.id', $task_id)
            ->where('users.is_deleted', \Config::get('const.DELETE_FLG.ACTIVE'))
            ->first();

        //绑定模板
        $signTemplates = parent::getSignTemplates($task_id, \Auth::user()->id);
        return TemplateEngine::make($template[0]->content, [
            'signature' => $signTemplates === null ? '' : $signTemplates->content,
            'step_user' => $task_user->name,
            'site_name' => $this->getMediaInfoByItemId($task_ext_info_po->request_id, 'サイト名'),
            'Q' => $this->getMediaInfoByItemId($task_ext_info_po->request_id, 'Q'),
            'media_info_url' => $this->getMediaInfoByItemId($task_ext_info_po->request_id, '媒体資料URL'),
            'media_info_file_name' => $this->getMediaInfoByItemId($task_ext_info_po->request_id, '媒体資料ファイル名'),
            'check_list_url' => $this->getMediaInfoByItemId($task_ext_info_po->request_id, 'チェックリストURL')
        ]);
    }


    /**
     * 获取ファイル添付.
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @return mixed ファイル添付
     */
    public function getDefaultAttachments(int $task_id, int $user_id)
    {
        return [];
    }

    /**
     * 获取CheckList的项目列表,为了前端处理方便在标准数据结构上增加了pos和disabled属性
     * @param int $task_id 作業ID
     * @return mixed 默认的CheckList的项目列表
     */
    public function getChecklistItems(int $task_id, int $user_id)
    {
        //get standard checkList
        $standard_check_list_array = parent::getChecklistItems($task_id, $user_id);

        // insert attribute
        $group_id = 0;
        foreach ($standard_check_list_array as &$group) {
            //add attribute 'value' to group
            $group['value'] = null;
            //add attribute 'pos' to group
            if ($group_id != 2 && $group_id != 3) {
                $group['pos'] = 0;
            } else {
                $group['pos'] = 1;
            }

            // add attribute 'group_component_type' to group
            if ($group_id == 1) {
                $group['group_component_type'] = 1; //radio group
            } else {
                $group['group_component_type'] = 0;// mixed
            }

            // add attribute 'disabled' to item
            foreach ($group['items'] as &$items) {
                $items['disabled'] = false;
            }

            $group_id++;
        }
        return $standard_check_list_array;
    }

    /**
     * mail to for 不明あり
     * @param int $business_id BusinessID
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $content 実作業内容
     * @return string mail to
     */
    public function getUnknownMailTo(int $business_id, int $task_id, int $user_id, array &$content)
    {
        return 'ap-ag@dac.co.jp';
    }

    /**
     * 入力の検証
     * @param int $task_id 作業ID
     * @param int $user_id ユーザーID
     * @param array $content_array コンテンツ配列
     * @return array ['result' => 'success|error', 'err_message' => '', 'data' => null];
     * @throws \Exception
     */
    public function inputValidation(int $task_id, int $user_id, array $content_array): array
    {

        $ext_info_mixed = self::getExtInfoById($task_id, $user_id);
        $business_id = sprintf("b%05d", $ext_info_mixed->business_id);
        $step_id = sprintf("s%05d", $ext_info_mixed->step_id);
        $mail_config_root = "biz.${business_id}.MAIL_CONFIG.${step_id}";
        $MAIL_CONTENT_CHECK_LIST = \Config::get($mail_config_root . '.MAIL_CONTENT_CHECK_LIST');
        //有效性检查
        $check_list_group_array = $this->arrayIfNull($content_array, $MAIL_CONTENT_CHECK_LIST, []);
        if (empty($check_list_group_array) || sizeof($check_list_group_array) < 5) {
            return MailController::errorResult('チェックリストの入力を完了してから進めてください。');
        }
        //１．リリースメニュー数を記入してください
        $group0 = $this->arrayIfNull($check_list_group_array[0], ['items'], [], true);
        if (empty($group0)) {
            return MailController::errorResult('チェックリストの入力を完了してから進めてください。');
        }
        $release_count = $this->arrayIfNull($check_list_group_array, [0, 'items', 0, 'value']);
        if ($release_count === null) {
            return MailController::errorResult('チェックリストの入力を完了してから進めてください。');
        } else if (!is_numeric($release_count) || ((int)$release_count) != $release_count || ((int)$release_count) < 0) {
            return MailController::errorResult('チェックリストのリリースメニュー数に整数しか入力できないため、ご確認ください');
        }

        //２．媒体資料をAGに登録（紐づけ）しましたか？　 ※社外秘資料は紐づけ不可！
        $group1 = $this->arrayIfNull($check_list_group_array[1], ['items'], [], true);
        if (empty($group1) || sizeof($group1) < 2) {
            return MailController::errorResult('チェックリストの入力を完了してから進めてください。');
        }
        // 完了
        $completed = $this->arrayIfNull($group1[0], ['value'], null, true);
        // 不要
        $not_needed = $this->arrayIfNull($group1[1], ['value'], null, true);
        if ($completed === null && $not_needed === null) {
            return MailController::errorResult('チェックリストの入力を完了してから進めてください。');
        }

        // 完了の場合
        if ($completed == true) {
            //2‐1. 紐づけたデバイスにチェックを入れてください
            // 少なくとも1つのアイテムを選択する必要があります
            $group2 = $this->arrayIfNull($check_list_group_array[2], ['items'], []);
            $nothing = true;
            foreach ($group2 as $item) {
                if ($this->arrayIfNull($item, ['value'], null, true) !== null) {
                    $nothing = false;
                    break;
                }
            }
            if ($nothing) {
                return MailController::errorResult('チェックリストの入力を完了してから進めてください。');
            }

            //2‐2.前Q資料の確認・対応をしましたか？
            $group3 = $this->arrayIfNull($check_list_group_array[3], ['items'], []);
            foreach ($group3 as $item) {
                if ($this->arrayIfNull($item, ['value'], null, true) === null) {
                    return MailController::errorResult('チェックリストの入力を完了してから進めてください。');
                }
            }
        }

        // ３．メールにチェックリストのboxURLの記述がありますか？
        $group4 = $this->arrayIfNull($check_list_group_array[4], ['items'], []);
        foreach ($group4 as $item) {
            if ($this->arrayIfNull($item, ['value'], null, true) === null) {
                return MailController::errorResult('チェックリストの入力を完了してから進めてください。');
            }
        }

        return MailController::successResult();
    }

    /**
     * 「媒体資料」の値を取得
     * @param int $request_id 依頼ID
     * @param string $item_id 項目名
     * @return mixed 媒体資料」の値
     */
    private function getMediaInfoByItemId(int $request_id, string $item_id)
    {
        $query = \DB::table('requests')
            ->select('order_detail_values.value')
            ->join('order_details_requests', 'requests.id', '=', 'order_details_requests.request_id')
            ->join('order_details', 'order_details_requests.order_detail_id', '=', 'order_details.id')
            ->join('order_detail_values', 'order_details.id', '=', 'order_detail_values.order_detail_id')
            ->join('order_file_import_column_configs_order_detail_values', 'order_detail_values.id', '=', 'order_file_import_column_configs_order_detail_values.order_detail_value_id')
            ->join('order_file_import_column_configs', 'order_file_import_column_configs_order_detail_values.order_file_import_column_config_id', '=', 'order_file_import_column_configs.id')
            ->where('requests.id', $request_id)
            ->where('order_file_import_column_configs.item', $item_id);
        $result = $query->first();
        if ($result == null) {
            return '';
        }
        return $result->value;
    }
}
