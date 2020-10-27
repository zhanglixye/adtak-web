<?php

namespace App\Services\Traits;

use App\Models\FileImportMainConfig;

/**
 * 依頼ファイル関連用のトレイト
 */
trait RequestFileTrait
{
    public function getFileImportConfigs($step_id)
    {
        $main_config = FileImportMainConfig::with(['fileImportColumnConfigs' => function ($query) {
            $query->orderBy('sort', 'asc');
        }])->where('step_id', $step_id)->first();
        $column_configs = $main_config->fileImportColumnConfigs;

        $configs = [
            'main_config' => $main_config,
            'column_configs' => $column_configs,
        ];

        return $configs;
    }

    // 設定マスタと依頼ファイル内容jsonデータから表示用のデータセットを作成
    public function generateRequestFileItemData($column_configs, $content)
    {
        $column_configs = $column_configs->toArray();
        $content = json_decode($content, true);
        $request_file_item_data = [];

        foreach ($column_configs as $column_config) {
            $target_content_key = $column_config['item_key'];

            if (isset($content[$target_content_key])) {
                $request_file_item_data[] = [
                    'label_id' => $column_config['label_id'],
                    'value' => $content[$target_content_key],
                    'item_type' => $column_config['item_type'],
                ];
            } else {
                $request_file_item_data[] = [
                    'label_id' => $column_config['label_id'],
                ];
            }
        }

        return $request_file_item_data;
    }
}
