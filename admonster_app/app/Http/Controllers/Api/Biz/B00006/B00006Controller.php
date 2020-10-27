<?php


namespace App\Http\Controllers\Api\Biz\B00006;

use App\Models\ExpenseCarfare;
use App\Services\CommonMail\CommonDownloader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;

class B00006Controller extends Controller
{
    //消费税率
    const EXCISE_RATE = 1.1;

    /**
     * 経費：月次レポート
     * @param Request $req
     * @return mixed
     */
    public function keihiMonthlyReport(Request $req)
    {
        try {
            //抽出条件の取得
            $accounting_year_month = parse_utc_string_to_user_timezone_date($req->accounting_year_month)->format('Y/m');
            $transfer_date = $req->transfer_date;//振り込み日


            //ファイル作成
            $report_files = [];
            array_push($report_files, self::summaryReport($accounting_year_month, $transfer_date));
            array_push($report_files, self::detailReport($accounting_year_month, $transfer_date));
            $pdf_file_array = self::pdfMergeReport($accounting_year_month);
            if ($pdf_file_array) {
                array_push($report_files, $pdf_file_array);
            }
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

    /**
     * 交通费汇总报表
     * @param string $accounting_year_month 抽出条件
     * @param string $transfer_date 振り込み日
     * @return array 文件信息
     * @throws \Exception
     */
    private function summaryReport(string $accounting_year_month, string $transfer_date): array
    {

        $accounting_year_month = str_replace('/', '-', $accounting_year_month);
        // テンプレートファイルの読み込み
        $template_file_path = storage_path('biz/b00006/template.xlsx');
        $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($template_file_path);
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type);
        $spreadsheet = $reader->load($template_file_path);

        //交通費精算レポート
        // 対象納品データの集計
        $year_month = explode('-', $accounting_year_month);
        $list = $this->getCarfareSummary($accounting_year_month);
        $worksheet = $spreadsheet->getSheetByName("交通費精算レポート");
        $worksheet->setCellValueByColumnAndRow(2, 1, $year_month[0] . '年' . $year_month[1] . '月度　交通費精算レポート');
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $start = 11;
        $str = $worksheet->getStyle('C7')->getNumberFormat()->getFormatCode();//获取格式刷

        $index = 0;
        for ($i = 0; $i < count($list); $i++) {
            $index = $start + $i;

            $worksheet->mergeCells('D' . $index . ':F' . $index);
            $worksheet->mergeCells('G' . $index . ':H' . $index);

            //设置B列：社員番号
            $worksheet->setCellValue(
                'B' . $index,
                $list[$i]->employees_id
            );
            //设置C列：氏名
            $worksheet->setCellValue(
                'C' . $index,
                $list[$i]->name
            );
            //设置D列：フリガナ
            $worksheet->setCellValue(
                'D' . $index,
                $list[$i]->spell
            );
            //设置G列：申请合计金额
            $worksheet->setCellValue(
                'G' . $index,
                $list[$i]->price
            );
            //设置I列
            $worksheet->setCellValue(
                'I' . $index,
                "=ROUND(G" . $index . "/1.1,0)"
            );
            //设置F列
            $worksheet->setCellValue(
                'J' . $index,
                "=+G$index-I$index"
            );
            $worksheet->getStyle('G' . $index)->getNumberFormat()->setFormatCode($str);//设置货币格式刷
            $worksheet->getStyle('I' . $index)->getNumberFormat()->setFormatCode($str);//设置货币格式刷
            $worksheet->getStyle('J' . $index)->getNumberFormat()->setFormatCode($str);//设置货币格式刷

            $worksheet->getStyle('B' . $index)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);//设置文本列格式刷
            $worksheet->getStyle('G' . $index)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('EBF1DE');

            $worksheet->getStyle('I' . $index)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('D8D8D8');
            $worksheet->getStyle('J' . $index)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('D8D8D8');
            $worksheet->getStyle('B' . $index . ':H' . $index)->applyFromArray($styleArray);
        }

        $worksheet->setCellValue(
            'C7',
            "=SUM(G" . $start . ":G" . $index . ")"
        );

        $worksheet->setCellValue(
            'C6',
            "=COUNT(G" . $start . ":G" . $index . ")"
        );

        //仕訳データレポート
        $data = $this->getDetailReportData($accounting_year_month, $transfer_date);
        $worksheet_2 = $spreadsheet->getSheetByName("仕訳データレポート");
        $row_index = 0;
        foreach ($data as $row_data) {
            $row_index++;
            $col_index = 0;
            foreach ($row_data as $col_data) {
                $col_index++;
                $worksheet_2->setCellValueByColumnAndRow($col_index, $row_index, $col_data);
            }
        }

        // ファイルの保存（local）
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $file_name = 'keihiMonthlyReport_' . $year_month[0] . $year_month[1] . '.xlsx';
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
     * 交通费明细报表
     * @param string $accounting_year_month 抽出条件
     * @param string $transfer_date 振り込み日
     * @return array 文件信息
     * @throws \Exception
     */
    private function detailReport(string $accounting_year_month, string $transfer_date): array
    {
        $data = $this->getDetailReportData($accounting_year_month, $transfer_date);

        // ファイルの保存（local）
        $accounting_year_month = str_replace('/', '-', $accounting_year_month);
        $year_month = explode('-', $accounting_year_month);
        $file_name = 'keihiMonthlyReport_' . $year_month[0] . $year_month[1] . '.csv';
        $directory_path = storage_path() . '/app/public/';
        $local_file_path = $directory_path . $file_name;

//            // ファイルの出力
        self::writeDataToCsv($data, $local_file_path);
        $file = [
            'name' => $file_name,
            'path' => $local_file_path
        ];


        return $file;
    }

    /**
     * 数据写入到CSV文件
     * @param array $data 要写入的数据
     * @param string $tmp_file_path 临时文件的路径
     * @throws \Exception
     */
    private function writeDataToCsv(array $data, string $tmp_file_path): void
    {
        $fp = fopen($tmp_file_path, 'w+');
        if (!$fp) {
            //文件打开失败
            throw new \Exception("fail to open the file");
        }
        try {
            // 添加 BOM
            fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
            foreach ($data as $row) {
                $index = 0;
                foreach ($row as $item) {
                    if ($index !== 0) {
                        fwrite($fp, ',');
                    }
                    fwrite($fp, $item);
                    $index++;
                }
                fwrite($fp, "\r\n");
            }
        } finally {
            fclose($fp);
        }
    }

    /**
     * 获取纳品后的S00011的request_work_id的子查询
     * @param string $date 计上月
     * @return mixed
     */
    public static function getExpenseCarfaresDetail(string $date)
    {
        $zichaxun = DB::table('request_works')->selectRaw('request_works.before_work_id as id')
            ->join('approvals', 'approvals.request_work_id', '=', 'request_works.id')
            ->join('approval_tasks', 'approval_tasks.approval_id', '=', 'approvals.id')
            ->join('deliveries', 'deliveries.approval_task_id', '=', 'approval_tasks.id')
            ->where('request_works.step_id', 12);
        return $zichaxun;
    }

    /**
     * 获取交通费汇总
     * @param string $accounting_year_month
     * @return array
     */
    private function getCarfareSummary(string $accounting_year_month): array
    {
        $list = DB::select(
            ' select expense_carfares.employees_id,' .
            '        expense_carfares.name,' .
            '        expense_carfares.spell,' .
            '        sum(expense_carfares.price) as price' .
            ' from expense_carfares' .
            ' where `expense_carfares`.`date` = ?' .
            '     and `expense_carfares`.`expenses_type` = 0' .
            '     and `task_id` in (select `approval_tasks`.`task_id`' .
            '                     from `request_works`' .
            '                              inner join `approvals` on `approvals`.`request_work_id` = `request_works`.`id`' .
            '                              inner join `approval_tasks` on `approval_tasks`.`approval_id` = `approvals`.`id`' .
            '                              inner join `deliveries` on `deliveries`.`approval_task_id` = `approval_tasks`.`id`' .
            '                     where `request_works`.`step_id` = 11)' .
            '   and `request_work_id` in (select `request_works`.before_work_id as id' .
            '                           from `request_works`' .
            '                                    inner join `approvals` on `approvals`.`request_work_id` = `request_works`.`id`' .
            '                                    inner join `approval_tasks` on `approval_tasks`.`approval_id` = `approvals`.`id`' .
            '                                    inner join `deliveries` on `deliveries`.`approval_task_id` = `approval_tasks`.`id`' .
            '                           where `request_works`.`step_id` = 12)' .
            ' group by `employees_id`, `name`, `spell`' .
            ' order by `expense_carfares`.`spell` asc',
            [$accounting_year_month]
        );
        return $list;
    }

    /**
     * 获取交通费详情
     * @param string $accounting_year_month 会计年月
     * @return array 结果数组
     */
    private function getCarfareDetail($accounting_year_month): array
    {
        $list = DB::select(
            ' select ' .
            "        month(CONCAT(expense_carfares.date,'-01')) as month," .
            '       expense_carfares.employees_id,' .
            '       expense_carfares.name,' .
            '       expense_carfares.spell,' .
            '       expense_carfares.price,' .
            '       expense_carfares.task_id' .
            ' from expense_carfares' .
            ' where `expense_carfares`.`date` = ?' .
            '   and `expense_carfares`.`expenses_type` = 0' .
            '   and `task_id` in (select `approval_tasks`.`task_id`' .
            '                     from `request_works`' .
            '                              inner join `approvals` on `approvals`.`request_work_id` = `request_works`.`id`' .
            '                              inner join `approval_tasks` on `approval_tasks`.`approval_id` = `approvals`.`id`' .
            '                              inner join `deliveries` on `deliveries`.`approval_task_id` = `approval_tasks`.`id`' .
            '                     where `request_works`.`step_id` = 11)' .
            '   and `request_work_id` in (select `request_works`.before_work_id as id' .
            '                           from `request_works`' .
            '                                    inner join `approvals` on `approvals`.`request_work_id` = `request_works`.`id`' .
            '                                    inner join `approval_tasks` on `approval_tasks`.`approval_id` = `approvals`.`id`' .
            '                                    inner join `deliveries` on `deliveries`.`approval_task_id` = `approval_tasks`.`id`' .
            '                           where `request_works`.`step_id` = 12)' .
            ' order by `expense_carfares`.`spell` asc',
            [$accounting_year_month]
        );
        return $list;
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


    /**
     * 合并PDF
     * @param array $files 文件路径数组
     * @param string $downLoad_filePath 合并后的文件名称
     * @return string 合并后的文件路径
     */
    private function mergePdf(array $files, string $downLoad_filePath): string
    {
        $pdf = new Fpdi();

        foreach ($files as $file) {
            // 获取当前文件总共的页数
            $pageCount = $pdf->setSourceFile($file);

            // 遍历所有页
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                // 导入页码生成一个临时页码ID
                $templateId = $pdf->importPage($pageNo);
                // 获取PDF当前页码的大小
                $size = $pdf->getTemplateSize($templateId);

                // 创建页面 (根据页面的大小判断是横向还是纵向生成)
                if ($size['width'] > $size['height']) {
                    $pdf->AddPage('L', array($size['width'], $size['height']));
                } else {
                    $pdf->AddPage('P', array($size['width'], $size['height']));
                }

                // 使用导入的页面
                $pdf->useTemplate($templateId);

                $pdf->SetFont('Helvetica');
                //用来设置签名
//                $pdf->SetXY(5, 5);
//                $pdf->Write(8, 'A simple concatenation demo with FPDI');
            }
        }

        //保存在服务器
        $pdf->Output('F', $downLoad_filePath);

        return $downLoad_filePath;
    }

    //全角转换成半角

    /**
     * 全角を半角にする
     * @param string $instr 変換前文字列
     * @return    string 変換後文字列
     */
    public static function zen2han($instr)
    {
        $flag = false;            //直前文字が全角カタカナならTRUE
        $len = mb_strlen($instr);
        $ofst = 1;
        $outstr = '';

        for ($ofst = 0; $ofst < $len; $ofst++) {
            $ch = mb_substr($instr, $ofst, 1);
            if ($ch == 'ー') {
                if ($flag) {
                    $outstr .= 'ｰ';                    //カタカナの長音記号
                } else {
                    $outstr .= $ch;
                }
            } else if (mb_ereg_match('[ァ-ヶ]+', $ch)) {    //カタカナの範囲
                $outstr .= mb_convert_kana($ch, 'k');
                $flag = true;
            } else if (mb_ereg_match('[０-９]+', $ch)) {    //全角数字の範囲
                $outstr .= mb_convert_kana($ch, 'n');
                $flag = true;
            } else if (mb_ereg_match('[Ａ-Ｚ]+', $ch)) {    //全角英字の範囲
                $outstr .= mb_convert_kana($ch, 'r');
                $flag = true;
            } else if (mb_ereg_match('[ぁ-ん]+', $ch)) {    //ひらがなの範囲
                $flag = true;
                $outstr .= mb_convert_kana($ch, 'h');
            } else {
                $outstr .= $ch;
                $flag = false;
            }
        }
        return $outstr;
    }

    /**
     * 获取承认后的PDF文件集合
     * @param string $date 会计月
     * @return array 一维索引数组：文件S3存储路径
     */
    private function readDeliveriesFile(string $date): array
    {

        $result = DB::select(
            ' select `approval_tasks`.`task_id`, `deliveries`.`content`' .
            ' from request_works ' .
            '          inner join `approvals` on `approvals`.`request_work_id` = `request_works`.`id` ' .
            '          inner join `approval_tasks` on `approval_tasks`.`approval_id` = `approvals`.`id` ' .
            '          inner join `deliveries` on `deliveries`.`approval_task_id` = `approval_tasks`.`id` ' .
            '          inner join ( ' .
            '                       select `request_works`.`id` as id ' .
            '                       from `expense_carfares` ' .
            '                       inner join `request_works` on `request_works`.`id` = `expense_carfares`.`request_work_id` ' .
            '                       inner join `approvals` on `approvals`.`request_work_id` = `request_works`.`id` ' .
            '                       inner join `approval_tasks` on `approval_tasks`.`approval_id` = `approvals`.`id` ' .
            '                                   and `approval_tasks`.`task_id` = `expense_carfares`.`task_id` ' .
            '                       inner join `deliveries` on `deliveries`.`approval_task_id` = `approval_tasks`.`id` ' .
            '                       where `expense_carfares`.`date` = ? ' .
            '                       and `expense_carfares`.`expenses_type` = 0 ' .
            '           ) as before_request_works ' .
            '           on request_works.before_work_id = before_request_works.id ',
            [$date]
        );

        if (!empty($result)) {
            $filelist = json_decode(json_encode($result), true);
            return self::filePathCollection($filelist);
        } else {
            return [];
        }
    }

    /**
     * 获取S12纳品后的文件路径并进行组装
     * @param array $file_list
     * @return array
     */
    private function filePathCollection($file_list): array
    {
        $return_array = [];//初始化返回路径
        foreach ($file_list as $item) {
            $task_id = $item['task_id'];
            $content = json_decode($item['content'], true);
            $task_result_id = DB::table('task_results')->where('task_id', $task_id)
                ->orderBy('updated_at', 'desc')
                ->first()->id;
            $ap_number = isset($content['C00800_3']) ? $content['C00800_3'] : null;
            $station_number = isset($content['C00800_4']) ? $content['C00800_4'] : null;
            if ($ap_number) {
                $query = DB::table('task_result_files')->select('file_path');
                $query->where('task_result_id', $task_result_id);
                $query->whereIn('seq_no', $ap_number);
                $ap_result = $query->get()->toArray();
                foreach ($ap_result as $item) {
                    array_push($return_array, $item->file_path);
                }
            }
            if ($station_number) {
                $query = DB::table('task_result_files')->select('file_path');
                $query->where('task_result_id', $task_result_id);
                $query->whereIn('seq_no', $station_number);
                $station_result = $query->get()->toArray();
                foreach ($station_result as $item) {
                    array_push($return_array, $item->file_path);
                }
            }
        }
        return $return_array;
    }

    /**
     * 合并PDF调用方法
     * @param string $accounting_year_month
     * @return array
     * @throws \Exception
     */
    private function pdfMergeReport(string $accounting_year_month): array
    {
        $accounting_year_month = str_replace('/', '-', $accounting_year_month);
        $files_index_array = self::readDeliveriesFile($accounting_year_month);//一维文件路径数组
        if ($files_index_array) {
            $unlink_files_path = [];
            $unlink_files_name = [];
            $year_month = explode('-', $accounting_year_month);
            $pdf_name = 'keihiMonthlyReport_' . $year_month[0] . $year_month[1] . '.pdf';
            $directory_path = storage_path() . '/app/public/';
            $downLoad_filePath = $directory_path . $pdf_name;
            foreach ($files_index_array as $file_path) {
                list($file_name, $tmp_disk, $tmp_file_name, $tmp_file_path, $mime_type, $file_size) = CommonDownloader::getFileFromS3($file_path);
                array_push($unlink_files_path, $tmp_file_path);
                array_push($unlink_files_name, $tmp_file_name);
            }
            //S3下载成功后的文件路径和名字
            self::mergePdf($unlink_files_path, $downLoad_filePath);//合并从S3下载的PDF
            foreach ($unlink_files_name as $unlink_temp_file_name) {//删除从S3下载的文件
                $tmp_disk->delete($unlink_temp_file_name);
            }
            $file = [
                'name' => $pdf_name,
                'path' => $downLoad_filePath
            ];
            return $file;
        }
        return [];
    }

    /**
     * 交通费明细报表数据取得
     * @param string $accounting_year_month 会计年月
     * @param string $transfer_date 振り込み日
     * @return array data
     * @throws \Exception
     */
    private function getDetailReportData(string $accounting_year_month, string $transfer_date): array
    {
        $accounting_year_month = str_replace('/', '-', $accounting_year_month);
        if ($transfer_date == null) {
            //transfer_date传值为空时
            throw new \Exception("transfer_date is null");
        } else {
            $transfer_date_for02 = date("Y/m/d", strtotime($transfer_date));
            $transfer_date_for22 = date("m/d", strtotime($transfer_date));
        }


        // 対象納品データの集計
        $data = [];
        // 写数据
        $carfare_array = self::getCarfareDetail($accounting_year_month);


        $seqNo = 0;
        foreach ($carfare_array as $carfare) {
            //税抜
            $exclusive_excise = round($carfare->price / self::EXCISE_RATE);
            //消費税
            $excise = $carfare->price - $exclusive_excise;
            $emp_id = substr($carfare->employees_id, -4);
            // 借方
            $seqNo++;
            //             1          2                 3      4      5     6     7     8     9   10  11  12          13          14    15       16   17  18  19   20                   21                   |                                                 22                                                            23    24  25  26  27   28     29    30      31    32  33  34  35  36   37   38   39    40  41  42  43  44  45  46  47  48  49  50  51  52  53
            $tmp_data = ['103', $transfer_date_for02, '210', $seqNo, '0', '7350', '', '9999', '', '', '', '', $exclusive_excise, '135', '2', $excise, '', '', '0', '0', $carfare->month . '月分　交通費　電車代', $transfer_date_for22 . '振込' . ' ' . $carfare->name . ' ' . self::zen2han($carfare->spell) . ' ' . $emp_id, 'AP', 'A0', '', '', '', '3310', '', '9999', '9999', '', '', '', '', '0', '0', '0', '0', '0', '', '', '', '', '', '', '', '', '', '', '', '', ''];
            array_push($data, $tmp_data);
            // 貸方
            $seqNo++;
            //             1          2                 3      4      5     6     7     8     9   10  11  12          13               14    15   16   17  18  19   20                   21                |                                                   22                                                          23    24  25  26  27    28    29   30      31     32  33  34  35  36   37   38   39   40      41    42  43  44  45  46  47  48  49  50  51  52  53
            $tmp_data = ['103', $transfer_date_for02, '210', $seqNo, '1', '3310', '', '9999', '', '', '', '', round($carfare->price), '000', '0', '0', '', '', '0', '0', $carfare->month . '月分　社員立替金', $transfer_date_for22 . '振込' . ' ' . $carfare->name . ' ' . self::zen2han($carfare->spell) . ' ' . $emp_id, 'AP', 'A0', '', '', '', '7350', '', '9999', '9999', '', '', '', '', '0', '0', '0', '0', '3', $emp_id, '', '', '', '', '', '', '', '', '', '', '', ''];
            array_push($data, $tmp_data);
        }
        return $data;
    }
}
