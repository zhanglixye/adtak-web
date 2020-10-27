<?php

namespace App\Services\Traits;

use App\Models\RequestMail;

trait RequestMailTrait
{
    /**
     * メールテンプレートの情報取得
     */
    public function tryCreateMailTemplateData($mail_id): object
    {
        try {
            // メール情報の取得
            $select_column = ['id', 'from', 'to', 'cc', 'subject', 'content_type', 'body', 'recieved_at'];
            $request_mail = RequestMail::select($select_column)->find($mail_id);

            if (is_null($request_mail)) {
                $mail_id_value = json_encode($mail_id);
                throw new \Exception("No mail with the specified ID ${mail_id_value}");
            }

            // 表示用に変換
            if ($request_mail->content_type === RequestMail::CONTENT_TYPE_HTML) {
                $request_mail->body = strip_tags($request_mail->body, '<br>');
            } else {
                $request_mail->body = nl2br(e($request_mail->body));
            }

            $request_mail_template_info = $request_mail;

            // 添付ファイルの取得
            $select_column = ['id', 'name', 'file_path'];
            $mail_attachments = RequestMail::find($mail_id)->requestMailAttachments()->select($select_column)->get();

            // ファイルサイズを取得
            $disk = \Storage::disk(\Config::get('filesystems.cloud'));
            foreach ($mail_attachments as $mail_attachment) {
                $file_size = $disk->size($mail_attachment->file_path);
                $mail_attachment->file_size = $file_size;
            }
            $request_mail_template_info->mail_attachments = $mail_attachments;

            return $request_mail_template_info;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
