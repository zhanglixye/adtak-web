<?php

namespace App\Http\Controllers\Api\Biz\B00010;

use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Models\Task;
use App\Models\task_result_file;
use App\Models\TaskResult;
use App\Models\TaskResultFile;
use App\Services\CommonMail\CommonDownloader;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Mixed_;
use setasign\Fpdi\Fpdi;
use Storage;
use function GuzzleHttp\json_encode;

require_once(__DIR__.'../../../../../../../vendor/setasign/fpdi_pdf-parser2/src/autoload.php');

class S00016Controller extends MailController
{

    /**
     * Abbeyチェック画面.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     */
    public function create(Request $req)
    {
        try {
            $base_info = parent::create($req)->original;
            $task_result_info = MailController::arrayIfNull($base_info, ['task_result_info']);
            if ($task_result_info === null || $task_result_info->content === null) {
                $mail_attachments = MailController::getMailAttachments($this->task_id, \Auth::user()->id);
                // 1没有作业履历，创建新的作业履历
                // 関連ファイルの情報
                $content_array = [];

                // リクエストメールの添付ファイル情報の取得
                $attachment_files = array_merge($mail_attachments['attachment_files'], $mail_attachments['attachment_extra_files']);
                $item_array = [];
                $file_seq_no = 1;
                foreach ($attachment_files as $db_file) {
                    if (self::specificExtensionCheck($db_file->name)) {
                        $item = array();
                        //pdf素材
                        $item['stamp_before']['seq_no'] = $file_seq_no++; //シーケンス番号
                        $item['stamp_before']['file_name'] = $db_file->name; //ファイル名
                        $item['stamp_before']['file_path'] = $db_file->file_path; //ファイルパス
                        $item['stamp_before']['file_size'] = $db_file->file_size;
                        //s3からのファイルのBase64表現
                        $s3_file = CommonDownloader::base64FileFromS3($db_file->file_path);
                        $item['stamp_before']['file_data'] = null;

                        $item['stamp_after']['seq_no'] = null; //シーケンス番号
                        $item['stamp_after']['file_name'] = $db_file->name; //ファイル名
                        $item['stamp_after']['file_path'] = $db_file->file_path; //ファイルパス
                        $item['stamp_after']['file_size'] = $db_file->file_size;
                        $item['stamp_after']['file_data'] = $s3_file[0];
                        array_push($item_array, $item);
                    }
                }


                $content_array['item_array'] = $item_array;
                //共通
                $content_array['results'] = \Config::get('biz.b00010.DEFAULT_CONTENT_RESULTS');
                // 最後に表示していたページ
                $content_array['lastDisplayedPage'] = '0';
                //メール共通
                $content_array['G00000_27'] = null;

                $task_result_po = self::saveContent($base_info['task_info']->id, $base_info['request_info']->step->id, $content_array);
                $base_info['task_result_info'] = $task_result_po;
                $base_info['task_result_info']->content = json_encode($content_array);
            } else {
                // 1-1反序列化作业履历的content，得到对象
                $task_result_array = json_decode($task_result_info->content, true);
                $task_result_file_array = $task_result_info->taskResultFiles->toArray();
                $content_array = self::contentToView($req, $task_result_array, $task_result_file_array);
                $base_info['task_result_info']->content = json_encode($content_array);
            }
            return response()->json($base_info);
        } catch (\Exception $e) {
            report($e);
            return MailController::error('初期化失敗しました。');
        }
    }

    /**
     * S3サーバーからファイルをダウンロードする.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     */
    public function downloadFromS3(Request $req)
    {
        $tmp_file_name = null;
        try {
            //有効性チェック
            $task = self::exclusiveTaskByUserId($req->task_id, \Auth::user()->id, false);// タスクは現在のユーザーに属します
            $task_result_po = TaskResult::where('task_id', $task->id)
                ->orderBy('id', 'desc')
                ->firstOrFail(); // 最新の作業履歴
            $file_valid_flag = false;
            foreach ($task_result_po->taskResultFiles as $file) {
                if ($file->file_path === $req->file_path) {
                    $file_valid_flag = true;
                    break;
                }
            }
            if (!$file_valid_flag) {
                return $this->error("ファイルは存在しません。");
            }

            //s3からのファイルのBase64表現
            $file = CommonDownloader::base64FileFromS3($req->file_path);

            return response()->json([
                'result' => 'success',
                'src' => $file[0],
                'mime_type' => $file[1],
                'file_size' => $file[2],
            ]);
        } catch (\Exception $e) {
            report($e);
            return self::error('ファイルのダウンロードに失敗しました');
        }
    }

