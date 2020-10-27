<?php

namespace App\Services\FileManager;

use DB;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CreateOrderFile
{
    /**
     * 案件ファイルを作成する
     *
     * @param array $order_detail_ids
     * @return array file_paths
     */
    public static function create(array $order_detail_ids): array
    {
        $order_ids = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereIn('order_details.id', $order_detail_ids)
            ->groupBy('orders.id')
            ->pluck('orders.id');

        $order_files = [];

        // エクセルファイルに記載する情報を取得
        foreach ($order_ids as $order_id) {
            $rows = [];

            // ヘッダーを除く行の値を取得
            foreach ($order_detail_ids as $order_detail_id) {
                $query = DB::table('order_details')
                    ->join('orders', 'order_details.order_id', '=', 'orders.id')
                    ->join('order_detail_values', 'order_details.id', '=', 'order_detail_values.order_detail_id')
                    ->where('order_details.id', $order_detail_id);

                $row = $query->pluck('order_detail_values.value')->toArray();
                array_push($row, $query->value('order_details.name'));
                array_push($row, $query->value('order_details.is_active') ? __('order/orders.list.status.active') : __('order/orders.list.status.inactive'));

                // 先にvalueで値を取得するとplukで正しく値取得出来なくなるため後判定
                if ($query->value('orders.id') !== $order_id) {
                    continue;
                }
                $rows[] = $row;
            }

            $query = DB::table('orders')
                ->join('orders_order_files', 'orders.id', '=', 'orders_order_files.order_id')
                ->join('order_files_order_file_import_main_configs', 'orders_order_files.order_file_id', '=', 'order_files_order_file_import_main_configs.order_file_id')
                ->join('order_file_import_main_configs', 'order_files_order_file_import_main_configs.order_file_import_main_config_id', '=', 'order_file_import_main_configs.id')
                ->join('order_file_import_column_configs', 'order_file_import_main_configs.id', '=', 'order_file_import_column_configs.order_file_import_main_config_id')
                ->join('labels', 'order_file_import_column_configs.label_id', '=', 'labels.label_id')
                ->join('languages', function ($join) {
                    $join->on('labels.language_id', '=', 'languages.id')
                        ->where('languages.code', 'ja');
                })
                ->where('orders.id', $order_id);

            $header_rows = $query->pluck('labels.name')->toArray();
            $data_formats = $query->pluck('order_file_import_column_configs.item_type')->toArray();

            // ヘッダーで表示する表示名＋案件明細の件名＋ステータスを取得
            $each_column_data_formats = [];
            $column_data_format = 'A';
            foreach ($data_formats as $item) {
                $each_column_data_formats = array_merge($each_column_data_formats, array($column_data_format => $item));
                ++$column_data_format;
            }
            array_push($header_rows, __('order/orders.order_detail_name'));
            $each_column_data_formats = array_merge($each_column_data_formats, array($column_data_format => \Config::get('const.ITEM_TYPE.STRING.ID')));
            ++$column_data_format;
            array_push($header_rows, __('order/orders.status'));
            $each_column_data_formats = array_merge($each_column_data_formats, array($column_data_format => \Config::get('const.ITEM_TYPE.STRING.ID')));

            $order_name = $query->value('orders.name');
            $sheet_name = $query->value('order_file_import_main_configs.sheet_name');

            $data = [
                'order_name' => mb_substr(FileNameManager::removeSpecialCharacterForWindows($order_name), 0, 128),
                'sheet_name' => $sheet_name,
                'header_rows' => $header_rows,
                'rows' => $rows,
                'each_column_data_formats' => $each_column_data_formats
            ];

            $order_files[] = $data;
        }

        // 案件ファイルを作成
        $disk = \Storage::disk('public');
        $file_paths = [];
        $order_names = [];
        // 案件ごとにファイルを作成
        foreach ($order_files as $order_file) {
            $spread_sheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spread_sheet->getActiveSheet();
            $sheet->setTitle($order_file['sheet_name']);

            // ヘッダーの情報をファイルに記載
            $header_row_column = 'A';
            $num_loop = 1;
            foreach ($order_file['header_rows'] as $header_row) {
                $sheet->setCellValue($header_row_column. $num_loop, $header_row);
                $sheet->getCell($header_row_column. $num_loop)
                    ->getStyle()
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
                ++$header_row_column;
            }

            $num_loop += 1;
            foreach ($order_file['rows'] as $rows) {
                $column = 'A';
                foreach ($rows as $row) {
                    $number_format = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT;
                    if ($order_file['each_column_data_formats'][$column] === \Config::get('const.ITEM_TYPE.NUM.ID')) {
                        // 数値として取り込む
                        $number_format = '#,##0_ ';
                    } elseif ($order_file['each_column_data_formats'][$column] === \Config::get('const.ITEM_TYPE.DATE.ID')) {
                        // 日付として取り込む
                        $number_format = \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDDSLASH;
                    }

                    $value = $row !== '' ? $row : null;
                    if (!is_null($value)
                        && mb_strlen($value) > 1
                        && mb_substr($value, 0, 1) === '='
                        ) {// 計算式と解釈しエラーを発生させる問題を回避させる
                        $value = '\'' . $value;
                    }
                    $sheet->setCellValue($column. $num_loop, $value);
                    $sheet->getCell($column. $num_loop)
                        ->getStyle()
                        ->getNumberFormat()
                        ->setFormatCode($number_format);

                    if ($row !== '' && $order_file['each_column_data_formats'][$column] === \Config::get('const.ITEM_TYPE.URL.ID')) {
                        // ハイパーリンクの設定
                        $sheet->getCell($column. $num_loop)->getHyperlink()->setUrl($row);

                        // 文字色：青、下線あり
                        $sheet->getStyle($column. $num_loop)
                            ->getFont()
                            ->setUnderline(true)
                            ->getColor()
                            ->setARGB('000000FF');
                    }

                    // 上寄せに設定（デフォルトは下寄せ）
                    $sheet->getStyle($column. $num_loop)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

                    // 改行がある場合は、「折り返して全体を表示する」を有効化
                    $matches = [];
                    preg_match('/\r\n|\r|\n/u', $value, $matches);
                    if (count($matches) > 0) {
                        $sheet->getStyle($column. $num_loop)->getAlignment()->setWrapText(true);
                    }

                    ++$column;
                }
                $num_loop += 1;
            }

            if (in_array($order_file['order_name'], $order_names)) {
                // ファイル名を設定（同一のファイル名がある場合は末尾に(n)を付ける）
                $new_order_name = $order_file['order_name']. '('. 1 . ')';
                for ($count = 1; in_array($new_order_name, $order_names); $count++) {
                    $new_order_name = $order_file['order_name']. '('. $count . ')';
                }
                $order_file['order_name'] = $new_order_name;
            }
            $order_names[] = $order_file['order_name'];

            $directory_path = 'tmp/order_files';
            // Maximum 55 characters in the file name. {file name}.xlsx = 55
            $file_path = $directory_path . '/' . mb_substr(str_replace('.', '-', strval(microtime(true))) . $order_file['order_name'], 0, 50) . '.xlsx';

            // 指定したフォルダが無い場合は作成
            if (!$disk->exists($directory_path)) {
                $disk->makeDirectory($directory_path);
            }

            // ファイルを保存
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spread_sheet);
            $writer->save($disk->path($file_path));
            $file_paths[] = [
                'local_path' => $file_path,
                'file_name' => $order_file['order_name']. '.xlsx'
            ];
        }

        return $file_paths;
    }
}
