<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\UploadFileManager\Uploader;
use Carbon\Carbon;
use App\Models\ReportFile;
use App\Models\WorkTimeBusinessReport;
use App\Models\WorkTimeOperatorReport;
use App\Services\ZipFileManager\ZipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ReportsController extends Controller
{
    private $result;
    private $err_message;
    private $business_id;
    private $report_id;
    private $report_type;

    public function __construct(Request $req)
    {
        $this->business_id = $req->business_id;
        $this->report_id = $req->report_id;
        $this->result = 'warning';
        $this->err_message = '';
        $this->report_type = $req->input('reportType');
    }

    /**
     * レポート関連情報を取得する
     *
     * @return JsonResponse
     */
    public function getReportInfo(): JsonResponse
    {
        $user = \Auth::user();

        $businesses = null;
        $candidates = null;

        if (Gate::allows('system-admin', $user)) {
            // システム管理者・・・全業務
            $businesses = \DB::table('businesses')
                ->select(
                    'businesses.id as id',
                    'businesses.name as name'
                )
                ->where('businesses.is_deleted', \Config::get('const.FLG.INACTIVE'))
                ->get();

            // システム管理者・・・全ユーザー
            $candidates = \DB::table('users')
                ->select(
                    'users.id as id',
                    'users.name as name',
                    'users.user_image_path as image_path'
                )
                ->get();
        } else {
            // 業務管理者・・・自分が担当する全業務
            $businesses = \DB::table('businesses')
                ->select(
                    'businesses.id as id',
                    'businesses.name as name'
                )
                ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
                ->where('businesses_admin.user_id', $user->id)
                ->where('businesses.is_deleted', \Config::get('const.FLG.INACTIVE'))
                ->get();

            // 業務管理者・・・自分が担当する業務の全担当者
            $candidates = \DB::table('users')
                ->select(
                    'users.id as id',
                    'users.name as name',
                    'users.user_image_path as image_path'
                )
                ->join('businesses_candidates', 'users.id', '=', 'businesses_candidates.user_id')
                ->whereIn('businesses_candidates.business_id', $businesses->pluck('id'))
                ->groupBy('users.id')
                ->get();
        }

        // 先に取得した全業務に紐づく全作業
        $steps = \DB::table('steps')
            ->select(
                'steps.id as id',
                'steps.name as name',
                'business_flows.business_id as business_id'
            )
            ->join('business_flows', 'steps.id', '=', 'business_flows.step_id')
            ->whereIn('business_flows.business_id', $businesses->pluck('id'))
            ->groupBy('steps.id', 'business_flows.business_id')
            ->get();

        $reports = \DB::table('reports')
            ->select(
                'reports.id as report_id',
                'reports.name as report_name',
                'reports.description as report_description',
                'businesses.id as business_id',
                'businesses.name as business_name'
            )
            ->join('businesses_reports', 'businesses_reports.report_id', '=', 'reports.id')
            ->join('businesses', 'businesses.id', '=', 'businesses_reports.business_id')
            ->whereIn('businesses_reports.business_id', $businesses->pluck('id'))
            ->where('reports.is_deleted', \Config::get('const.FLG.INACTIVE'))
            ->get();

        return response()->json([
            'businesses' => $businesses,
            'steps' => $steps,
            'reports' => $reports,
            'candidates' => $candidates
        ]);
    }

    public function output(Request $req)
    {
        $user = \Auth::user();
        $report_type = $req->input('reportType');
        if ($report_type === 'businessReport') {
            $res = $this->outputByBusinessIds($req);
        } elseif ($report_type === 'operatorReport') {
            $res = $this->outputByUserIds($req);
        } else {
            // カスタムレポート出力処理
            $res = $this->customOutput($req);
        }

        // 戻り値がresponseなので、必要なデータの取り出し
        $result_json = $res->content();
        $result_array = json_decode($result_json, true);
        if (strcmp($result_array['result'], 'success') !== 0) {
            throw new \Exception('error custom out put');
        }
        $report_files = $result_array['report_files'];

        // response用
        $response_file_name = '';
        $response_file_path = '';

        // 削除するファイルの管理
        $managed_tmp_files = [];

        $local_disk = \Storage::disk('public');

        \DB::beginTransaction();
        try {
            if (is_null($report_files) || count($report_files) === 0) {
                throw new \Exception('no report file');
            } elseif (count($report_files) === 1) {
                // ファイルが一つ
                $file = $report_files[0];
                $path_after_upload = $this->tryUpload($file['path'], $file['name']);
                $current_time = Carbon::now();
                $insert_report_file = [
                    'report_id' => $this->report_id,
                    'name' => $file['name'],
                    'file_path' => $path_after_upload,
                    'created_user_id' => $user->id,
                    'created_at' => $current_time,
                    'updated_user_id' => $user->id,
                    'updated_at' => $current_time,
                ];
                $insert_report_files[] = $insert_report_file;

                // responseの値をセット
                $pattern = '[' . $local_disk->path('') . ']';// []delimiters
                $response_file_path = preg_replace(
                    $pattern,
                    '',
                    $file['path'],
                    1
                );// full_pathからStorage:public以下のpathに変換
                $response_file_name = $file['name'];
            } else {
                // 複数ファイルある場合はzipにまとめてupload

                // zipファイルの作成処理
                foreach ($report_files as $file) {// tmpファイルの管理配列に追加
                    $pattern = '[' . $local_disk->path('') . ']';// []delimiters
                    $local_file_path = preg_replace(
                        $pattern,
                        '',
                        $file['path'],
                        1
                    );// full_pathからStorage:public以下のpathに変換
                    if (!$local_disk->exists($local_file_path)) {// 存在を確認
                        throw new \Exception('File not exists.');
                    }
                    $tmp_file = [
                        'file_name' => $file['name'],
                        'local_path' => $local_file_path
                    ];
                    $managed_tmp_files[] = $tmp_file;
                }
                $zip_file_name = 'reports.zip';
                $zip_file = ZipService::compress($managed_tmp_files, $zip_file_name, 'test');// create zip

                // ファイルの削除
                $local_disk->delete(array_column($managed_tmp_files, 'local_path'));// 内包ファイル
                $managed_tmp_files[] = $zip_file;// for error process
                $path_after_upload = $this->tryUpload($zip_file['full_path'], $zip_file_name);
                $current_time = Carbon::now();
                $insert_report_file = [
                    'report_id' => $this->report_id,
                    'name' => $zip_file_name,
                    'file_path' => $path_after_upload,
                    'created_user_id' => $user->id,
                    'created_at' => $current_time,
                    'updated_user_id' => $user->id,
                    'updated_at' => $current_time,
                ];
                $insert_report_files[] = $insert_report_file;
                $response_file_name = $zip_file_name;
                $response_file_path = $zip_file['local_path'];
            }

            // ファイル情報をDBに保存
            ReportFile::insert($insert_report_files);
            \DB::commit();

            return response()->json([
                'result' => 'success',
                'file_name' => $response_file_name,
                'file_path' => $response_file_path,
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            // ファイルの削除
            $local_disk->delete(array_column($managed_tmp_files, 'local_path'));// 内包ファイル

            return response()->json([
                'result' => 'error'
            ]);
        }
    }

    public function outputByBusinessIds(Request $req)
    {
        $businessIds = $req->input('businessIds');
        $steps = $req->input('stepIds');
        $period = json_decode($req->input('period'), true);
        if (is_null($businessIds)) {
            throw new \Exception('businessIds is null');
        }
        if (is_null($steps)) {
            throw new \Exception('steps is null');
        }
        if (is_null($period)) {
            throw new \Exception('period is null');
        }

        try {
            $res = WorkTimeBusinessReport::getWorkTimeReportByBusinessIds($businessIds, $steps, $period);
            //ファイル作成
            $report_files = [];
            array_push($report_files, $this->businessReportToExcel($res));

            // 正常終了
            return response()->json([
                'result' => 'success',
                'report_files' => $report_files
            ]);
        } catch (\Exception $e) {
            // エラーあり
            report($e);
            return response()->json([
                'result' => 'error',
                'err_message' => ''
            ]);
        }
    }

    public function outputByUserIds(Request $req)
    {
        $user_ids = $req->input('userIds');
        $period = json_decode($req->input('period'), true);


        if (is_null($user_ids)) {
            throw new \Exception('user_ids is null');
        }
        if (is_null($period)) {
            throw new \Exception('period is null');
        }

        try {
            $res = WorkTimeOperatorReport::getWorkTimeReportByUserIds($user_ids, $period);
            //ファイル作成
            $report_files = [];
            array_push($report_files, $this->operatorReportToExcel($res));

            // 正常終了
            return response()->json([
                'result' => 'success',
                'report_files' => $report_files
            ]);
        } catch (\Exception $e) {
            // エラーあり
            report($e);
            return response()->json([
                'result' => 'error',
                'err_message' => ''
            ]);
        }
    }

    private function customOutput(Request $req)
    {
        // Create class instance
        $business_code = $req->input('business_code');
        if (is_null($business_code)) {
            throw new \Exception('business_code is null');
        }
        $class = "App\Http\Controllers\Api\Biz\\{$business_code}\\{$business_code}Controller";
        $instance = new $class;

        $form = json_decode($req->input('form'), true);
        if (is_null($form)) {
            throw new \Exception('form is null');
        }

        $report_id = $req->input('report_id');
        if (is_null($report_id)) {
            throw new \Exception('report_id is null');
        }

        $report = Report::findOrFail($report_id);
        $func = $report->identifier;
        // NOTE: 一旦は業務IDで分岐する
        // TODO: 項目もDB定義し実装は不要とする
        if ($this->business_id == 6) {
            $req->accounting_year_month = $form['accounting_year_month'];
            $req->transfer_date = $form['transfer_date'];
        } elseif ($this->business_id == 8) {
            $req->started_at = $form['started_at'];
            $req->finished_at = $form['finished_at'];
        } elseif ($this->business_id == 12) {
            $req->business_id = $this->business_id;
            $req->started_at = $form['started_at'];
            $req->finished_at = $form['finished_at'];
        } elseif ($this->business_id == 14) {
            $req->business_id = $this->business_id;
            $req->started_at = $form['started_at'];
            $req->finished_at = $form['finished_at'];
        } elseif ($this->business_id == 16) {
            $req->business_id = $this->business_id;
            $req->started_at = $form['started_at'];
            $req->finished_at = $form['finished_at'];
        } else {
            throw new \Exception('not exist custom report.');
        }

        // Method call
        return $instance->$func($req);
    }

    private function tryUpload(string $file_full_path, string $rename = null): string
    {
        try {
            $file_name = $rename;
            if (is_null($file_name)) {
                $file_name = basename($file_full_path);
            }

            if ($this->report_type === 'businessReport') {
                $upload_path = 'report_files/business_report/' . Carbon::now()->format('Ymd') . '/' . md5(strval(microtime(true))) . '/' . $file_name;
            } elseif ($this->report_type === 'operatorReport') {
                $upload_path = 'report_files/operatorReport/' . Carbon::now()->format('Ymd') . '/' . md5(strval(microtime(true))) . '/' . $file_name;
            } else {
                $upload_path = 'report_files/' . $this->business_id . '/' . Carbon::now()->format('Ymd') . '/' . md5(strval(microtime(true))) . '/' . $file_name;
            }
            Uploader::uploadToS3(file_get_contents($file_full_path), $upload_path);
            return $upload_path;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function businessReportToExcel(array $res)
    {
        $template_file_path = storage_path('report/business/template.xlsx');
        $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($template_file_path);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type);
        $spreadsheet = $reader->load($template_file_path);
        $worksheet = $spreadsheet->getSheetByName("業務別工数レポート");
        $start = 2;
        $index = 0;
        for ($i = 0; $i < count($res); $i++) {
            $process_name = '';
            // 工程を判断
            if ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.IMPORT')) {
                $process_name = '取込';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.ALLOCATE')) {
                $process_name = '割振';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.TASK')) {
                $process_name = 'タスク';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.APPROVE')) {
                $process_name = '承認';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.DELIVERY')) {
                $process_name = '納品';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.MANAGE')) {
                $process_name = '管理';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.MASTER_SET')) {
                $process_name = 'マスタ設定';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.CLIENT_CHECK')) {
                $process_name = 'クライアント確認';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.VERIFICATION')) {
                $process_name = '検証';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.OTHER')) {
                $process_name = '他';
            }

            $index = $start + $i;
            // excelに書き出す
            $worksheet->setCellValue(
                'A' . $index,
                $res[$i]->actual_date
            );
            $worksheet->setCellValue(
                'B' . $index,
                $res[$i]->business_name
            );
            $worksheet->setCellValue(
                'C' . $index,
                $res[$i]->step_name
            );
            $worksheet->setCellValue(
                'D' . $index,
                $process_name
            );
            $worksheet->setCellValue(
                'E' . $index,
                $res[$i]->work_count
            );
            $worksheet->setCellValue(
                'F' . $index,
                $res[$i]->worker_count
            );
            $worksheet->setCellValue(
                'G' . $index,
                $res[$i]->system_work_time
            );
            $worksheet->setCellValue(
                'H' . $index,
                $res[$i]->manual_work_time
            );
            $worksheet->setCellValue(
                'I' . $index,
                $res[$i]->education_flg === \Config::get('const.FLG.ACTIVE') ? '教育' : '通常'
            );

            // 火付けをフォーマット
            $worksheet->getStyle('A' . $index)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDD);
        }
        // ファイルをローカルに保存
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $file_name = '業務別工数レポート' . '.xlsx';
        $directory_path = storage_path() . '/app/public/';
        $local_file_path = $directory_path . $file_name;
        $writer->save($local_file_path);
        // ファイル作成
        $file = [
            'name' => $file_name,
            'path' => $local_file_path
        ];

        return $file;
    }

    private function operatorReportToExcel(array $res)
    {
        $template_file_path = storage_path('report/user/template.xlsx');
        $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($template_file_path);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type);
        $spreadsheet = $reader->load($template_file_path);
        $worksheet = $spreadsheet->getSheetByName("担当者別工数レポート");
        $start = 2;
        $index = 0;
        for ($i = 0; $i < count($res); $i++) {
            $process_name = '';
            // 工程を判断
            if ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.IMPORT')) {
                $process_name = '取込';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.ALLOCATE')) {
                $process_name = '割振';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.TASK')) {
                $process_name = 'タスク';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.APPROVE')) {
                $process_name = '承認';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.DELIVERY')) {
                $process_name = '納品';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.MANAGE')) {
                $process_name = '管理';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.MASTER_SET')) {
                $process_name = 'マスタ設定';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.CLIENT_CHECK')) {
                $process_name = 'クライアント確認';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.VERIFICATION')) {
                $process_name = '検証';
            } elseif ($res[$i]->process_type === \Config::get('const.WORK_PROCESS_TYPE.OTHER')) {
                $process_name = '他';
            }

            $index = $start + $i;
            // excelに書き出す
            $worksheet->setCellValue(
                'A' . $index,
                $res[$i]->actual_date
            );
            // todo: B,C,D列にユーザーの所属を設定
            $worksheet->setCellValue(
                'B' . $index,
                ''
            );
            $worksheet->setCellValue(
                'C' . $index,
                ''
            );
            $worksheet->setCellValue(
                'D' . $index,
                ''
            );
            $worksheet->setCellValue(
                'E' . $index,
                $res[$i]->user_name
            );
            $worksheet->setCellValue(
                'F' . $index,
                $res[$i]->business_name
            );
            $worksheet->setCellValue(
                'G' . $index,
                $res[$i]->step_name
            );
            $worksheet->setCellValue(
                'H' . $index,
                $process_name
            );
            $worksheet->setCellValue(
                'I' . $index,
                $res[$i]->work_count
            );
            $worksheet->setCellValue(
                'J' . $index,
                $res[$i]->ok_count
            );
            $worksheet->setCellValue(
                'K' . $index,
                $res[$i]->ng_count
            );
            $worksheet->setCellValue(
                'L' . $index,
                $res[$i]->system_work_time
            );
            $worksheet->setCellValue(
                'M' . $index,
                $res[$i]->manual_work_time
            );
            $worksheet->setCellValue(
                'N' . $index,
                $res[$i]->education_flg === \Config::get('const.FLG.ACTIVE') ? '教育' : '通常'
            );

            // 火付けをフォーマット
            $worksheet->getStyle('A' . $index)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDD);
        }
        // ファイルをローカルに保存
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $file_name = '担当者別工数レポート' . '.xlsx';
        $directory_path = storage_path() . '/app/public/';
        $local_file_path = $directory_path . $file_name;
        $writer->save($local_file_path);
        // ファイル作成
        $file = [
            'name' => $file_name,
            'path' => $local_file_path
        ];

        return $file;
    }
}