    /**
     * JSONファイルからビュー形式変換する.
     * @param array $db_content_array DB形式のJSONファイル
     * @param array $task_result_file_array 結果ファイル
     * @return array ビュー形式のJSONファイル
     * @throws Exception 変換に失敗しました
     */
    private function contentToView(Request $req, array $db_content_array, array $task_result_file_array): array
    {

        $mail_content_main_key = $this->MAIL_CONTENT_MAIN[0];
        $mail_attach_file_key = array_keys($this->MAIL_RETURN_ATTACH_KEY)[0];
        $view_content_array = [];

        $view_content_array['results'] = self::arrayIfNull($db_content_array, ['results'], \Config::get('biz.b00010.DEFAULT_CONTENT_RESULTS'));
        // 最後に表示していたページ
        $view_content_array['lastDisplayedPage'] = self::arrayIfNull($db_content_array, ['lastDisplayedPage'], '0');

        // PDF画面データ
        $G00000_1_array = self::arrayIfNull($db_content_array, ['G00000_1'], []);
        $item_array = array();
        for ($i = 0; $i < count($G00000_1_array); $i++) {
            $item = array();
            //PDF素材
            $C00800_2_seq_no = $G00000_1_array[$i]['C00800_2'];
            //ファイル情報検索
            foreach ($task_result_file_array as $task_result_file) {
                if ($task_result_file['seq_no'] == $C00800_2_seq_no) {
                    $item['stamp_before']['seq_no'] = $C00800_2_seq_no;
                    $item['stamp_before']['file_name'] = $task_result_file['name']; //ファイル名
                    $item['stamp_before']['file_path'] = $task_result_file['file_path']; //ファイルパス
                    $item['stamp_before']['file_size'] = $task_result_file['size'];
                    //s3からのファイルのBase64表現
                    $file = CommonDownloader::base64FileFromS3($task_result_file['file_path']);
                    $item['stamp_before']['file_data'] = $file[0];
                    break;
                }
            }
            //pdf押印結果
            $C00800_3_seq_no = self::arrayIfNull($G00000_1_array[$i], ['C00800_3']);
            $has_result_pdf = false;
            if ($C00800_3_seq_no != null) {
                //ファイル情報検索
                foreach ($task_result_file_array as $task_result_file) {
                    if ($task_result_file['seq_no'] == $C00800_3_seq_no) {
                        $item['stamp_after']['seq_no'] = $C00800_3_seq_no;
                        $item['stamp_after']['file_name'] = $task_result_file['name']; //ファイル名
                        $item['stamp_after']['file_path'] = $task_result_file['file_path']; //ファイルパス
                        $item['stamp_after']['file_size'] = $task_result_file['size'];
                        //s3からのファイルのBase64表現
                        $file = CommonDownloader::base64FileFromS3($task_result_file['file_path']);
                        $item['stamp_after']['file_data'] = $file[0];
                        $has_result_pdf = true;
                        break;
                    }
                }
            }
            if (!$has_result_pdf) {
                // pdf押印結果が存在しません
                $item['stamp_after']['seq_no'] = null;
                $item['stamp_after']['file_name'] = $item['stamp_before']['file_name']; //ファイル名
                $item['stamp_after']['file_path'] = $item['stamp_before']['file_path']; //ファイルパス
                $item['stamp_after']['file_size'] = $item['stamp_before']['file_size']; //ファイルサイズを取得
                $item['stamp_after']['file_data'] = $item['stamp_before']['file_data'];
            }
            $item['stamp_before']['file_data'] = null;
            array_push($item_array, $item);
        }

        //pdf素材
        $view_content_array['item_array'] = $item_array;
        //メール共通
        $view_content_array['G00000_27'] = self::arrayIfNull($db_content_array, \Config::get('biz.b00010.MAIL_CONFIG.s00016.MAIL_CONTENT_MAIN'), ['G00000_33' => [], 'C00200_34' => '']);
        // メール共通附件
        $attach_file_array = $this->getAttachFiles($req);
        $attach_file = MailController::arrayIfNull($attach_file_array, [$mail_attach_file_key], []);
        $view_content_array[$mail_content_main_key][$mail_attach_file_key] = $attach_file;

        return $view_content_array;
    }

