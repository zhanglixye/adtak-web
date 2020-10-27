<?php

namespace App\Http\Controllers\Api\Biz\B00007;

use App\Http\Controllers\Api\Biz\BaseController;
use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Models\Abbey;
use App\Models\Business;
use App\Models\Queue;
use App\Models\Request as RequestModel;
use App\Models\RequestMail;
use App\Models\RequestWork;
use App\Models\SendMail;
use App\Models\Task;
use App\Models\task_result_file;
use App\Models\TaskResult;
use App\Models\TaskResultFile;
use App\Services\CommonMail\CommonDownloader;
use App\Services\UploadFileManager\Uploader;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Mixed_;
use stdClass;
use Storage;
use ZipArchive;
use function GuzzleHttp\json_encode;

class S00013Controller extends BaseController
{

    /**
     * Abbeyチェック画面.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     */
    public function create(Request $req)
    {
        try {
            $user = \Auth::user();
            $base_info = parent::create($req)->original;
            $task_result_info = self::arrayIfNull($base_info, ['task_result_info']);
            $mail_attachments = self::getMailAttachments($this->task_id, \Auth::user()->id);
            if ($task_result_info === null || $task_result_info->content === null) {
                // 1没有作业履历，创建新的作业履历
                // 関連ファイルの情報
                $content_array = [];

                // リクエストメールの添付ファイル情報の取得
                $attachment_files = array_merge($mail_attachments['attachment_files'], $mail_attachments['attachment_extra_files']);
                $item_array = [];
                $file_seq_no = 0;
                foreach ($attachment_files as $file) {
                    if (self::specificExtensionCheck($file->name)) {
                        $item = array();
                        //素材
                        $item['material']['seq_no'] = $file_seq_no++; //シーケンス番号
                        $item['material']['file_name'] = $file->name; //ファイル名
                        $item['material']['file_path'] = $file->file_path; //ファイルパス
                        $item['material']['file_size'] = $file->file_size; //ファイルサイズ
                        $item['material']['display_size'] = ($file->height == null || $file->width == null) ? '' : $file->width . 'x' . $file->height; //ディスプレイサイズ
                        $item['material']['check_file_name'] = self::convert($file->name, number_format($file->file_size)); //チェックファイル名
                        //結果キャプチャー
                        $item['result_capture']['seq_no'] = null; //シーケンス番号
                        $item['result_capture']['file_name'] = ''; //ファイル名
                        $item['result_capture']['file_path'] = ''; //ファイルパス
                        $item['result_capture']['file_size'] = ''; //ファイルサイズ
                        $item['result_capture']['display_size'] = ''; //ディスプレイサイズ
                        //チェック
                        $item['check']['result'] = ''; //チェック結果
                        $item['check']['abbey_id'] = ''; //abbey id
                        $item['check']['menu_name'] = ''; //メニュー名
                        $item['check']['error_message'] = ''; //エラー内容
                        $item['check']['result_comment'] = ''; //結果コメント
                        array_push($item_array, $item);
                    }
                }
                //パスワード付きのZipファイル
                $zip_array = [];
                foreach ($mail_attachments['attachment_not_unzipped_files'] as $file) {
                    $zip_item = array();
                    $zip_item['seq_no'] = $file->id; //依頼メールの添付のid
                    $zip_item['file_name'] = $file->name; //ファイル名
                    $zip_item['file_path'] = $file->file_path; //ファイルパス
                    $zip_item['file_size'] = $file->file_size; //ファイルサイズ
                    $zip_item['unzip_flag'] = false; //解凍されていません
                    array_push($zip_array, $zip_item);
                }

                $content_array['item_array'] = $item_array;
                $content_array['zip_array'] = $zip_array;
                $content_array['client_name'] = null;
                $content_array['unknown_comment'] = null;
                $task_result_po = self::saveContent($base_info['task_info']->id, $base_info['request_info']->step->id, $content_array);
                $base_info['task_result_info'] = $task_result_po;
                $base_info['task_result_info']->content = json_encode($content_array);
            } else {
                // 1-1反序列化作业履历的content，得到对象
                $task_result_array = json_decode($task_result_info->content, true);
                $task_result_file_array = $task_result_info->taskResultFiles->toArray();
                $content_array = self::contentToView($task_result_array, $task_result_file_array);
                $base_info['task_result_info']->content = json_encode($content_array);
            }
            return response()->json($base_info);
        } catch (\Exception $e) {
            report($e);
            return self::error('初期化失敗しました。');
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
     * ファイルをs3にアップロードする.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     */
    public function uploadFileToS3(Request $req)
    {
        \DB::beginTransaction();
        try {
            $file_name = $req->file_name;
            $file_data = $req->file_data;
            if (empty($file_name) || empty($file_data)) {
                return $this->error('ファイルの形式に不備はあります。');
            }
            // 排他制御
            $this->exclusiveTask($this->task_id, \Auth::user()->id);

            // 最新の作業履歴
            $task_result_info = TaskResult::with('taskResultFiles')
                ->where('task_id', $this->task_id)
                ->orderBy('id', 'desc')
                ->first();

            //ファイル番号を計算する
            $task_result_file_array = $task_result_info->taskResultFiles->toArray();
            $seqNo = 0;
            foreach ($task_result_file_array as $task_result_file) {
                if ($seqNo < (int) $task_result_file['seq_no']) {
                    $seqNo = (int) $task_result_file['seq_no'];
                }
            }
            $seqNo++;
            list(, $fileData) = explode(';', $file_data);
            list(, $fileData) = explode(',', $fileData);
            $file_contents = base64_decode($fileData);
            $file_path = 'task_result_files/B00007/' . Carbon::now()->format('Ymd') . '/' . md5(microtime()) . '/' . $file_name;

            //ファイルをデータベースに保存する
            $task_result_file_po = Uploader::tryUploadAndSave($file_contents, $file_path, 'App\Models\TaskResultFile', ['seq_no' => $seqNo, 'task_result_id' => $task_result_info->id]);

            //ファイル名変換
            $check_file_name = self::convert($task_result_file_po->name, number_format($task_result_file_po->size)); //チェックファイル名

            \DB::commit();

            return self::success(
                [
                    'seq_no' => $seqNo,
                    'file_name' => $task_result_file_po->name,
                    'file_path' => $task_result_file_po->file_path,
                    'file_size' => $task_result_file_po->size,
                    'display_size' => $task_result_file_po->width . 'x' . $task_result_file_po->height,
                    'check_file_name' => $check_file_name
                ]
            );
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'result' => 'error',
            ]);
        }
    }

