<?php


namespace App\Http\Controllers\Api\Biz\B00012;

use App\Http\Controllers\Api\Biz\Common\MailController;
use App\Http\Controllers\Controller;
use App\Models\TaskResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;
use Carbon\Carbon;

class B00012Controller extends Controller
{
    /**
     *  AG業務件数レポート（日本）
     * @param Request $req
     * @return mixed
     */
    public function agRegisterReport(Request $req)
    {
        try {
            //抽出条件の取得
            $business = $req->business_id;//業務ID
            $started_at = $req->started_at;//期間From
            $finished_at = $req->finished_at;//期間To

            //ファイル作成
            $report_files = array();
            $report_files[] = self::makeReport($started_at, $finished_at);

            // 終了
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

    /**
     * レポート出力
     * @param string $started_at 期間From
     * @param string $finished_at 期間To
     * @return array report file
     */
    private function makeReport(string $started_at, string $finished_at): array
    {
        $utc_timezone_started_at = Carbon::parse($started_at);
        $utc_timezone_finished_at = Carbon::parse($finished_at);
        $user_timezone_started_at = parse_utc_string_to_user_timezone_date($started_at);//期間From
        $user_timezone_finished_at = parse_utc_string_to_user_timezone_date($finished_at);//期間To
        // テンプレートファイルの読み込み
        $template_file_path = storage_path('biz/b00012/template.xlsx');
        $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($template_file_path);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type);
        $spreadsheet = $reader->load($template_file_path);
        $worksheet = $spreadsheet->getSheetByName("Sheet1");
        // 対象納品データの集計
        $result = DB::select(
            'select requests.business_id                              business_id,' .
            '       tasks.id                                          task_id,' .
            '       tasks.updated_at                                  task_update_at,' .
            '       approvals.updated_at                              approvals_update_at,' .
            '       businesses.name                                   business_name,' .
            '       requests.name                                     request_name,' .
            '       tasks.status                                      tasks_status,' .
            '       approvals.status                                  approvals_status,' .
            '       users.name                                        task_user_name,' .
            '       tmp_approval_users.name                           approval_users_name,' .
            '       deliveries.content                                deliveries_content,' .
            '       tasks.is_education                                is_education ' .
            'from requests' .
            '         inner join request_works on requests.id = request_works.request_id' .
            '         inner join tasks on request_works.id = tasks.request_work_id' .
            '         inner join businesses on requests.business_id = businesses.id' .
            '         inner join users on tasks.user_id = users.id' .
            '         left join approval_tasks on tasks.id = approval_tasks.task_id' .
            '         left join approvals on approval_tasks.approval_id = approvals.id' .
            '         left join users tmp_approval_users on approvals.updated_user_id = tmp_approval_users.id' .
            '         left join deliveries on approval_tasks.id = deliveries.approval_task_id ' .
            'where (tasks.status = 2 or approvals.status = 2) ' .
            'and business_id in (12, 13) ' .
            'and (tasks.updated_at between ? and ? or approvals.updated_at between ? and ?) ' .
            'order by tasks.updated_at',
            [$utc_timezone_started_at->toDateTimeString(), $utc_timezone_finished_at->toDateTimeString(), $utc_timezone_started_at->toDateTimeString(), $utc_timezone_finished_at->toDateTimeString()]
        );
        $index = 2;
        foreach ($result as $data) {
            // 最新の作業履歴
            $task_result_info = TaskResult::with('taskResultFiles')
                ->where('task_id', $data->task_id)
                ->orderBy('id', 'desc')
                ->first();

            $date = '';
            $business_name = $data->business_name;
            $request_name = $data->request_name;
            $project = '';
            $task_user_name = '';
            $count = '';
            $work_time = '';
            if (!empty($task_result_info)) {
                $work_time = $task_result_info->work_time;
            }
            if ($data->is_education == 1) {
                $is_education = '○';
            } else {
                $is_education = '';
            }
            $business_id = $data->business_id;

            if ($data->tasks_status == 2) {
                $project = '作業';
                $date = parse_utc_string_to_user_timezone_date($data->task_update_at)->format("Y/m/d");
                $task_user_name = $data->task_user_name;

                if (empty($task_result_info) || empty($task_result_info->content)) {
                    $task_content_array = [];
                } else {
                    $task_content_array = json_decode($task_result_info->content, true);
                }

                if ($data->business_id == 12) {
                    $count = MailController::arrayIfNull($task_content_array, ['G00000_27', 'G00000_33', 0, 'items', 0, 'value'], '');
                } else {
                    $count = MailController::arrayIfNull($task_content_array, ['G00000_1', 'C00100_4'], '');
                }
                $worksheet->setCellValue('A' . $index, $date);//日付
                $worksheet->setCellValue('B' . $index, $business_name);//業務名
                $worksheet->setCellValue('C' . $index, $request_name);//依頼名
                $worksheet->setCellValue('D' . $index, $project);//工程
                $worksheet->setCellValue('E' . $index, $task_user_name);//作業者
                $worksheet->setCellValue('F' . $index, $count);//件数
                $worksheet->setCellValue('G' . $index, $work_time);//手動工数（時間）
                $worksheet->setCellValue('H' . $index, $is_education);//演習
                $index++;
            }

            $begin_date = (int)$utc_timezone_started_at->format('YmdHis');
            $end_date = (int)$utc_timezone_finished_at->format('YmdHis');
            $approvals_date = 0;
            if (!empty($data->approvals_update_at)) {
                $approvals_date = (int)Carbon::parse($data->approvals_update_at)->format('YmdHis');
            }
            if ($data->tasks_status == 2 && $data->approvals_status == 2 && $approvals_date >= $begin_date && $approvals_date <= $end_date) {
                $project = '承認';
                $date = parse_utc_string_to_user_timezone_date($data->approvals_update_at)->format("Y/m/d");

                $task_user_name = $data->approval_users_name;
                $count = 0;
                if (!empty($data->deliveries_content)) {
                    $task_content_array = json_decode($data->deliveries_content, true);
                    if ($business_id == 12) {
                        $count = MailController::arrayIfNull($task_content_array, ['G00000_27', 'G00000_33', 0, 'items', 0, 'value'], 0);
                    } else {
                        $count = MailController::arrayIfNull($task_content_array, ['G00000_1', 'C00100_4'], '');
                    }
                }
                $worksheet->setCellValue('A' . $index, $date);//日付
                $worksheet->setCellValue('B' . $index, $business_name);//業務名
                $worksheet->setCellValue('C' . $index, $request_name);//依頼名
                $worksheet->setCellValue('D' . $index, $project);//工程
                $worksheet->setCellValue('E' . $index, $task_user_name);//作業者
                $worksheet->setCellValue('F' . $index, $count);//件数
                $worksheet->setCellValue('G' . $index, '');//手動工数（時間） ADPORTER_PF-527 【AG登録】カスタムレポート：承認に手動工数が表示されている
                $worksheet->setCellValue('H' . $index, $is_education);//演習
                $index++;
            }
        }
        // ファイルの保存（local）
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $file_name = 'AG業務件数レポート（日本）_' . $user_timezone_started_at->format('Ymd') . '-' . $user_timezone_finished_at->format('Ymd') . '.xlsx';
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


    /**
     * 返回成功报文
     * @param null|array|mixed $data 返回对象
     * @return \Illuminate\Http\JsonResponse
     */
    private function success($data = null)
    {
        $message = ['result' => 'success', 'err_message' => '', 'data' => $data];
        return response()->json($message);
    }

    /**
     * 返回失败报文
     * @param string $errorMsg 错误消息
     * @return \Illuminate\Http\JsonResponse
     */
    private function error($errorMsg)
    {
        $message = ['result' => 'error', 'err_message' => $errorMsg];
        return response()->json($message);
    }
}