    /**
     * JSONファイルからデータベース形式変換する.
     * @param array $view_content_array ビュー形式のJSONファイル
     * @return array ビュー形式のJSONファイル
     * @throws Exception 変換に失敗しました
     */
    private function convertToDb(array $view_content_array): array
    {

        $db_content_array = [];

        $db_content_array['results'] = self::arrayIfNull($view_content_array, ['results'], \Config::get('biz.b00010.DEFAULT_CONTENT_RESULTS'));
        // 最後に表示していたページ
        $db_content_array['lastDisplayedPage'] = self::arrayIfNull($view_content_array, ['lastDisplayedPage'], '0');
        $db_content_array['G00000_1'] = [];

        $task_result_file_array = []; //

        $item_array = self::arrayIfNull($view_content_array, ['item_array'], []);
        foreach ($item_array as $item) {
            $seqNo = self::arrayIfNull($item, ['stamp_before', 'seq_no'], -1, true);
            if ($seqNo < 0) {
                //pdf素材が存在しません
                continue;
            }

            $G00000_1 = array();

            $G00000_1['C00800_2'] = self::arrayIfNullFail($item, ['stamp_before', 'seq_no']); //画像素材
            //pdf素材 -> タスク実績（ファイル）
            $file_item = array();
            $file_item['seq_no'] = self::arrayIfNullFail($item, ['stamp_before', 'seq_no']);
            $file_item['file_name'] = self::arrayIfNullFail($item, ['stamp_before', 'file_name']);
            $file_item['file_path'] = self::arrayIfNullFail($item, ['stamp_before', 'file_path']);
            $file_item['file_size'] = self::arrayIfNullFail($item, ['stamp_before', 'file_size']);
            $file_item['file_data'] = self::arrayIfNullFail($item, ['stamp_before', 'file_data']);


            array_push($task_result_file_array, $file_item);

            //pdf押印結果
            $result_capture_seq_no = self::arrayIfNull($item, ['stamp_after', 'seq_no']);
            $G00000_1['C00800_3'] = $result_capture_seq_no; //結果キャプチャー
            //pdf押印結果 -> タスク実績（ファイル）
            if ($result_capture_seq_no != null) {
                $file_item = array();
                $file_item['seq_no'] = $result_capture_seq_no;
                $file_item['file_name'] = self::arrayIfNullFail($item, ['stamp_after', 'file_name']);
                $file_item['file_path'] = self::arrayIfNullFail($item, ['stamp_after', 'file_path']);
                $file_item['file_size'] = self::arrayIfNullFail($item, ['stamp_after', 'file_size']);
                $file_item['file_data'] = self::arrayIfNullFail($item, ['stamp_after', 'file_data']);
                array_push($task_result_file_array, $file_item);
            }
            array_push($db_content_array['G00000_1'], $G00000_1);
        }
        return [
            'task_result_array' => $db_content_array,
            'task_result_file_array' => $task_result_file_array
        ];
    }

    /**
     * 特定のファイルのチェック.
     * @param string $file_name ファイル名
     * @return bool true:有効な,false:無効
     */
    private function specificExtensionCheck(string $file_name): bool
    {
        // Exclusion hidden file name
        if (mb_substr($file_name, 0, 1) == '.') {
            return false;
        }

        $tmp_list = explode('.', $file_name);
        $extension = array_pop($tmp_list);

        switch ($extension) {
            case 'pdf':
                return true;
            default:
                return false;
        }
    }