    /**
     * 素材全件ダウンロード.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     * @throws \Throwable 材全件ダウンロードに失敗しました
     */
    public function materialZipDownload(Request $req)
    {
        try {
            // タスク実績を取得
            $this->exclusiveTaskByUserId($this->task_id, \Auth::user()->id);

            $data = $req->task_result_content;
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error('JSONファイルの形式に不備はあります。');
            }

            $content_array = self::fileValidate($data);

            // $base_info = parent::create($req)->original;
            // $task_result_info = self::arrayIfNull($base_info, ['task_result_info'], []);
            // $task_result_array = json_decode($task_result_info->content, true);
            // $task_result_file_array = $task_result_info->taskResultFiles->toArray();
            // $content_array = self::contentToView($task_result_array, $task_result_file_array);

            // 圧縮ファイルを生成する
            $file_name = 'material.zip';
            $src_file_array = array();
            foreach ($content_array['item_array'] as $item) {
                array_push($src_file_array, $item['material']);
            }
            if (empty($src_file_array)) {
                return self::error("素材ファイルが存在しません");
            }
            $zipFile = self::createZipFile('B00007', $src_file_array, $file_name);
            $disk = $zipFile['disk'];
            $full_path = $disk->path($zipFile['file_path']);
            $data = base64_encode(file_get_contents($full_path));
            $src = 'data:' . $zipFile['mime_type'] . ';base64,' . $data;
            $zipFile['src'] = $src;
            unset($zipFile['disk']);

            // 一時ファイルを削除
            $disk->delete($zipFile['file_path']);
            return self::success($zipFile);
        } catch (\Exception $e) {
            report($e);
            return self::error('素材全件ダウンロード失敗しました。' . $e->getMessage());
        }
    }

    /**
     * 暗号化された圧縮ファイルを解凍.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     */
    public function unZipMaterialWithPasswd(Request $req)
    {
        $zip_file_full_path = "";
        $tmp_dir_path = "";
        try {
            $passwd = $req->passwd;
            $file_path = $req->file_path;

            // ファイルが存在するかどうかを確認します
            $mail_attachments = self::getMailAttachments($this->task_id, \Auth::user()->id);
            $file_exist_flag = false;
            foreach ($mail_attachments['attachment_not_unzipped_files'] as $zip_attachment) {
                if ($zip_attachment->file_path === $file_path) {
                    $file_exist_flag = true;
                    break;
                }
            }
            if (!$file_exist_flag) {
                return self::error("ファイルが存在しません");
            }

            $task_result_info = TaskResult::with('taskResultFiles')
                ->where('task_id', $this->task_id)
                ->orderBy('id', 'desc')
                ->first(); // 最新の作業履歴

            list($org_file_name, $tmp_disk, $tmp_file_name, $zip_file_full_path, $mime_type, $file_size) = CommonDownloader::getFileFromS3($file_path, null, true);

            $path_info_array = (object) pathinfo($zip_file_full_path, PATHINFO_DIRNAME | PATHINFO_BASENAME | PATHINFO_EXTENSION | PATHINFO_FILENAME);

            $zip_file_dir_name = $path_info_array->dirname;
            // $zip_file_file_name = $path_info_array->basename;
            // $zip_file_extension = $path_info_array->extension;
            // $zip_file_file_name_no_ext = $path_info_array->filename;

            //ファイルを一時ディレクトリに解凍します
            $tmp_dir_path = "${zip_file_dir_name}/unzip_" . md5(microtime());
            $cmd = "unzip -P ${passwd} ${zip_file_full_path} -d ${tmp_dir_path} > /dev/null";
            system($cmd, $return_val);
            if ($return_val !== 0) {
                //一時ファイルのディレクトリを削除する
                \File::delete($zip_file_full_path);
                \File::deleteDirectory($tmp_dir_path);
                return self::error('ファイル解凍が失敗しました、ローカルまで保存してから行ってください。');
            }

            $unziped_file_array = \File::allFiles($tmp_dir_path);
            foreach ($unziped_file_array as $unziped_file) {
                $tmp_list = explode('.', $unziped_file->getFilename());
                $extension = array_pop($tmp_list);
                if ($extension == 'zip') {
                    // ADTAKT_PF-79 一番外のファイルも解凍せず、すぐメッセージ「解凍できないファイルがありました」を出すことはよろしいです。
                    return self::error('解凍できないファイルがありました');
                }
            }

            //解凍したファイルをS3にアップロードします
            //ファイル情報をタスク実績（ファイル）に保存する
            \DB::beginTransaction();
            try {
                self::exclusiveTask($this->task_id, \Auth::user()->id); //排他
                $seqNo = TaskResultFile::where('task_result_id', $task_result_info->id)->max('seq_no');
                $upload_file_info_array = [];
                foreach ($unziped_file_array as $unziped_file) {
                    $seqNo++;
                    $file_name = $unziped_file->getFilename();
                    //素材の種類確認
                    if (!self::specificExtensionCheck($unziped_file->getFilename())) {
                        //許可されていないファイルは処理されません
                        continue;
                    }
                    $open_file = $unziped_file->openFile('r');
                    $file_data = $open_file->fread($open_file->getSize());
                    //解凍後のファイル名の文字化けの問題に対処する(convert to hash code using md5）
                    $tmp_list = explode('.', $file_name);
                    $extension = array_pop($tmp_list);
                    $file_path = 'task_result_files/B00007/' . Carbon::now()->format('Ymd') . '/' . md5(microtime()) . '/' . md5($file_name) . '.' . $extension;

                    //ファイルをデータベースに保存する
                    $task_result_file_po = Uploader::tryUploadAndSave($file_data, $file_path, 'App\Models\TaskResultFile', ['seq_no' => $seqNo, 'task_result_id' => $task_result_info->id]);
                    $upload_file_info = [
                        'seq_no' => $task_result_file_po->seq_no,
                        'file_name' => $task_result_file_po->name,
                        'file_path' => $task_result_file_po->file_path,
                        'display_size' => $task_result_file_po->width . 'x' . $task_result_file_po->height, //TODO 課題ADTAKT_PF-4
                        'file_size' => $task_result_file_po->size,
                        'check_file_name' => self::convert($task_result_file_po->name, $task_result_file_po->size)
                    ];
                    array_push($upload_file_info_array, $upload_file_info);
                }
                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollback();
                return self::error('ファイルの保存に失敗しました');
            }
            return self::success($upload_file_info_array);
        } catch (\Exception $e) {
            report($e);
            return self::error('ファイルの解凍に失敗しました');
        } finally {
            //一時ファイルのディレクトリを削除する
            if (!empty($zip_file_full_path)) {
                \File::delete($zip_file_full_path);
            }
            if (!empty($tmp_dir_path)) {
                \File::deleteDirectory($tmp_dir_path);
            }
        }
    }

    /**
     * データベース検索.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     */
    public function search(Request $req)
    {
        try {
            $user = \Auth::user();
            if (empty($req->search_word)) {
                $result = Abbey::getAllAbbeyList();
            } else {
                // 検索文字列が空文字の場合は条件指定をしない
                $search_word = self::createNgramString($req->search_word);

                // AbbeyDB
                $result = Abbey::searchBySearchWord($search_word);
            }
            return self::success($result);
        } catch (\Exception $e) {
            report($e);
            return self::error('検索失敗しました。');
        }
    }

    /**
     * 処理する.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     * @throws \Throwable 処理に失敗しました
     */
    public function commitWork(Request $req)
    {
        try {
            // $user = \Auth::user();
            // $base_info = parent::create($req)->original;
            $data = $req->task_result_content;
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error('JSONファイルの形式に不備はあります。');
            }

            $data = self::fileValidate($data);

            // 作業時間
            $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            $work_time['started_at'] = $this->arrayIfNull($data, ['common_work_time', 'started_at']);
            $work_time['finished_at'] = $this->arrayIfNull($data, ['common_work_time', 'finished_at']);
            $work_time['total'] = $this->arrayIfNull($data, ['common_work_time', 'total']);
            unset($data['common_work_time']);

            //validate
            $abbey_array = \DB::table('abbey')
                ->selectRaw(
                    'abbey_id,' .
                    'specification'
                )->get()
                ->toArray();

            self::arrayIfNullFail($data, ['client_name'], '[クライアント名]の入力は必須です。', true);
            $zip_array = self::arrayIfNull($data, ['zip_array'], null, true);
//            if ($zip_array != null) {
//                foreach ($zip_array as $zip) {
//                    if (!self::arrayIfNull($zip, ['unzip_flag'], false)) {
//                        return self::error('[暗号化される添付ファイル]の解凍する必要があります。');
//                    }
//                }
//            }
            $item_array = self::arrayIfNull($data, ['item_array'], []);
            $row_no = 0;
            foreach ($item_array as $item) {
                $row_no++;
                self::arrayIfNullFail($item, ['material', 'seq_no'], "[ $row_no 番目の素材]は必須です。", true);
                self::arrayIfNullFail($item, ['check', 'result'], "[ $row_no 番目]未判定の素材があります。", true);
                if (!in_array($item['check']['result'], [0, 1, 2], true)) {
                    return self::error("[ $row_no 番目の判定]値の範囲は[OK, NG,不明あり]です。");
                }
                //判定は不明
                if ($item['check']['result'] === 2) {
                    continue;
                }
                //判定はNG
                if ($item['check']['result'] === 0) {
                    self::arrayIfNullFail($item, ['check', 'error_message'], "[ $row_no 番目のエラー内容]は必須です。", true);
                }
                //判定はOK or NG
                self::arrayIfNullFail($item, ['check', 'result_comment'], "[ $row_no 番目のチェック結果コメント]は必須です。", true);
                self::arrayIfNullFail($item, ['result_capture', 'seq_no'], "[ $row_no 番目の結果キャプチャー]は必須です。", true);
                self::arrayIfNullFail($item, ['check', 'abbey_id'], "[ $row_no 番目のabbey id]は必須です。", true);
                self::arrayIfNullFail($item, ['check', 'menu_name'], "[ $row_no 番目のメニュー名]は必須です。", true);

                // abbey_idチェック
                $abbey_id_valid_flag = false;
                foreach ($abbey_array as $abbey) {
                    if ($abbey->abbey_id === $item['check']['abbey_id']) {
                        $abbey_id_valid_flag = true;
                        break;
                    }
                }
                if (!$abbey_id_valid_flag) {
                    return self::error("[ $row_no 番目のabbey id]は存在しません。");
                }
            }


            //JSONファイルからデータベース形式変換する
            $db_task_result_info = self::convertToDb($data);
            //作業内容を保存する
            self::taskSave($db_task_result_info['task_result_array'], $db_task_result_info['task_result_file_array'], $work_time);

            return self::success();
        } catch (\Exception $e) {
            report($e);
            return self::error($e->getMessage());
        }
    }

    /**
     * 作業内容を保存する.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     */
    public function saveWork(Request $req)
    {
        try {
            $data = $req->task_result_content;
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error('JSONファイルの形式に不備はあります。');
            }

            $data = self::fileValidate($data);

            // 作業時間
            $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            $work_time['started_at'] = $this->arrayIfNull($data, ['common_work_time', 'started_at']);
            $work_time['finished_at'] = $this->arrayIfNull($data, ['common_work_time', 'finished_at']);
            $work_time['total'] = $this->arrayIfNull($data, ['common_work_time', 'total']);
            unset($data['common_work_time']);

            $db_task_result_info = self::convertToDb($data);

            self::taskTemporarySave($db_task_result_info['task_result_array'], $db_task_result_info['task_result_file_array'], $work_time);

            return self::success();
        } catch (\Exception $e) {
            report($e);
            return self::error('保存失敗しました。' . $e->getMessage());
        }
    }

    /**
     * 不明あり.
     * @param Request $req リクエスト
     * @return \Illuminate\Http\JsonResponse レスポンス
     * @throws \Throwable 不明あり実行失敗
     */
    public function wrongWork(Request $req)
    {
        try {
            $data = $req->task_result_content;
            $data = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error('JSONファイルの形式に不備はあります。');
            }

            $data = self::fileValidate($data);

            self::arrayIfNullFail($data, ['unknown_comment'], '[担当者へのコメント]の入力は必須です。', true);

            // 作業時間
            $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            $work_time['started_at'] = $this->arrayIfNull($data, ['common_work_time', 'started_at']);
            $work_time['finished_at'] = $this->arrayIfNull($data, ['common_work_time', 'finished_at']);
            $work_time['total'] = $this->arrayIfNull($data, ['common_work_time', 'total']);
            unset($data['common_work_time']);

            $db_task_result_info = self::convertToDb($data);

            self::taskContact($db_task_result_info['task_result_array'], $db_task_result_info['task_result_file_array'], $work_time);

            return self::success();
        } catch (\Exception $e) {
            report($e);
            return self::error($e->getMessage());
        }
    }

    /**
     * 作業内容を保存する.
     * @param array $content 作業内容
     * @param array $task_result_file_array 結果ファイル
     * @param array|null $work_time 作業時間
     * @throws \Throwable 保存に失敗しました
     */
    private function taskSave(array $content, array $task_result_file_array, array $work_time = null)
    {
        $content['results']['type'] = \Config::get('const.TASK_RESULT_TYPE.DONE');
        \DB::beginTransaction();
        try {
            $this->exclusiveTask($this->task_id, \Auth::user()->id);

            // 対応表の生成
            $excel_file = self::checkResultExcel($content, $task_result_file_array);
            // メールの登録
            $send_mail = self::registerMail($content, $task_result_file_array, $excel_file);
            // タスク実績の中に入れる
            $content['results']['mail_id'] = [$send_mail->id];

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
            if ($work_time === null) {
                $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            }
            MailController::defaultWorkTime($work_time, $this->task_id);
            $task_result->started_at = $work_time['started_at'];
            $task_result->finished_at = $work_time['finished_at'];
            $task_result->work_time = $work_time['total'];
            $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
            $task_result->created_user_id = \Auth::user()->id;
            $task_result->updated_user_id = \Auth::user()->id;
            $task_result->save();

            // タスク実績（ファイル）
            foreach ($task_result_file_array as $task_result_file) {
                $task_result_file_po = new TaskResultFile();
                $task_result_file_po->seq_no = $task_result_file['seq_no'];
                $task_result_file_po->name = $task_result_file['name'];
                $task_result_file_po->file_path =  $task_result_file['file_path'];
                $task_result_file_po->width = self::arrayIfNull($task_result_file, ['width']);
                $task_result_file_po->height = self::arrayIfNull($task_result_file, ['height']);
                $task_result_file_po->size = self::arrayIfNull($task_result_file, ['size']);
                $task_result_file_po->task_result_id = $task_result->id;
                $task_result_file_po->created_user_id = \Auth::user()->id;
                $task_result_file_po->updated_user_id = \Auth::user()->id;
                $task_result_file_po->save();
            }


            // 処理キュー登録（承認）
            $queue = new Queue;
            $queue->process_type = config('const.QUEUE_TYPE.APPROVE');
            $queue->argument = json_encode(['request_work_id' => $this->request_work_id]);
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
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * 不明あり.
     * @param array $content 作業内容
     * @param array $task_result_file_array 結果ファイル
     * @param array|null $work_time 作業時間
     * @throws \Throwable 保存に失敗しました
     */
    private function taskContact(array $content, array $task_result_file_array, array $work_time = null)
    {
        $content['results']['type'] = \Config::get('const.TASK_RESULT_TYPE.CONTACT');
        \DB::beginTransaction();
        try {
            $this->exclusiveTask($this->task_id, \Auth::user()->id);
            // メールの登録
            $send_mail = self::registerMail($content, $task_result_file_array, null);
            // タスク実績の中に入れる
            $content['results']['mail_id'] = [$send_mail->id];

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
            if ($work_time === null) {
                $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            }
            MailController::defaultWorkTime($work_time, $this->task_id);
            $task_result->started_at = $work_time['started_at'];
            $task_result->finished_at = $work_time['finished_at'];
            $task_result->work_time = $work_time['total'];
            $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
            $task_result->created_user_id = \Auth::user()->id;
            $task_result->updated_user_id = \Auth::user()->id;
            $task_result->save();

            // タスク実績（ファイル）
            foreach ($task_result_file_array as $task_result_file) {
                $task_result_file_po = new TaskResultFile();
                $task_result_file_po->seq_no = $task_result_file['seq_no'];
                $task_result_file_po->name = $task_result_file['name'];
                $task_result_file_po->file_path =  $task_result_file['file_path'];
                $task_result_file_po->width = self::arrayIfNull($task_result_file, ['width']);
                $task_result_file_po->height = self::arrayIfNull($task_result_file, ['height']);
                $task_result_file_po->size = self::arrayIfNull($task_result_file, ['size']);
                $task_result_file_po->task_result_id = $task_result->id;
                $task_result_file_po->created_user_id = \Auth::user()->id;
                $task_result_file_po->updated_user_id = \Auth::user()->id;
                $task_result_file_po->save();
            }

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
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * 作業内容を保存する.
     * @param array $content 作業内容
     * @param array $task_result_file_array 結果ファイル
     * @param array|null $work_time 作業時間
     * @throws Exception 保存に失敗しました
     */
    private function taskTemporarySave(array $content, array $task_result_file_array, array $work_time = null)
    {
        $content['results']['type'] = \Config::get('const.TASK_RESULT_TYPE.HOLD');
        \DB::beginTransaction();
        try {
            $this->exclusiveTask($this->task_id, \Auth::user()->id);

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
            if ($work_time === null) {
                $work_time = ['started_at' => null, 'finished_at' => null, 'total' => null];
            }
            MailController::defaultWorkTime($work_time, $this->task_id);
            $task_result->started_at = $work_time['started_at'];
            $task_result->finished_at = $work_time['finished_at'];
            $task_result->work_time = $work_time['total'];
            $task_result->content = json_encode($content, JSON_UNESCAPED_UNICODE);
            $task_result->created_user_id = \Auth::user()->id;
            $task_result->updated_user_id = \Auth::user()->id;
            $task_result->save();

            // タスク実績（ファイル）
            foreach ($task_result_file_array as $task_result_file) {
                $task_result_file_po = new TaskResultFile();
                $task_result_file_po->seq_no = $task_result_file['seq_no'];
                $task_result_file_po->name = $task_result_file['name'];
                $task_result_file_po->file_path = $task_result_file['file_path'];
                $task_result_file_po->width = self::arrayIfNull($task_result_file, ['width']);
                $task_result_file_po->height = self::arrayIfNull($task_result_file, ['height']);
                $task_result_file_po->size = self::arrayIfNull($task_result_file, ['size']);
                $task_result_file_po->task_result_id = $task_result->id;
                $task_result_file_po->created_user_id = \Auth::user()->id;
                $task_result_file_po->updated_user_id = \Auth::user()->id;
                $task_result_file_po->save();
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();
            throw $e;
        }
    }

    /**
     * JSONファイルからビュー形式変換する.
     * @param array $db_content_array DB形式のJSONファイル
     * @param array $task_result_file_array 結果ファイル
     * @return array ビュー形式のJSONファイル
     * @throws Exception 変換に失敗しました
     */
    private function contentToView(array $db_content_array, array $task_result_file_array): array
    {
        $view_content_array = [];

        $view_content_array['results'] = self::arrayIfNull($db_content_array, ['results'], \Config::get('biz.b00007.DEFAULT_CONTENT_RESULTS'));
        // 最後に表示していたページ
        $view_content_array['lastDisplayedPage'] = self::arrayIfNull($db_content_array, ['lastDisplayedPage'], '0');

        // ファイルサイズを取得
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        // AbbeyCheck処理画面データ
        $G00000_1_array = self::arrayIfNull($db_content_array, ['G00000_1'], []);
        $item_array = array();
        for ($i = 0; $i < count($G00000_1_array); $i++) {
            $item = array();
            //画像素材
            $C00800_7_seq_no = $G00000_1_array[$i]['C00800_7'];
            //ファイル情報検索
            foreach ($task_result_file_array as $task_result_file) {
                if ($task_result_file['seq_no'] == $C00800_7_seq_no) {
                    $item['material']['seq_no'] = $C00800_7_seq_no;
                    $item['material']['file_name'] = $task_result_file['name']; //ファイル名
                    $item['material']['file_path'] = $task_result_file['file_path']; //ファイルパス
                    $file_size = $disk->size($task_result_file['file_path']); //ファイルサイズを取得
                    $item['material']['file_size'] = $file_size; //ファイルサイズ  TODO 課題ADTAKT_PF-4
                    $item['material']['display_size'] = (self::arrayIfNull($task_result_file, ['height']) == null || self::arrayIfNull($task_result_file, ['width']) == null) ? '' : $task_result_file['width'] . 'x' . $task_result_file['height']; //画像解像度
                    $item['material']['check_file_name'] = self::convert($task_result_file['name'], number_format($file_size)); //チェックファイル名
                    break;
                }
            }
            //結果キャプチャー
            $C00800_8_seq_no = self::arrayIfNull($G00000_1_array[$i], ['C00800_8']);
            $has_result_capture = false;
            if ($C00800_8_seq_no != null) {
                //ファイル情報検索
                foreach ($task_result_file_array as $task_result_file) {
                    if ($task_result_file['seq_no'] == $C00800_8_seq_no) {
                        $item['result_capture']['seq_no'] = $C00800_8_seq_no;
                        $item['result_capture']['file_name'] = $task_result_file['name']; //ファイル名
                        $item['result_capture']['file_path'] = $task_result_file['file_path']; //ファイルパス
                        $item['result_capture']['file_size'] = $disk->size($task_result_file['file_path']); //ファイルサイズを取得 TODO 課題ADTAKT_PF-4
                        $item['result_capture']['display_size'] = (self::arrayIfNull($task_result_file, ['height']) == null || self::arrayIfNull($task_result_file, ['width']) == null) ? '' : $task_result_file['width'] . 'x' . $task_result_file['height']; //画像解像度
                        $has_result_capture = true;
                        break;
                    }
                }
            }
            if (!$has_result_capture) {
                // 結果キャプチャーが存在しません
                $item['result_capture']['seq_no'] = null;
                $item['result_capture']['file_name'] = ''; //ファイル名
                $item['result_capture']['file_path'] = ''; //ファイルパス
                $item['result_capture']['file_size'] = ''; //ファイルサイズを取得
                $item['result_capture']['display_size'] = ''; //画像解像度
            }

            //判定
            $item['check']['result'] = self::arrayIfNull($G00000_1_array[$i], ['C00500_2'], null, true); //判定  OK:1, NG:0, 不明あり:2, 決まっていない:null
            $item['check']['abbey_id'] = self::arrayIfNull($G00000_1_array[$i], ['C00100_3']); //abbey id
            $item['check']['menu_name'] = self::arrayIfNull($G00000_1_array[$i], ['C00100_4']); //メニュー名
            $item['check']['error_message'] = self::arrayIfNull($G00000_1_array[$i], ['C00200_5']); //エラー内容
            $item['check']['result_comment'] = self::arrayIfNull($G00000_1_array[$i], ['C00200_6']); //チェック結果コメント

            array_push($item_array, $item);
        }

        //画像素材
        $view_content_array['item_array'] = $item_array;
        //クライアント名
        $view_content_array['client_name'] = self::arrayIfNull($db_content_array, ['C00100_9']);
        //暗号化される添付ファイル
        $attachments = self::getMailAttachments($this->task_id, \Auth::user()->id);
        $C00800_10_array = self::arrayIfNull($db_content_array, ['C00800_10'], []);
        $zip_array = array();
        foreach ($attachments['attachment_not_unzipped_files'] as $attachment) {
            $item = array();
            $item['seq_no'] = $attachment->id;
            $item['file_name'] = $attachment->name; //ファイル名
            $item['file_path'] = $attachment->file_path; //ファイルパス
            $item['file_size'] = $attachment->file_size; //ファイルサイズ
            $item['unzip_flag'] = !in_array($attachment->id, $C00800_10_array, true); //解凍されていません
            array_push($zip_array, $item);
        }
        $view_content_array['zip_array'] = $zip_array;
        //「不明あり」で処理します,担当者へのコメント
        $view_content_array['unknown_comment'] = $view_content_array['results']['comment'];

        return $view_content_array;
    }

    /**
     * JSONファイルからデータベース形式変換する.
     * @param array $view_content_array  ビュー形式のJSONファイル
     * @return array ビュー形式のJSONファイル
     * @throws Exception 変換に失敗しました
     */
    private function convertToDb(array $view_content_array) : array
    {

        $db_content_array = [];

        $db_content_array['results'] = self::arrayIfNull($view_content_array, ['results'], \Config::get('biz.b00007.DEFAULT_CONTENT_RESULTS'));
        //「不明あり」で処理します,担当者へのコメント
        $db_content_array['results']['comment'] = self::arrayIfNull($view_content_array, ['unknown_comment']);
        // 最後に表示していたページ
        $db_content_array['lastDisplayedPage'] = self::arrayIfNull($view_content_array, ['lastDisplayedPage'], '0');
        $db_content_array['G00000_1'] = [];

        $task_result_file_array = []; //

        $item_array = self::arrayIfNull($view_content_array, ['item_array'], []);
        foreach ($item_array as $item) {
            $seqNo = self::arrayIfNull($item, ['material', 'seq_no'], -1, true);
            if ($seqNo < 0) {
                //画像素材が存在しません
                continue;
            }

            $G00000_1 = array();
            //判定
            //判定 -> タスク実績
            $G00000_1['C00500_2'] = self::arrayIfNull($item, ['check', 'result']); //判定  OK:1, NG:0, 不明あり:2
            $G00000_1['C00100_3'] = self::arrayIfNull($item, ['check', 'abbey_id']); //abbey id
            $G00000_1['C00100_4'] = self::arrayIfNull($item, ['check', 'menu_name']); //メニュー名
            $G00000_1['C00200_5'] = self::arrayIfNull($item, ['check', 'error_message']); //エラー内容
            $G00000_1['C00200_6'] = self::arrayIfNull($item, ['check', 'result_comment']); //チェック結果コメント

            //画像素材
            //画像素材 -> タスク実績
            $G00000_1['C00800_7'] = $seqNo; //画像素材
            //画像素材 -> タスク実績（ファイル）
            $file_item = array();
            $file_item['seq_no'] = $seqNo;
            $file_item['name'] = self::arrayIfNullFail($item, ['material', 'file_name']);
            $file_item['file_path'] = self::arrayIfNullFail($item, ['material', 'file_path']);
            $display_size = self::arrayIfNull($item, ['material', 'display_size']);
            if ($display_size != null) {
                $display_size_array = explode('x', $display_size);
                $file_item['width'] = (int) $display_size_array[0];
                $file_item['height'] = (int) $display_size_array[1];
            }
            $file_size = self::arrayIfNull($item, ['material', 'file_size']);
            if ($file_size != null) {
                $file_item['size'] = (int) $file_size;
            }
            array_push($task_result_file_array, $file_item);

            //結果キャプチャー
            //結果キャプチャー -> タスク実績
            $result_capture_seq_no = self::arrayIfNull($item, ['result_capture', 'seq_no']);
            $G00000_1['C00800_8'] = $result_capture_seq_no; //結果キャプチャー
            //結果キャプチャー -> タスク実績（ファイル）
            if ($result_capture_seq_no != null) {
                $file_item = array();
                $file_item['seq_no'] = $result_capture_seq_no;
                $file_item['name'] = self::arrayIfNullFail($item, ['result_capture', 'file_name']);
                $file_item['file_path'] = self::arrayIfNullFail($item, ['result_capture', 'file_path']);
                $display_size = self::arrayIfNull($item, ['result_capture', 'display_size']);
                if ($display_size != null) {
                    $display_size_array = explode('x', $display_size);
                    $file_item['width'] = (int) $display_size_array[0];
                    $file_item['height'] = (int) $display_size_array[1];
                }
                $file_size = self::arrayIfNull($item, ['result_capture', 'file_size']);
                if ($file_size != null) {
                    $file_item['size'] = (int) $file_size;
                }
                array_push($task_result_file_array, $file_item);
            }
            array_push($db_content_array['G00000_1'], $G00000_1);
        }
        //クライアント名
        $db_content_array['C00100_9'] = self::arrayIfNull($view_content_array, ['client_name'], '');

        //暗号化される添付ファイル
        $C00800_10_array = [];
        $zip_array = self::arrayIfNull($view_content_array, ['zip_array'], []);
        foreach ($zip_array as $zip) {
            $seqNo = self::arrayIfNull($zip, ['seq_no'], -1, true);
            $unzip_flag = self::arrayIfNull($zip, ['unzip_flag'], false, true);
            if ($unzip_flag) {
                //（解凍済み）
                continue;
            }
            //暗号化される添付ファイル -> タスク実績
            array_push($C00800_10_array, $seqNo); //暗号化される添付ファイル
        }
        $db_content_array['C00800_10'] = $C00800_10_array;

        return [
            'task_result_array' => $db_content_array,
            'task_result_file_array' => $task_result_file_array
        ];
    }

    /**
     * ユーザーが送信したファイルの有効性を確認します.
     * @param array $view_content_array ビュー形式のJSONファイル
     * @return array ファイル情報を処理した後のJSONファイル
     * @throws Exception 不正なファイルが存在します
     */
    private function fileValidate(array $view_content_array) : array
    {
        // 最新の作業履歴
        $task_result_info = TaskResult::with('taskResultFiles')
            ->where('task_id', $this->task_id)
            ->orderBy('id', 'desc')
            ->first();

        //タスク実績（ファイル）
        $task_result_file_array = $task_result_info == null ? [] : $task_result_info->taskResultFiles->toArray();
        $file_map = array();
        foreach ($task_result_file_array as $task_result_file) {
            $file_map[$task_result_file['seq_no']] = $task_result_file;
        }

        // file validate
        $item_array = self::arrayIfNull($view_content_array, ['item_array'], []);

        for ($i = 0; $i < count($item_array); $i++) {
            $row = $i + 1;
            // 画像素材ファイル
            $seqNo = self::arrayIfNullFail($item_array[$i], ['material', 'seq_no'], "[ ${row} 番目の画像素材ファイル]はアップロードされません。", true);
            // exist check
            $task_result_file = self::arrayIfNullFail($file_map, [$seqNo], "[ ${row} 番目の画像素材ファイル]は存在しません。", true);
            // ignore client value
            $view_content_array['item_array'][$i]['material']['file_name'] = $task_result_file['name'];
            $view_content_array['item_array'][$i]['material']['file_path'] = $task_result_file['file_path'];

            // 結果キャプチャーファイル
            $seqNo = self::arrayIfNull($item_array[$i], ['result_capture', 'seq_no'], -1, true);
            if ($seqNo < 0) {
                // ignore client value
                $view_content_array['item_array'][$i]['result_capture']['seq_no'] = null;
                $view_content_array['item_array'][$i]['result_capture']['file_name'] = null;
                $view_content_array['item_array'][$i]['result_capture']['file_path'] = null;
            } else {
                // exist check
                $task_result_file = self::arrayIfNullFail($file_map, [$seqNo], "[ ${row} 番目の結果キャプチャーファイル]は存在しません。", true);
                // ignore client value
                $view_content_array['item_array'][$i]['result_capture']['file_name'] = $task_result_file['name'];
                $view_content_array['item_array'][$i]['result_capture']['file_path'] = $task_result_file['file_path'];
            }
        }
        //暗号化される添付ファイル
        $attachments = self::getMailAttachments($this->task_id, \Auth::user()->id);
        $attachment_not_unzipped_file_map = [];
        foreach ($attachments['attachment_not_unzipped_files'] as $not_unzipped_file) {
            $attachment_not_unzipped_file_map[$not_unzipped_file->id] = $not_unzipped_file;
        }
        $zip_array = self::arrayIfNull($view_content_array, ['zip_array'], []);
        for ($i = 0; $i < count($zip_array); $i++) {
            $row = $i + 1;
            $seqNo = self::arrayIfNullFail($zip_array[$i], ['seq_no'], "[ ${row} 番目の暗号化される添付ファイル]は存在しません。", true);
            // exist check
            $not_unzipped_file = self::arrayIfNullFail($attachment_not_unzipped_file_map, [$seqNo], "[ ${row} 番目の暗号化される添付ファイル]は存在しません。", true);
            // ignore client value
            $view_content_array['zip_array'][$i]['file_name'] = $not_unzipped_file->name;
            $view_content_array['zip_array'][$i]['file_path'] = $not_unzipped_file->file_path;
        }
        return $view_content_array;
    }

    /**
     * 作業内容を初期化する.
     * @param int $task_id タスクID
     * @param int $step_id ステップID
     * @param array $view_content_array ビュー形式のJSONファイル
     * @return TaskResult タスク結果
     * @throws Exception 初期化に失敗しました
     */
    private function saveContent(int $task_id, int $step_id, array $view_content_array)
    {
        \DB::beginTransaction();
        try {
            // 排他制御
            $this->exclusiveTask($this->task_id, \Auth::user()->id);
            $db_task_result_info = self::convertToDb($view_content_array);
            // データベースに新しいジョブ履歴書を作成する
            $task_result_po = new TaskResult();
            $task_result_po->task_id = $task_id;
            $task_result_po->step_id = $step_id;
            $task_result_po->created_user_id = \Auth::user()->id;
            $task_result_po->updated_user_id = \Auth::user()->id;
            $task_result_po->content = json_encode($db_task_result_info['task_result_array']);
            $task_result_po->save();

            // 新しく生成されたファイルをデータベースに挿入します
            foreach ($db_task_result_info['task_result_file_array'] as $task_result_file) {
                $task_result_file_po = new TaskResultFile();
                $task_result_file_po->seq_no = $task_result_file['seq_no'];
                $task_result_file_po->name = $task_result_file['name'];
                $task_result_file_po->file_path =  $task_result_file['file_path'];
                $task_result_file_po->width = self::arrayIfNull($task_result_file, ['width']);
                $task_result_file_po->height = self::arrayIfNull($task_result_file, ['height']);
                $task_result_file_po->size = self::arrayIfNull($task_result_file, ['size']);
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
     * 対応表（Excel）.
     * @param array $content データベース形式JSONファイル
     * @param array $task_result_file_array 結果ファイル
     * @return array 対応表
     * @throws \PhpOffice\PhpSpreadsheet\Exception excel exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception excel exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception excel exception
     */
    private function checkResultExcel(array $content, array $task_result_file_array) : array
    {
        // create excel file
        // excel file upload to s3

        $abbey_ids = [];
        foreach ($content['G00000_1'] as $check_result) {
            array_push($abbey_ids, $check_result['C00100_3']);
        }

        $abbeys = Abbey::searchByAbbeyId($abbey_ids);

        // 対応表の生成
        $excel_file_name = 'チェック結果_' . $content['C00100_9'] . '_' . $this->request_id . '.xlsx';

        $tmp_file_path = storage_path('biz/b00007/result_template.xlsx');
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
        $spreadsheet = $reader->load($tmp_file_path);
        $worksheet = $spreadsheet->getSheetByName("Worksheet");

        // テンプレート内の値のコピーに使用
        $template_sheet = clone $worksheet;
        $check_files = $content['G00000_1'];

        // 基準となる行を探索
        $start_string = '%start_cell%';
        $start_cell = null;
        $start_row = null;
        $start_column_string = null;
        foreach ($template_sheet->getRowIterator() as $row) {
            $cell_iterator = $row->getCellIterator();
            $cell_iterator->setIterateOnlyExistingCells(false); //すべてのセルを確認
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
        $cell_iterator->setIterateOnlyExistingCells(false); //すべてのセルを確認
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

        // ファイルサイズを取得
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));

        // 指定した行に値を設定
        for ($i = 0; $i < count($check_files); $i++) {
            // 結果判定
            $is_success = '';
            if ($check_files[$i]['C00500_2'] === 1) {
                $is_success = '判定OK';
            } elseif ($check_files[$i]['C00500_2'] === 0) {
                $is_success = '判定NG';
            } elseif ($check_files[$i]['C00500_2'] === 2) {
                $is_success = '不明あり';
            }

            $material_file = null;
            $result_capture_file = null;
            $material_file_seq_no = $check_files[$i]['C00800_7'];
            $check_file_seq_no = $check_files[$i]['C00800_8'];
            foreach ($task_result_file_array as $result_file) {
                if ($result_file['seq_no'] === $material_file_seq_no) {
                    $material_file = $result_file;
                } else if ($result_file['seq_no'] === $check_file_seq_no) {
                    $result_capture_file = $result_file;
                }
                if ($result_capture_file != null && $material_file != null) {
                    break;
                }
            }
            $regulation = '';
            foreach ($abbeys as $value) {
                if ($value->abbey_id == $check_files[$i]['C00100_3']) {
                    $linefeed_code = "\n";
                    $regulation = '横幅:' . $value->width . $linefeed_code;
                    $regulation .= '高さ:' . $value->hight . $linefeed_code;
                    $regulation .= 'ファイルサイズ:' . $value->file_size . ($value->file_size_unit == 1 ? 'KB' : 'MB') . $linefeed_code;
                    break;
                }
            }
            // セルにデータをセット
            $cellNumber = $i + $start_row->getRowIndex();
            $worksheet->setCellValueByColumnAndRow($start_column_index, $cellNumber, $i + 1);
            $worksheet->setCellValueByColumnAndRow($start_column_index + 1, $cellNumber, $check_files[$i]['C00100_4']);
            $worksheet->setCellValueByColumnAndRow($start_column_index + 2, $cellNumber, $check_files[$i]['C00100_3']);
            $worksheet->setCellValueByColumnAndRow($start_column_index + 3, $cellNumber, $material_file['name']);
            $file_size = $disk->size($material_file['file_path']); //ファイルサイズを取得 TODO 課題ADTAKT_PF-4
            $worksheet->setCellValueByColumnAndRow($start_column_index + 4, $cellNumber, self::convert($material_file['name'], number_format($file_size)));
            $worksheet->setCellValueByColumnAndRow($start_column_index + 5, $cellNumber, $is_success);
            $worksheet->setCellValueByColumnAndRow($start_column_index + 6, $cellNumber, $check_files[$i]['C00200_5']);
            $worksheet->setCellValueByColumnAndRow($start_column_index + 7, $cellNumber, $check_files[$i]['C00200_6']);
            $worksheet->setCellValueByColumnAndRow($start_column_index + 8, $cellNumber, $regulation);
            $capture_file_url = '';
            if (!empty($result_capture_file['file_path'])) {
                $capture_file_url = self::createDownloadUrl($result_capture_file['file_path'], $result_capture_file['name']);
            }
            $worksheet->setCellValueByColumnAndRow($start_column_index + 9, $cellNumber, $capture_file_url);
            $material_file_url = '';
            if (!empty($material_file['file_path'])) {
                $material_file_url = self::createDownloadUrl($material_file['file_path'], $material_file['name']);
            }
            $worksheet->setCellValueByColumnAndRow($start_column_index + 10, $cellNumber, $material_file_url);

            // 折り返しで高さを自動にするため
            $worksheet->getRowDimension($cellNumber)->SetRowHeight(-1);
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

        // ローカルに置く際の一時ファイル名
        $microtime_float = explode(".", (microtime(true)) . '.');
        $tmp_file_name = $microtime_float[0] . $microtime_float[1] . $excel_file_name;

        // ファイルの出力
        $writer->save(storage_path() . '/app/public/' . $tmp_file_name);

        // ファイルのアップロード
        $local_disk = Storage::disk('public');
        $seq_no = -1;
        foreach ($task_result_file_array as $result_file) {
            if ($result_file['seq_no'] > $seq_no) {
                $seq_no = $result_file['seq_no'];
            }
        }
        $seq_no++;

        $file = array(
            'file_name' => $excel_file_name,
            'file_data' => $local_disk->get($tmp_file_name),
            'task_result_file_seq_no' => $seq_no
        );
        $upload_data = array(
            'business_id' => 'B00007',
            'file' => $file
        );
        $task_result_file = self::uploadFile($upload_data);

        // 一時ファイルの削除
        $local_disk->delete($tmp_file_name);

        return array(
            'file_name' => $excel_file_name,
            'download_url' => self::createDownloadUrl($task_result_file['file_path'], $excel_file_name)
        );
    }

    /**
     * メールを登録する.
     * @param array $content データベース形式JSONファイル
     * @param array $task_result_file_array 結果ファイル
     * @param array|null $excel_file 対応表（Excel）
     * @return SendMail 送信メール記録
     * @throws \Throwable メールの登録に失敗しました
     */
    private function registerMail(array $content, array $task_result_file_array, array $excel_file = null) : SendMail
    {
        // メール送信者のアドレスを取得
        $business_id = RequestModel::where('id', $this->request_id)->pluck('business_id')->first();
        $business = Business::with('users')->where('id', $business_id)->first();

        $request_work = RequestWork::findOrFail($this->request_work_id);
        $request_mail_id = $request_work->requestMails[0]->id;
        $request_mail = RequestMail::where('id', $request_mail_id)->first();
        $request_mail_subject = isset($request_mail->subject) ? $request_mail->subject : '';

        // ファイルサイズを取得
        $disk = \Storage::disk(\Config::get('filesystems.cloud'));
        $task_result_type = $content['results']['type'];
        $check_files = $content['G00000_1'];
        $material_file_array = [];
        $result_capture_file_array = [];
        //素材全件
        foreach ($task_result_file_array as $result_file) {
            foreach ($check_files as $check_file) {
                if ($result_file['seq_no'] === $check_file['C00800_7']) {
                    $file_size = $disk->size($result_file['file_path']); //ファイルサイズを取得 TODO 課題ADTAKT_PF-4
                    $result_file['check_file_name'] = self::convert($result_file['name'], number_format($file_size)); //チェックファイル名
                    $result_file['download_url'] = self::createDownloadUrl($result_file['file_path'], $result_file['name']);
                    $result_file['is_success'] = true;
                    if ($check_file['C00500_2'] !== 1) {
                        $result_file['is_success'] === false;
                    }
                    array_push($material_file_array, $result_file);
                    break;
                }
                $capture_file_seq_no = self::arrayIfNull($check_file, ['C00800_8'], -1, true);
                if ($capture_file_seq_no >= 0) {
                    if ($result_file['seq_no'] === $check_file['C00800_8']) {
                        $result_file['download_url'] = self::createDownloadUrl($result_file['file_path'], $result_file['name']);
                        array_push($result_capture_file_array, $result_file);
                        break;
                    }
                }
            }
        }
        $material_zip_file = null;
        if (count($material_file_array) > 4) {
            $material_zip_file = $this->createZipFile('B00007', $material_file_array, 'material.zip', true);
            $material_zip_file['download_url'] = self::createDownloadUrl($material_zip_file['file_path'], $material_zip_file['file_name']);
        }


        $mail_body_data = [
            'mail_type' => $task_result_type,
            'business_name' => $business->name,
            'comment' => $content['results']['comment'],
            'request_mail' => $request_mail,
            'check_files' => $material_file_array,
            'check_file_zip' => $material_zip_file,
            'excel_file' => $excel_file,
            'result_files' => $result_capture_file_array
        ];

        // 判定
        $is_check_ok = true;
        for ($i = 0; $i < count($check_files); $i++) {
            // 結果判定
            if ($check_files[$i]['C00500_2'] !== 1) {
                $is_check_ok = false;
                break;
            }
        }

        $mail_type = '結果報告';
        if ($task_result_type == 1) {
            $mail_type = '不明あり';
        }
        $mail_subject_data = [
            'is_check_decision' => $is_check_ok ? '' : '【NG】',
            'business_name' => $business->name,
            'mail_type' => $mail_type,
            'request_name' => $request_mail_subject,
        ];

        // 本文生成
        $mail_body = $this->makeMailBody($mail_body_data);

        $send_mail = new SendMail;
        $send_mail->request_work_id = $this->request_work_id;
        $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));

        if ($mail_body_data['mail_type'] == \Config::get('const.TASK_RESULT_TYPE.CONTACT')) {
            $send_mail->to = config('biz.b00007.MAIL_SETTING.contact_mail_receiver');
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
        $send_mail->subject = self::generateSendMailSubject($mail_subject_data);
        $send_mail->content_type = $request_mail->content_type;
        $send_mail->body = $mail_body;
        $send_mail->created_user_id = \Auth::user()->id;
        $send_mail->updated_user_id = \Auth::user()->id;
        $send_mail->save();
        return $send_mail;
    }

    /**
     * メール本文を作成する.
     * @param array $mail_body_data 本文の内容
     * @return string メール本文
     */
    public function makeMailBody(array $mail_body_data) : string
    {
        $mail_body = '';
        if ($mail_body_data['mail_type'] == \Config::get('const.TASK_RESULT_TYPE.CONTACT')) {
            $mail_body = \View::make('biz.b00007.s00013.emails.contact')
                ->with($mail_body_data)
                ->render();
        } elseif ($mail_body_data['mail_type'] == \Config::get('const.TASK_RESULT_TYPE.DONE')) {
            $mail_body = \View::make('biz.b00007.s00013.emails.done')
                ->with($mail_body_data)
                ->render();
        }

        return $mail_body;
    }

    /**
     * メールの件名を生成.
     * @param array $data 件名の内容
     * @return string メールの件名
     */
    private static function generateSendMailSubject(array $data) : string
    {
        $subject = $data['is_check_decision'] . '【' . $data['business_name'] . '】' . $data['mail_type'] . ' Re:' . $data['request_name'];

        return $subject;
    }

    /**
     * 排他制御.
     * @param int $task_id タスクID
     * @param int $user_id ユーザーID
     * @throws \Exception 排他失败
     */
    private function exclusiveTask(int $task_id, int $user_id)
    {
        $query = Task::where('id', $task_id)
            ->where('user_id', $user_id)
            ->where('status', '<>', \Config::get('const.TASK_STATUS.DONE'))
            ->where('is_active', \Config::get('const.FLG.ACTIVE'))
            //            ->where('updated_at', '<', $this->task_started_at)
            ->lockForUpdate();
        $task = $query->first();
        if ($task === null) {
            throw new \Exception("The task does not exist or is completed, task_id:$task_id user_id:$user_id");
        }
    }

    /**
     * 排他制御.
     * @param int $task_id タスクID
     * @param int $user_id ユーザーID
     * @throws \Exception 排他失败
     * @return mixed
     */
    private function exclusiveTaskByUserId(int $task_id, int $user_id, bool $lock = true)
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
     * 現在のユーザーが業務の管理者であるかどうかを確認します.
     * @param int $business_id 業務Id
     * @param int $user_id ユーザーId
     * @return bool true or false
     */
    private function isBusinessAdmin(int $business_id, int $user_id) : bool
    {
        $result = \DB::table('businesses_admin')
            ->where('business_id', $business_id)
            ->where('user_id', $user_id)
            ->count();
        return $result > 0;
    }
    /**
     * TaskIdでタスク関連情報を取得する.
     * @param int $task_id タスクId
     * @return mixed
     */
    protected function getExtInfoByTeskId(int $task_id)
    {
        $result = \DB::table('businesses')
            ->selectRaw(
                'businesses.id business_id,' .
                'businesses.company_id,'.
                'request_works.step_id,'.
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
     * 検索用の文字列を作成.
     * @param string $keyword キーワード
     * @return string 検索用の文字列
     */
    private function createNgramString(string $keyword) : string
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
    }

    /**
     * 配列のNULL処理.
     * @param array $array 配列
     * @param array $key_array キー配列，例（['top','sec'] 対応 ['top' => ['sec' => 'aaaa']])
     * @param mixed|null|array|object $default キーが存在しない場合に使用するデフォルト値
     * @param bool $empty_check null値を確認するかどうか
     * @return mixed|null|array|object キーが存在する場合、対応する値が返されます。それ以外の場合、指定されたデフォルト値が返されます
     */
    private function arrayIfNull(array $array, array $key_array, $default = null, bool $empty_check = false)
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
    private function arrayIfNullFail(array $array, array $key_array, string $fail_msg = 'null point exception', bool $empty_check = false)
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

    /**
     * 特定のファイルのチェック.
     * @param string $file_name ファイル名
     * @return bool true:有効な,false:無効
     */
    private function specificExtensionCheck(string $file_name) : bool
    {
        // Exclusion hidden file name
        if (mb_substr($file_name, 0, 1) == '.') {
            return false;
        }

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
     * ファイル名変換.
     * @param string $file_name ファイル名
     * @param string $file_size ファイルサイズ
     * @return string 変換されたファイル名
     */
    private function convert(string $file_name, string $file_size) : string
    {
        return self::shortHash($file_name, $file_size) . substr($file_name, strrpos($file_name, '.'));
    }

    /**
     * 引数で受け取った文字列の配列の値をつなげ、crc32->16進数でハッシュ化.
     * @param mixed ...$arg 文字列
     * @return string crc32-> 16進文字列として表されます
     */
    private function shortHash(...$arg) : string
    {
        $data = '';
        foreach ($arg as $key => $value) {
            $data = $data . $value;
        }

        return dechex(crc32($data));
    }

    /**
     * ファイルをs3にアップロードする.
     * @param array $data アップロードするすべてのファイルを含む配列
     * @param string $type base64:ファイルコンテンツはbase64で取得されます, content:ファイルの内容はバイト単位で取得されます
     * @param bool $return_detail_info 詳細を返すかどうか(file_size,mini_type,src)
     * @return array (file_name, file_path, url, file_size, mime_type) ファイル情報
     * @throws Exception アップロードに失敗しました
     */
    private static function uploadFile(array $data, string $type = 'content', bool $return_detail_info = false): array
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
     * zipファイルを作成.
     * @param string $business_id 業務ID
     * @param array $src_file_array ソースファイル
     * @param string $zip_file_name zipファイル名
     * @param bool $upload_to_s3 生成されたzipファイルは、s3サービスにアップロードするかどうか
     * @return array ファイル情報
     * @throws \Throwable  zipに失敗しました
     */
    private function createZipFile(string $business_id, array $src_file_array, string $zip_file_name, bool $upload_to_s3 = false): array
    {
        // 管理用配列
        $manage_array = [];
        try {
            // ファイルをダウンロード
            foreach ($src_file_array as $file) {
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
                $local_disk = \Storage::disk('public');
                $microtime_float = explode('.', (microtime(true)) . '.');
                $tmp_file_name = $microtime_float[0] . $microtime_float[1] . $file_name;
                $local_file_path = 'tmp/' . $tmp_file_name;
                $local_disk->put($local_file_path, $disk->get($file_path));
                $tmp_file_path = storage_path() . '/app/public/' . $local_file_path;
                array_push($manage_array, ['file_name' => $file_name, 'file_full_path' => $tmp_file_path, 'local_disk_path' => $local_file_path]);
            }

            // zipファイルの作成
            $zip = new ZipArchive();
            // ZIPファイルをオープン
            $microtime_float = explode('.', (microtime(true)) . '.');
            $tmp_zip_file_name = $microtime_float[0] . $microtime_float[1] . $zip_file_name;
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

            $local_disk = \Storage::disk('public');
            $file_content = $local_disk->get($tmp_local_file_path);

            if ($upload_to_s3) {
                // ファイルアップロード
                $upload_data = [
                    'business_id' => $business_id,
                    'file' => [
                        'file_name' => $zip_file_name,
                        'file_data' => $file_content,
                    ]
                ];
                $zip_file = $this->uploadFile($upload_data, 'content', true);
                // $task_result_file['download_url'] = url('utilities/download_allow_file?file_path=' . urlencode($task_result_file['file_path']) . '&file_name=' . urlencode($task_result_file['name']));
                // 一時ファイルを削除
                array_push($manage_array, ['file_name' => $tmp_zip_file_name, 'file_full_path' => $tmp_zip_file_path, 'local_disk_path' => $tmp_local_file_path]);
            } else {
                $mime_type = \File::mimeType($tmp_zip_file_path);
                $file_size = filesize($tmp_zip_file_path);
                $zip_file = [
                    'disk' => $local_disk,
                    'file_name' => $tmp_zip_file_name,
                    'file_path' => $tmp_local_file_path,
                    'url' => null,
                    'src' => null,
                    'file_size' => $file_size,
                    'mime_type' => $mime_type
                ];
            }
            foreach ($manage_array as $file) {
                $local_disk = \Storage::disk('public');
                $local_disk->delete($file['local_disk_path']);
            }
            return $zip_file;
        } catch (\Throwable $th) {
            // 一時ファイルを削除
            foreach ($manage_array as $file) {
                $local_disk = \Storage::disk('public');
                $local_disk->delete($file['local_disk_path']);
            }
            throw $th;
        }
    }

    /**
     * リクエストメールの添付ファイル情報の取得.
     * @param int $task_id タスクID
     * @param int $user_id ユーザーID
     * @return array リクエストメールの添付ファイル情報
     * @throws Exception 添付ファイルの取得に失敗しました
     */
    private function getMailAttachments(int $task_id, int $user_id) : array
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
     * 外部からダウンロードするためのURLを生成.
     * @param string $url ファイルパス
     * @param string $file_name ファイルネーム
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string 外部からダウンロードするためのURL
     */
    private static function createDownloadUrl(string $url, string $file_name)
    {

        $uri = url('utilities/download_allow_file?file_path=' . urlencode($url) . '&file_name=' . urlencode($file_name));

        return $uri;
    }

    /**
     * 成功メッセージを返す.
     * @param null|array|mixed $data オブジェクトを返す
     * @return \Illuminate\Http\JsonResponse 成功メッセージ
     */
    private function success($data = null)
    {
        $message = ['result' => 'success', 'err_message' => '', 'data' => $data];
        return response()->json($message);
    }

    /**
     * 失敗したメッセージを返す.
     * @param string $errorMsg エラーメッセージ
     * @return \Illuminate\Http\JsonResponse エラーメッセージ
     */
    private function error(string $errorMsg)
    {
        $message = ['result' => 'error', 'err_message' => $errorMsg];
        return response()->json($message);
    }
}
