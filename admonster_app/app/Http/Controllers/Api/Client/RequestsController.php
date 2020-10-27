<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use App\Models\RequestMail;
use App\Models\RequestLog;

class RequestsController extends Controller
{
    // 補足情報と関連メールをまとめたページャーを取得
    public function getAppendices(Request $req)
    {
        $request_id = $req->request_id;
        $search_params = $req->search_params;
        $types = array_get($search_params, 'types');

        $request = RequestModel::where('id', $request_id)
            ->with([
                'relatedMails' => function ($query) {
                    $query->where('is_open_to_client', \Config::get('const.FLG.ACTIVE'));
                },
                'relatedMails.requestMailAttachments',
                'requestAdditionalInfos' => function ($query) {
                    $query->where('is_open_to_client', \Config::get('const.FLG.ACTIVE'));
                    $query->where('is_deleted', \Config::get('const.FLG.INACTIVE'));
                    $query->orderBy('created_at', 'desc');
                },
                'requestAdditionalInfos.requestAdditionalInfoAttachments' => function ($query) {
                    $query->where('is_deleted', 0);
                },
                'requestLogs' => function ($query) {
                    $query->whereIn('type', [\Config::get('const.REQUEST_LOG_TYPE.ALL_COMPLETED'), \Config::get('const.REQUEST_LOG_TYPE.IMPORT_COMPLETED')]);
                }
            ])->first();

        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        $appendices = collect();
        // 関連メール
        if (!$types || in_array(\Config::get('const.APPENDICES_TYPE.RELATED_MAIL'), $types)) {
            $related_mails = $request->relatedMails;
            foreach ($related_mails as &$related_mail) {
                $related_mail->original_body = $related_mail->body;
                // 表示用に変換
                if ($related_mail->content_type === RequestMail::CONTENT_TYPE_HTML) {
                    $related_mail->body = strip_tags($related_mail->body, '<br>');
                } else {
                    $related_mail->body = nl2br(e($related_mail->body));
                }

                // ファイルサイズを取得
                foreach ($related_mail->requestMailAttachments as $mail_attachment) {
                    $file_size = $disk->size($mail_attachment->file_path);
                    $mail_attachment->file_size = $file_size;
                }
                $related_mail->mail_attachments = $related_mail->requestMailAttachments;
            }
            $appendices = $appendices->merge($related_mails);
        }
        // 補足情報
        if (!$types || in_array(\Config::get('const.APPENDICES_TYPE.ADDITIONAL'), $types)) {
            $additional_infos = $request->requestAdditionalInfos;
            foreach ($additional_infos as $additional_info) {
                // ファイルサイズを取得
                foreach ($additional_info->requestAdditionalInfoAttachments as $mail_attachment) {
                    $file_size = $disk->size($mail_attachment->file_path);
                    $mail_attachment->file_size = $file_size;
                }
                $additional_info->mail_attachments = $additional_info->requestAdditionalInfoAttachments;
            }
            $appendices = $appendices->merge($additional_infos);
        }
        // 依頼ログ
        if (!$types || in_array(\Config::get('const.APPENDICES_TYPE.REQUEST_LOG'), $types)) {
            // 依頼ログ取得（最初の取込と全体の完了のみ）
            $request_logs = $request->requestLogs;
            $selected_request_logs = [];
            $request_completed_log = '';
            foreach ($request_logs as $key => $request_log) {
                if ($request_log->type == \Config::get('const.REQUEST_LOG_TYPE.ALL_COMPLETED')) {
                    // 全体の完了
                    $selected_request_logs[] = $request_log;
                }
                if ($request_log->type == \Config::get('const.REQUEST_LOG_TYPE.IMPORT_COMPLETED')) {
                    // 最初の取込
                    if (!$request_completed_log) {
                        $request_completed_log = $request_log;
                    } else if ($request_completed_log->created_at < $request_log->created_at) {
                        $request_completed_log = $request_log;
                    }
                }
            }
            if ($request_completed_log) {
                $selected_request_logs[] = $request_completed_log;
            }
            $appendices = $appendices->merge($selected_request_logs);
        }
        $appendices = $appendices->sortByDesc('updated_at')->values();
        $appendices = json_decode(json_encode($appendices));

        // ページ番号が指定されていなければ１ページ目
        $page_num = isset($search_params['page']) ? $search_params['page'] : 1;
        $per_page = isset($search_params['rows_per_page']) ? $search_params['rows_per_page'] : 5;
        $disp_rec = array_slice($appendices, ($page_num - 1) * $per_page, $per_page);
        // ページャーオブジェクトを生成
        $pager = new \Illuminate\Pagination\LengthAwarePaginator(
            $disp_rec, // ページ番号で指定された表示するレコード配列
            count($appendices), // 検索結果の全レコード総数
            $per_page, // 1ページ当りの表示数
            $page_num, // 表示するページ
            ['path' => $req->url()] // ページャーのリンク先のURL
        );

        return response()->json([
            'appendices' => $pager,
        ]);
    }
}