    /**
     * 一時保存
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function tmpSave(Request $req)
    {
        try {
            $data = $req->task_result_content;
            $view_content_array = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error('JSONファイルの形式に不備はあります。');
            }
            $view_content_array['results'] = \Config::get('biz.b00010.DEFAULT_CONTENT_RESULTS');
            $view_content_array['results']['type'] = \Config::get('const.TASK_RESULT_TYPE.HOLD');
            // 作業時間
            $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            $work_time['started_at'] = $this->arrayIfNull($view_content_array, ['common_work_time', 'started_at']);
            $work_time['finished_at'] = $this->arrayIfNull($view_content_array, ['common_work_time', 'finished_at']);
            $work_time['total'] = $this->arrayIfNull($view_content_array, ['common_work_time', 'total']);
            unset($view_content_array['common_work_time']);

            self::saveContent($req->task_id, $req->step_id, $view_content_array, config('const.TASK_STATUS.ON'), $work_time);
            return MailController::success();
        } catch (\Exception $e) {
            report($e);
            return self::error('保存失敗しました。' . $e->getMessage());
        }
    }

    /**
     * 作業内容を初期化する.
     * @param int $task_id タスクID
     * @param int $step_id ステップID
     * @param array $view_content_array ビュー形式のJSONファイル
     * @return TaskResult タスク結果
     * @throws Exception 初期化に失敗しました
     */
    private function saveContent(int $task_id, int $step_id, array $view_content_array, int $task_status = null, array $work_time = null)
    {
        \DB::beginTransaction();
        try {
            // 排他制御
            $this->exclusiveTask($this->task_id, \Auth::user()->id);
            if ($task_status !== null) {
                // タスクのステータスに更新
                $task = Task::findOrFail($task_id);
                $task->status = $task_status;
                $task->updated_user_id = \Auth::user()->id;
                $task->save();
            }

            $db_task_result_info = self::convertToDb($view_content_array);
            // データベースに新しいジョブ履歴書を作成する
            if ($work_time === null) {
                $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            }
            $task_result_po = new TaskResult();
            $task_result_po->task_id = $task_id;
            $task_result_po->step_id = $step_id;
            $task_result_po->started_at = $work_time['started_at'];
            $task_result_po->finished_at = $work_time['finished_at'];
            $task_result_po->work_time = $work_time['total'];
            $task_result_po->created_user_id = \Auth::user()->id;
            $task_result_po->updated_user_id = \Auth::user()->id;
            $task_result_po->content = json_encode($db_task_result_info['task_result_array']);
            $task_result_po->save();

            // 新しく生成されたファイルをデータベースに挿入します
            foreach ($db_task_result_info['task_result_file_array'] as $task_result_file) {
                $task_result_file_po = new TaskResultFile();
                $task_result_file_po->seq_no = $task_result_file['seq_no'];
                $task_result_file_po->name = $task_result_file['file_name'];
                $task_result_file_po->file_path = $task_result_file['file_path'];
                $task_result_file_po->width = self::arrayIfNull($task_result_file, ['width']);
                $task_result_file_po->height = self::arrayIfNull($task_result_file, ['height']);
                $task_result_file_po->size = self::arrayIfNull($task_result_file, ['file_size']);
                $task_result_file_po->task_result_id = $task_result_po->id;
                $task_result_file_po->created_user_id = \Auth::user()->id;
                $task_result_file_po->updated_user_id = \Auth::user()->id;
                $task_result_file_po->save();
            }
            \DB::commit();
            return $task_result_po;
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * PDFに印鑑を追加
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse 封印されたPDFファイル
     */
    public function approvalPdf(Request $req)
    {
        $width = $req->width; //ページに表示されるPDFの幅(pixels)
        $height = $req->height; //ページに表示されるPDFの高さ（pixels）
        $zoom = $req->zoom; //スケーリング比(一時的に使用されていません)
        $left = $req->left; //ページに表示される印鑑のx座標（pixels）
        $top = $req->top; //ページに表示される印鑑のy軸座標（pixels）
        $sealWidth = $req->sealWidth; //ページに表示される印鑑の幅(pixels)
        $sealHeight = $req->sealHeight; //ページに表示される印鑑の高さ（pixels）
        $seq_no = $req->seq_no; //タスク実績（ファイル）SeqNo
        $current_page = $req->currentPage;


        \DB::beginTransaction();
        try {
            // 排他处理
            MailController::exclusiveTask($req->task_id, \Auth::user()->id);
            // 获取作业实绩的Id
            $base_info = parent::create($req)->original;
            $task_result_id = MailController::arrayIfNull($base_info, ['task_result_info'])->id;

            $file_info = self::getFileInfoByTaskResultIdAndSeqNO($task_result_id, $seq_no);
            $db_file_name = $file_info['file_name'];
            $db_file_path = $file_info['file_path'];
            list($file_name, $tmp_disk, $tmp_file_name, $tmp_file_path, $mime_type, $file_size) = CommonDownloader::getFileFromS3($db_file_path, $db_file_name);
            //重新生成印章
            // ローカルに一時保存
            $seal_tmp_disk_file_name = time() . '_s00016_' . \Auth::user()->id . '_seal_tmp.png';
            $seal_tmp_file_path = storage_path() . '/app/public/' . $seal_tmp_disk_file_name;
            $this->makeSeal($req->time_zone, $seal_tmp_file_path);

            $pdf = new Fpdi();
            $page_count = $pdf->setSourceFile($tmp_file_path);//原始PDF地址

            for ($pageNo = 1; $pageNo <= $page_count; $pageNo++) {
                // import a page
                $template_id = $pdf->importPage($pageNo);

                // get the size of the imported page
                $size = $pdf->getTemplateSize($template_id);

                // create a page (landscape or portrait depending on the imported page size)
                if ($size['width'] > $size['height']) {
                    $pdf->AddPage('L', array($size['width'], $size['height']));
                } else {
                    $pdf->AddPage('P', array($size['width'], $size['height']));
                }
                // use the imported page
                $pdf->useTemplate($template_id);

                if ($pageNo == $current_page) {
                    //単位換算(pixels->cm)
                    if ($left == 0) {
                        //除数が0にならないようにする
                        $left = 0.0000001;
                    }
                    if ($top == 0) {
                        //除数が0にならないようにする
                        $top = 0.0000001;
                    }
                    $x = $size['width'] / $width * $left;
                    $y = $size['height'] / $height * $top;
                    $w = $sealWidth / ($left / $x);
                    $h = $sealHeight / ($top / $y);
                    // Place the graphics
                    $pdf->image(
                        $seal_tmp_file_path,
                        $x,
                        $y,
                        $w,
                        $h
                    );//图片地址
                }
            }
            // ローカルに一時保存
            $file_name = self::approvalFileRename($file_name);//押印されたPDFfile 元File名＋”_承認済”
            $tmp_disk = Storage::disk('public');
            $tmp_disk_file_name = time() . $file_name;
            $tmp_file_path = storage_path() . '/app/public/' . $tmp_disk_file_name;
            // シール追加後のPDFファイルが一時ファイルに出力されます
            $pdf->Output('F', $tmp_file_path);
            // S3への一時ファイルのアップロード
            try {
                $tmp_file = $tmp_disk->get($tmp_disk_file_name);
                $upload_data = array(
                    'business_id' => 'B00010',
                    'file' => array(
                        'file_name' => $file_name
                    , 'file_data' => $tmp_file
                    )
                );
                $s3_file_detail = MailController::uploadFileDetail($upload_data, 'content', true);
                $task_result_file_po = new TaskResultFile();
                $task_result_file_result = \DB::table('task_result_files')
                    ->selectRaw(
                        'ifnull(max(seq_no),0)+1 seq_no'
                    )
                    ->where('task_result_files.task_result_id', $task_result_id)
                    ->first();
                $task_result_file_po->seq_no = $task_result_file_result->seq_no;
                $task_result_file_po->name = $s3_file_detail['file_name'];
                $task_result_file_po->file_path = $s3_file_detail['file_path'];
                $task_result_file_po->width = null;
                $task_result_file_po->height = null;
                $task_result_file_po->size = $s3_file_detail['file_size'];
                $task_result_file_po->task_result_id = $task_result_id;
                $task_result_file_po->created_user_id = \Auth::user()->id;
                $task_result_file_po->updated_user_id = \Auth::user()->id;
                $task_result_file_po->save();

                $result_array = [];
                $result_array['seq_no'] = (int)$task_result_file_po->seq_no;
                $result_array['file_name'] = $s3_file_detail['file_name'];
                $result_array['file_path'] = $s3_file_detail['file_path'];
                $result_array['file_size'] = $s3_file_detail['file_size'];
                $result_array['file_data'] = $s3_file_detail['src'];
            } finally {
                // 一時ファイルを削除
                $tmp_disk->delete($tmp_disk_file_name);
                $tmp_disk->delete($seal_tmp_disk_file_name);
            }
            \DB::commit();
            return MailController::success(json_encode($result_array));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return MailController::error('押印に失敗しました。');
        }
    }

    /**
     * PDFに印鑑をクリア
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function eraseStamp(Request $req)
    {
        $after_seq_no = $req->after_seq_no;
        $before_seq_no = $req->before_seq_no;
        if (empty($after_seq_no) || empty($before_seq_no)) {
            return MailController::error('印鑑クリアに失敗しました。');
        }
        \DB::beginTransaction();
        try {
            // 排他处理
            MailController::exclusiveTask($req->task_id, \Auth::user()->id);
            //最新の作業履歴
            $task_result_po = MailController::getTaskResult($req->task_id);
            //タスク実績（ファイル）を削除する
            MailController::removeTaskResultFile($task_result_po->id, $after_seq_no);
            //タスク実績（ファイル）を取得
            $result_file_po = MailController::getTaskResultFileById($task_result_po->id, $before_seq_no);
            //s3からのファイルのBase64表現
            $file = CommonDownloader::base64FileFromS3($result_file_po->file_path);
            $result_array['seq_no'] = null;
            $result_array['file_name'] = $result_file_po->name;
            $result_array['file_path'] = $result_file_po->file_path;
            $result_array['file_size'] = $result_file_po->size;
            $result_array['file_data'] = $file[0];
            \DB::commit();
            return MailController::success(json_encode($result_array));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return MailController::error('押印に失敗しました。');
        }
    }

    /**
     * 获取印章
     * @param Request $req
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function getSeal(Request $req)
    {
        try {
            $img_path = '/var/www/storage/biz/b00010/seal_front.png';
            $imgInfo = @getimagesize($img_path);//获取图片相关信息,抑制报错，不存在则返回false
            if ($imgInfo === false) {
                //此处提示报错信息
                return MailController::error('印鑑取得失敗しました。');
            }
            $mime = $imgInfo['mime'];//获取图片类型
            $createFun = str_replace('/', 'createfrom', $mime);//由文件或 URL 创建一个新图象，动态生成创建图像的方法。
            $image = $createFun($img_path);
            imagesavealpha($image, true);//设置保存PNG时保留透明通道信息
            header("content-type:image/png");
            imagepng($image);//生成图片到浏览器
            return;
        } catch (\Exception $e) {
            report($e);
            return MailController::error('印鑑取得失敗しました。');
        }
    }

    /**
     * @param string $img_path 图片实际地址
     * @param string $font_path 字体文件路径
     * @param array $text_array 填充的文本
     * @param string $save_path 生成填充后的文件（自定义）
     * @return bool
     */
    private function fillImage(string $img_path, string $font_path, array $text_array, string $save_path = null): bool
    {
        $imgInfo = @getimagesize($img_path);//获取图片相关信息,抑制报错，不存在则返回false
        if ($imgInfo === false) {
            //此处提示报错信息
            return false;
        }
        $mime = $imgInfo['mime'];//获取图片类型
        $createFun = str_replace('/', 'createfrom', $mime);//由文件或 URL 创建一个新图象，动态生成创建图像的方法。
        $image = $createFun($img_path);
        $back = imagecolorallocate($image, 218, 52, 53);//设置画笔颜色
        foreach ($text_array as $item) {
            $tt = imagettftext($image, $item[0], 0, $item[1], $item[2], $back, $font_path, $item[3]);//书写文字方式
        }
        imagesavealpha($image, true);//设置保存PNG时保留透明通道信息
        if ($save_path === null) {
            header("content-type:image/png");
            imagepng($image);//生成图片到浏览器
            return true;
        } else {
            $bool = imagepng($image, $save_path);
            return $bool;
        }
    }

    /**
     * 生成印章
     * @param float $time_zone 客户端的时区
     * @param string|null $save_path 保存路径,为空直接写出到浏览器
     */
    private function makeSeal($time_zone, string $save_path = null): void
    {
        $timestamp = time() + $time_zone * 3600;
        $date = date('Y.m.d', (int)$timestamp);
        $this->fillImage(
            '/var/www/storage/biz/b00010/seal.png',
            '/var/www/storage/biz/b00010/ipag.ttf',
            [[50, 120, 135, 'ADP承認'], [60, 60, 277, $date], [50, 100, 420, '社長 山﨑']],
            $save_path
        );
    }

    /**
     * 押印されたPDFfile 元File名＋”_承認済”
     * @param string $file_name ファイル名
     * @return string rename
     */
    private function approvalFileRename(string $file_name): string
    {
        // Exclusion hidden file name
        if (mb_substr($file_name, 0, 1) == '.') {
            return $file_name . '_承認済';
        }
        $file_name_array = explode('.', $file_name);
        return $file_name_array[0] . '_承認済.' . $file_name_array[1];
    }
}
