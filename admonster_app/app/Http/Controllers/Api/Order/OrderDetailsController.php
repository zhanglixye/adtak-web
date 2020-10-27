<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Services\DownloadFileManager\Downloader;
use App\Services\UploadFileManager\Uploader;
use Illuminate\Http\JsonResponse;
use App\Models\Label;
use App\Models\Order;
use App\Models\CustomStatus;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BusinessFlow;
use App\Models\OrderFileImportColumnConfig;
use App\Models\OrderDetailValue;
use App\Models\Queue;
use App\Models\Request as RequestModel;
use App\Models\RequestMail;
use App\Models\SendMail;
use App\Models\SendMailAttachment;
use App\Models\RequestMailAttachment;
use App\Models\RequestWork;
use App\Models\Step;
use App\Models\ImportOrderRelatedMailAlias;
use Carbon\Carbon;
use App\Services\FileManager\CreateOrderFile;
use App\Services\Traits\RequestLogStoreTrait;
use App\Exceptions\ExclusiveException;
use App\Http\Controllers\Api\UtilitiesController as Utilities;
use DB;

class OrderDetailsController extends Controller
{
    use RequestLogStoreTrait;

    /**
     * 案件明細一覧のデータ取得
     * @param Request $req
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $req): JsonResponse
    {
        // 検索条件の取得
        $form = [
            // 必須の項目
            'order_id' => $req->input('order_id'),

            // 検索欄の項目
            'order_detail_name' => $req->input('order_detail_name'),
            'from' => $req->input('from'),
            'to' => $req->input('to'),
            'selected_custom_status_id' => $req->input('selected_custom_status_id'),
            'selected_attribute_id' => $req->input('selected_attribute_id'),
            'status' => $req->input('status'),
            'display_column' => $req->get('display_column'),
            'display_id' => $req->get('display_id'),
            'display_item_type' => $req->get('display_item_type'),
            'display_text' => $req->get('display_text'),
            'display_from' => $req->get('display_from'),
            'display_to' => $req->get('display_to'),

            // 一覧表示に関する項目
            'page' => $req->input('page'),
            'sort_by' => $req->get('sort_by'),
            'descending' => $req->get('descending') ? 'desc' : 'asc',
            'rows_per_page' => $req->get('rows_per_page'),
        ];

        /** @var Order */
        $order = Order::where('is_deleted', \Config::get('const.FLG.INACTIVE'))->findOrFail($form['order_id']);

        $user = \Auth::user();
        $order_details = $order->orderDetails();// joinの計算量を減らすためのSQL
        $base_table = 'order_details';
        $query = DB::table(DB::raw('('.$order_details->toSql().') AS '. $base_table))// from
        ->select(
            $base_table.'.id AS order_detail_id',
            $base_table.'.name AS order_detail_name',
            $base_table.'.is_active AS status',
            $base_table.'.is_deleted AS is_deleted',
            $base_table.'.created_at AS created_at',
            $base_table.'.created_user_id AS created_user_id',
            $base_table.'.updated_at AS updated_at',
            $base_table.'.updated_user_id AS updated_user_id'
        )
        ->setBindings($order_details->getBindings())
        ->leftJoin('orders_administrators', function ($join) use ($base_table, $user) {
            $join->on($base_table.'.order_id', '=', 'orders_administrators.order_id')
                ->whereRaw('orders_administrators.user_id = '. $user->id);
        })
        ->leftJoin('orders_sharers', function ($join) use ($base_table, $user) {
            $join->on($base_table.'.order_id', '=', 'orders_sharers.order_id')
                ->whereRaw('orders_sharers.user_id = '. $user->id);
        });

        // ソートする際のコンバート対象を管理する連想配列を作成
        $each_column_sort = [];// 各カラムのソート基準
        $convert_manage = [];// key:const.ITEM_TYPE.*.ID value:const.SORT_TYPE.*
        foreach (\Config::get('const.ITEM_TYPE') as $key => $value) {
            if ($value['RULE'] === 'numeric') {
                $convert_manage[$value['ID']] = \Config::get('const.SORT_TYPE.SIGNED');
            } elseif ($value['RULE'] === 'date') {
                $convert_manage[$value['ID']] = \Config::get('const.SORT_TYPE.DATE');
            } else {
                $convert_manage[$value['ID']] = '';
            }
        }

        /** @var array 画面に表示するラベルIDの配列 */
        $label_ids = [];

        // ---- 各order_file_import_column_configsの値で、項目別のテーブルを作成する。
        // 案件関連するcolumn_configsを取得
        $order_file = $order
            ->orderFiles()
            ->wherePivot('import_type', \Config::get('const.FILE_IMPORT_TYPE.NEW'))
            ->first();

        if (is_null($order_file)) {
            throw new \Exception('案件ファイルがありません');
        }

        $order_file_import_main_config = $order_file->orderFileImportMainConfigs->first();
        if (is_null($order_file_import_main_config)) {
            throw new \Exception('案件ファイルメイン設定がありません');
        }

        $column_configs = $order_file_import_main_config->orderFileImportColumnConfigs;
        if (count($column_configs) === 0) {
            throw new \Exception('案件ファイル列設定がありません');
        }

        $label_ids = array_merge($label_ids, $column_configs->pluck('label_id')->toArray());// ラベルIDの追加

        $each_column_sql = [];
        $prefix = (new OrderFileImportColumnConfig())->getTable().'_';
        foreach ($column_configs as $key => $column_config) {
            $sql = OrderDetailValue::select('order_detail_id', 'value')->whereIn('id', $column_config->orderDetailValues->pluck('id'));
            $each_column_sql[$prefix.$column_config->id] = $sql;
        }

        // select追加
        foreach (array_keys($each_column_sql) as $value) {
            $query->addSelect($value.'.value AS '. $value);
        }

        // join
        foreach ($each_column_sql as $key => $sql) {
            $query->leftJoinSub($sql->toSql(), $key, function ($join) use ($key, $base_table) {
                $join->on($key.'.order_detail_id', '=', $base_table.'.id');
            })
            ->mergeBindings($sql->getQuery());
        }

        // 表頭データの取得
        $item_column_configs = $order
            ->orderFiles()
            ->wherePivot('import_type', \Config::get('const.FILE_IMPORT_TYPE.NEW'))
            ->first()
            ->orderFileImportMainConfigs
            ->first()
            ->orderFileImportColumnConfigs()
            ->select('id', 'item_type', 'label_id', 'display_format')
            ->get();
        foreach ($item_column_configs as $value) {
            $value->column = $prefix . $value->id;
            $value->display_formats = OrderFileImportColumnConfig::getTargetDisplayFormat($value->display_format, $value->item_type);
            $each_column_sort[$value->column] = $convert_manage[$value->item_type];
        }
        // !--- 各order_file_import_column_configsの値で、項目別のテーブルを作成する。

        $register_custom_status_columns = [];
        // カスタムステータス
        $custom_statuses = DB::table('custom_statuses')
            ->select(
                'id',
                'label_id'
            )
            ->where('order_id', $form['order_id'])
            ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))
            ->get();

        // ラベルデータ追加
        $label_ids = array_merge($label_ids, $custom_statuses->pluck('label_id')->toArray());
        $selected_custom_status_prefix = 'selected_custom_status_';

        // カスタムステータス属性
        foreach ($custom_statuses as $custom_status) {
            // カスタムステータスに紐づく属性を取得
            $custom_status->attributes = DB::table('custom_status_attributes')
                ->select(
                    'id',
                    'label_id'
                )
                ->where('custom_status_id', $custom_status->id)
                ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))
                ->get();

            $order_detail_ids = CustomStatus::where('is_deleted', \Config::get('const.FLG.INACTIVE'))
                ->find($custom_status->id)
                ->orderDetails
                ->pluck('id');

            $label_column = (new CustomStatus())->getTable().'_'. $custom_status->id;
            array_push($register_custom_status_columns, $label_column);

            // select追加
            $query->addSelect($selected_custom_status_prefix. $custom_status->id.'.label_id AS '. $label_column);
            $query->addSelect($selected_custom_status_prefix. $custom_status->id.'.custom_status_attribute_sort AS custom_status_attribute_sort_'. $custom_status->id);

            $sql = OrderDetail::select(
                'order_details_related_custom_status_attributes.order_detail_id AS order_detail_id',
                'custom_status_attributes.label_id AS label_id',
                'custom_status_attributes.sort AS custom_status_attribute_sort',
                'custom_status_attributes.id AS custom_status_attribute_id'
            )
            ->join('order_details_related_custom_status_attributes', 'order_details.id', '=', 'order_details_related_custom_status_attributes.order_detail_id')
            ->join('custom_status_attributes', 'order_details_related_custom_status_attributes.custom_status_attribute_id', '=', 'custom_status_attributes.id')
            ->whereIn('order_details.id', $order_detail_ids)
            ->where('order_details_related_custom_status_attributes.custom_status_id', $custom_status->id);

            // join
            $query->leftJoinSub($sql->toSql(), $selected_custom_status_prefix. $custom_status->id, function ($join) use ($base_table, $custom_status, $selected_custom_status_prefix) {
                $join->on($selected_custom_status_prefix. $custom_status->id.'.order_detail_id', '=', $base_table.'.id');
            })
            ->mergeBindings($sql->getQuery());

            // ラベルデータ追加
            $label_ids = array_merge($label_ids, $custom_status->attributes->pluck('label_id')->toArray());
        }

        $query = $query->where(function ($query) use ($user) {
            $query->where('orders_administrators.user_id', $user->id)
                ->orWhere('orders_sharers.user_id', $user->id);
        });

        // where

        // 件名
        if ($form['order_detail_name']) {
            $query = $query->where($base_table.'.name', 'LIKE', '%'.$form['order_detail_name'].'%');
        }

        // 発生日
        if ($form['from'] && $form['to']) {
            $query = $query->whereBetween($base_table . '.created_at', [$form['from'], $form['to']]);
        } elseif ($form['from'] && !$form['to']) {
            $query = $query->where($base_table . '.created_at', '>=', $form['from']);
        } elseif (!$form['from'] && $form['to']) {
            $query = $query->where($base_table . '.created_at', '<=', $form['to']);
        }

        // ステータス
        if (is_numeric($form['status'])) {
            if ($form['status'] === \Config::get('const.FLG.ACTIVE')) {
                $query->where($base_table . '.is_active', '=', \Config::get('const.FLG.ACTIVE'));
            } elseif ($form['status'] === \Config::get('const.FLG.INACTIVE')) {
                $query->where($base_table . '.is_active', '=', \Config::get('const.FLG.INACTIVE'));
            }
        }

        // 表示データ
        if ($form['display_column'] && is_numeric($form['display_id'])) {
            $item_type = OrderFileImportColumnConfig::findOrFail($form['display_id'])->item_type;

            $column = $form['display_column'].'.value';

            $item_types = \Config::get('const.ITEM_TYPE');
            $result = null;
            foreach ($item_types as $value) {
                if ($item_type === $value['ID']) {
                    $result = $value['RULE'];
                }
            }

            // 文字列
            if ($result === 'string' && is_string($form['display_text'])) {
                $query = $query->where($column, 'LIKE', '%'.$form['display_text'].'%');
                // order_file_import_column_configs_30
            }

            // 数値
            if ($result === 'numeric' && is_string($form['display_text'])) {
                //全角数値の場合は半角数値へ変換
                $query = $query->where($column, 'LIKE', '%' . mb_convert_kana($form['display_text'] . '%'));
            }

            // 日付
            if ($result === 'date') {
                if ($form['display_from'] && $form['display_to']) {
                    $query = $query->whereRaw(
                        'CONVERT(`' . $form['display_column'] . '`.`value`, DATE) between CONVERT(?, DATE) and CONVERT(?, DATE)',
                        [ $form['display_from'],  $form['display_to']]
                    );
                } elseif ($form['display_from'] && !$form['display_to']) {
                    $query = $query->whereRaw(
                        'CONVERT(`' . $form['display_column'] . '`.`value`, DATE) >= CONVERT(?, DATE)',
                        [$form['display_from']]
                    );
                } elseif (!$form['display_from'] && $form['display_to']) {
                    $query = $query->whereRaw(
                        'CONVERT(`' . $form['display_column'] . '`.`value`, DATE) <= CONVERT(?, DATE)',
                        [$form['display_to']]
                    );
                }
            }
        }

        // カスタムステータス
        if ($form['selected_custom_status_id']) {
            $selected_custom_status = DB::table('custom_statuses')
                ->where('id', $form['selected_custom_status_id'])
                ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))
                ->first();

            if (!is_null($form['selected_attribute_id'])) {// カスタムステータス属性が選択されている
                if (!is_null($selected_custom_status)) {
                    $selected_attribute = DB::table('custom_status_attributes')
                        ->where('id', $form['selected_attribute_id'])
                        ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))
                        ->first();

                    if (!is_null($selected_attribute)) {// 選択されているカスタムステータス属性が削除されていない
                        $query = $query->where($selected_custom_status_prefix. $form['selected_custom_status_id'].'.custom_status_attribute_id', $form['selected_attribute_id']);
                    } else {// 選択されているカスタムステータス属性が削除
                        $query = $query->where($selected_custom_status_prefix. $form['selected_custom_status_id'].'.custom_status_attribute_id', null);
                    }
                }
            } else {// カスタムステータス属性が未選択
                if (!is_null($selected_custom_status)) {
                    $query = $query->where($selected_custom_status_prefix. $form['selected_custom_status_id'].'.custom_status_attribute_id', null);
                }
            }
        }

        // 除外対象
        $query = $query->where($base_table . '.is_deleted', \Config::get('const.FLG.INACTIVE'));

        $all_order_detail_ids = $query->pluck('order_detail_id');// 検索結果の全案件明細ID

        // sort
        if ($form['sort_by']) {
            $prefix = (new CustomStatus())->getTable().'_';
            if (array_key_exists($form['sort_by'], $each_column_sort) &&  $each_column_sort[$form['sort_by']] !== '') {
                $query = $query->orderBy(DB::raw('CONVERT(`' . $form['sort_by'] . '`, ' . $each_column_sort[$form['sort_by']] . ')'), $form['descending']);
            } elseif (preg_match('/^'.$prefix.'[0-9]*$/', $form['sort_by'])) {
                if (in_array($form['sort_by'], $register_custom_status_columns)) {
                    $query = $query->orderBy('custom_status_attribute_sort_'. str_replace($prefix, '', $form['sort_by']), $form['descending']);
                }
            } else {
                $query = $query->orderBy($form['sort_by'], $form['descending']);
            }
        }

        $query = $query->orderBy('order_detail_id', 'asc');

        $list = $query->paginate($form['rows_per_page'], ['*'], 'page', (int) $form['page']);

        // ラベル一覧を取得
        $label_data = Label::getLangKeySetByIds($label_ids);

        $is_admin = DB::table('orders_administrators')
            ->where('order_id', $form['order_id'])
            ->where('user_id', $user->id)
            ->count() !== 0;

        return response()->json([
            'list' => $list,             // 案件明細一覧
            'item_column_configs' => $item_column_configs,// 案件固有の情報
            'label_data' => $label_data,// ラベルデータ
            'all_order_detail_ids' => $all_order_detail_ids, // 検索結果の全案件明細ID
            'order_name' => $order->name,//案件名
            'custom_statuses' => $custom_statuses,// カスタムステータス
            'is_admin' => $is_admin,
            'started_at' => Carbon::now(),
        ]);
    }

    /**
     * 案件明細の一括更新
     * @param Request $req
     * @return JsonResponse
     */
    public function bulkUpdate(Request $req): JsonResponse
    {
        $user = \Auth::user();

        // 検索条件の取得
        $form = [
            // 検索欄の項目
            'order_detail_ids' => $req->input('order_detail_ids'),
            'order_id' => $req->input('order_id'),
            'is_active' => $req->input('is_active'),
            'started_at' => $req->input('started_at'),
        ];

        DB::beginTransaction();
        try {
            // 排他制御--------------------------------------
            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $form['order_id'])
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }

            $can_update = DB::table('order_details')
                ->whereIn('id', $form['order_detail_ids'])
                ->where('updated_at', '>=', $form['started_at'])
                ->count() === 0;

            if (!$can_update) {
                throw new ExclusiveException("The order details data was updated by another user");
            }
            // 排他制御--------------------------------------

            OrderDetail::whereIn('id', $form['order_detail_ids'])
                ->update([
                    'is_active' => $form['is_active'],
                    'updated_user_id' => $user->id
                ]);

            DB::commit();

            return response()->json([
                'status' => 200,
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);
            if ($e->getMessage() === 'no admin permission') {
                return response()->json([
                    'status' => $e->getCode(),
                    'error' => get_class($e),
                    'message' => 'no_admin_permission',
                ]);
            } else {
                return response()->json([
                    'status' => $e->getCode(),
                    'error' => get_class($e),
                    'message' => 'updated_by_others',
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 案件のDL
     * @param Request $req
     * @return JsonResponse
     */
    public function createOrderFile(Request $req): JsonResponse
    {
        $user = \Auth::user();

        /** @var array */
        $order_detail_ids = $req->input('order_detail_ids') ?? [];
        $order_id = $req->input('order_id') ?? [];

        try {
            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            $is_sharer = DB::table('orders_sharers')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin && !$is_sharer) {
                throw new ExclusiveException("no permission");
            }

            /** @var array */
            $file_paths = CreateOrderFile::create($order_detail_ids);

            return response()->json(
                [
                    'status' => 200,
                    'file_path' => $file_paths[0]['local_path'],
                    'file_name' => $file_paths[0]['file_name']
                ]
            );
        } catch (ExclusiveException $e) {
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'no_permission',
            ]);
        }
    }

    /**
     * 案件明細詳細画面初期表示
     * @param Request $req
     * @return JsonResponse
     */
    public function show(Request $req): JsonResponse
    {
        $form = [
            // 作成する場合order_detail_idには0が入る
            'order_detail_id' => $req->input('order_detail_id'),
            // 既存のデータを編集する場合order_idには0が入る
            'order_id' => $req->input('order_id'),
        ];

        try {
            $query = DB::table('orders')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->join('orders_order_files', function ($join) {
                    $join->on('orders.id', '=', 'orders_order_files.order_id')
                        ->where('orders_order_files.import_type', '=', \Config::get('const.FILE_IMPORT_TYPE.NEW'));
                })
                ->join('order_files_order_file_import_main_configs', 'orders_order_files.order_file_id', '=', 'order_files_order_file_import_main_configs.order_file_id')
                ->join('order_file_import_column_configs', 'order_files_order_file_import_main_configs.order_file_import_main_config_id', '=', 'order_file_import_column_configs.order_file_import_main_config_id');

            if ($form['order_id']) {
                $order_detail_id = Order::find($form['order_id'])->orderDetails->first()->id;
                $query = $query->where('order_details.id', $order_detail_id);
            } else {
                $query = $query->where('order_details.id', $form['order_detail_id']);
            }

            $order_file_import_column_config_ids = $query->pluck('order_file_import_column_configs.id');
            $order_detail_data = $query->select(
                'orders.id AS order_id',
                'order_details.id AS order_detail_id',
                'order_details.name AS name',
                'orders.is_active AS order_is_active',
                'order_details.is_active AS order_detail_is_active'
            )->first();

            $columns = [];
            $label_ids = [];

            // 列ごとの値を取得
            foreach ($order_file_import_column_config_ids as $order_file_import_column_config_id) {
                $column_query = DB::table('order_file_import_column_configs')->select(
                    'order_detail_values.value AS value',
                    'order_file_import_column_configs.label_id AS label_id',
                    'order_file_import_column_configs.item_type AS item_type'
                )
                ->join('order_file_import_column_configs_order_detail_values', 'order_file_import_column_configs.id', '=', 'order_file_import_column_configs_order_detail_values.order_file_import_column_config_id')
                ->join('order_detail_values', 'order_file_import_column_configs_order_detail_values.order_detail_value_id', '=', 'order_detail_values.id')
                ->where('order_file_import_column_configs.id', $order_file_import_column_config_id);

                if ($form['order_id']) {
                    $column = $column_query->first();
                    $column->value = '';
                } else {
                    $column = $column_query
                        ->where('order_detail_values.order_detail_id', $form['order_detail_id'])
                        ->first();
                }

                $label_ids[] = $column->label_id;

                $columns[$order_file_import_column_config_id] = $column;
            }
            $order_detail_data->columns = $columns;

            $user = \Auth::user();

            // order_idを取得(カスタムステータス取得に使用)
            $order_id = null;
            $is_admin = null;
            if ($form['order_detail_id']) {
                $order_id = DB::table('order_details')
                    ->where('id', $form['order_detail_id'])
                    ->value('order_id');

                $is_admin = DB::table('orders_administrators')
                    ->where('order_id', $order_id)
                    ->where('user_id', $user->id)
                    ->count() !== 0;

                $is_sharer = DB::table('orders_sharers')
                    ->where('order_id', $order_id)
                    ->where('user_id', $user->id)
                    ->count() !== 0;

                if (!$is_admin && !$is_sharer) {
                    throw new ExclusiveException("no permission");
                }
            } else {
                $order_id = $form['order_id'];

                $is_admin = DB::table('orders_administrators')
                    ->where('order_id', $order_id)
                    ->where('user_id', $user->id)
                    ->count() !== 0;

                if (!$is_admin) {
                    throw new ExclusiveException("no admin permission");
                }
            }

            // カスタムステータス
            $custom_statuses = DB::table('custom_statuses')
                ->select(
                    'id AS custom_status_id',
                    'label_id AS label_id'
                )
                ->where('order_id', $order_id)
                ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))
                ->orderBy('sort')
                ->get();

            // ラベルデータ追加
            $label_ids = array_merge($label_ids, $custom_statuses->pluck('label_id')->toArray());

            // カスタムステータス属性
            foreach ($custom_statuses as $custom_status) {
                // int|null
                $custom_status->select_attribute_id = DB::table('order_details_related_custom_status_attributes')
                    ->where('order_detail_id', $form['order_detail_id'])
                    ->where('custom_status_id', $custom_status->custom_status_id)
                    ->value('custom_status_attribute_id');

                // カスタムステータスに紐づく属性を取得
                $custom_status->attributes = DB::table('custom_status_attributes')
                    ->select(
                        'id AS value',
                        'label_id AS label_id'
                    )
                    ->where('custom_status_id', $custom_status->custom_status_id)
                    ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))
                    ->orderBy('sort')
                    ->get();

                // ラベルデータ追加
                $label_ids = array_merge($label_ids, $custom_status->attributes->pluck('label_id')->toArray());
            }

            // ラベル一覧を取得
            $label_data = Label::getLangKeySetByIds($label_ids);

            // 全ユーザ情報を保持
            $candidates = User::select(
                'users.id',
                'users.name',
                'users.user_image_path'
            )->get();

            $subject = $form['order_detail_id'] === 0 ? '' : $order_detail_data->name;

            return response()->json([
                'status' => 200,
                'order_detail_data' => $order_detail_data,
                'custom_statuses' => $custom_statuses,
                'label_data' => $label_data,// ラベルデータ,
                'candidates' => $candidates,
                'subject' => $subject,
                'started_at' => Carbon::now(),
                'is_admin' => $is_admin
            ]);
        } catch (ExclusiveException $e) {
            report($e);
            if ($e->getMessage() === 'no admin permission') {
                return response()->json([
                    'status' => $e->getCode(),
                    'error' => get_class($e),
                    'message' => 'no_admin_permission',
                ]);
            } else {
                return response()->json([
                    'status' => $e->getCode(),
                    'error' => get_class($e),
                    'message' => 'no_permission',
                ]);
            }
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 案件詳細を追加または編集する
     * @param Request $req
     * @return JsonResponse
     */
    public function save(Request $req): JsonResponse
    {
        $form = [
            'order_detail_id' => $req->input('order_detail_id'),
            'order_id' => $req->input('order_id'),
            'subject_data' => $req->input('subject_data'),
            'started_at' => new Carbon($req->input('started_at')),
            'order_details_data' => $req->input('order_details_data') ? json_decode($req->input('order_details_data'), true) : [],
            'custom_statuses' => $req->input('custom_statuses') ? json_decode($req->input('custom_statuses'), true) : []
        ];
        $register_time = Carbon::now();
        $user = \Auth::user();
        \DB::beginTransaction();

        try {
            /** @var int|null */
            $order_detail_id = null;
            if ($form['order_detail_id'] === 0) {
                $is_admin = DB::table('orders_administrators')
                    ->where('order_id', $form['order_id'])
                    ->where('user_id', $user->id)
                    ->count() !== 0;

                if (!$is_admin) {
                    throw new ExclusiveException("no admin permission");
                }

                // 新規登録
                // OrderDetail
                $order_detail = new OrderDetail;
                $order_detail->order_id = $form['order_id'];
                $order_detail->name = $form['subject_data'];
                $order_detail->created_at = $register_time;
                $order_detail->created_user_id = $user->id;
                $order_detail->updated_at = $register_time;
                $order_detail->updated_user_id = $user->id;
                $order_detail->save();

                $order_detail_id = $order_detail->id;
                foreach ($form['order_details_data']['columns'] as $key => $column) {
                    // OrderDetailValue
                    $order_detail_value = new OrderDetailValue;
                    $order_detail_value->order_detail_id = $order_detail_id;
                    $order_detail_value->value = $column['value'];
                    $order_detail_value->created_at = $register_time;
                    $order_detail_value->created_user_id = $user->id;
                    $order_detail_value->updated_at = $register_time;
                    $order_detail_value->updated_user_id = $user->id;
                    $order_detail_value->save();

                    // OrderFileImportColumnConfigsOrderDetailValues
                    OrderFileImportColumnConfig::find($key)
                    ->orderDetailValues()
                    ->attach($order_detail_value->id, [
                        'created_at' => $register_time,
                        'created_user_id' => $user->id,
                        'updated_at' => $register_time,
                        'updated_user_id' => $user->id
                    ]);
                }
            } else {
                // 排他制御--------------------------------------
                $can_update = OrderDetail::where('id', $form['order_detail_id'])->where('updated_at', '>=', $form['started_at'])->count() === 0;
                if (!$can_update) {
                    throw new ExclusiveException("The data was updated by another user");
                }

                $order_id = DB::table('order_details')
                    ->where('id', $form['order_detail_id'])
                    ->value('order_id');

                $is_admin = DB::table('orders_administrators')
                    ->where('order_id', $order_id)
                    ->where('user_id', $user->id)
                    ->count() !== 0;

                if (!$is_admin) {
                    throw new ExclusiveException("no admin permission");
                }
                // 排他制御--------------------------------------

                // 編集
                OrderDetail::where('id', $form['order_detail_id'])
                    ->update(
                        [
                            'name' => $form['subject_data'],
                            'updated_at' => $register_time,
                            'updated_user_id' => $user->id
                        ]
                    );
                foreach ($form['order_details_data']['columns'] as $key => $column) {
                    // OrderDetailValue
                    \DB::table('order_detail_values')
                        ->join('order_file_import_column_configs_order_detail_values', 'order_detail_values.id', '=', 'order_file_import_column_configs_order_detail_values.order_detail_value_id')
                        ->where('order_file_import_column_configs_order_detail_values.order_file_import_column_config_id', $key)
                        ->where('order_detail_values.order_detail_id', $form['order_detail_id'])
                        ->update(
                            [
                                'value' => $column['value'],
                                'order_detail_values.updated_at' => $register_time,
                                'order_detail_values.updated_user_id' => $user->id
                            ]
                        );
                }
                $order_detail_id = $form['order_detail_id'];
            }

            // 排他制御--------------------------------------
            $custom_status_ids = array_filter(array_column($form['custom_statuses'], 'customStatusId'));
            $can_update = DB::table('custom_statuses')
                ->whereIN('id', $custom_status_ids)
                ->where('updated_at', '>=', $form['started_at'])
                ->count() === 0;
            if (!$can_update) {
                throw new ExclusiveException("The custom status data was updated by another user");
            }
            // 排他制御--------------------------------------

            foreach ($form['custom_statuses'] as $custom_status) {
                if (!is_null($custom_status['selectAttributeId'])) {
                    $order_details_related_custom_status_attribute = DB::table('order_details_related_custom_status_attributes')
                        ->where('order_detail_id', $order_detail_id)
                        ->where('custom_status_id', $custom_status['customStatusId'])
                        ->first();

                    // 追加・編集
                    OrderDetail::find($order_detail_id)
                        ->customStatuses()
                        ->sync([$custom_status['customStatusId'] => [
                            'custom_status_attribute_id' => $custom_status['selectAttributeId'],
                            'created_at' => is_null($order_details_related_custom_status_attribute) ? $register_time : $order_details_related_custom_status_attribute->created_at,
                            'created_user_id' => is_null($order_details_related_custom_status_attribute) ? $user->id : $order_details_related_custom_status_attribute->created_user_id,
                            'updated_at' => $register_time,
                            'updated_user_id' => $user->id
                        ]], false);// 第一引数で渡した配列以外の中間テーブルのレコードの削除を防ぐため、第二引数にfalseを設定
                } else {
                    // 削除
                    OrderDetail::find($order_detail_id)
                        ->customStatuses()
                        ->detach($custom_status['customStatusId']);
                }
            }

            \DB::commit();

            // 排他制御エラーの回避
            $started_at = clone $register_time;
            $started_at->addSeconds(1);

            return response()->json([
                'status' => 200,
                'started_at' => $started_at,
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);
            if ($e->getMessage() === 'no admin permission') {
                return response()->json([
                    'status' => $e->getCode(),
                    'error' => get_class($e),
                    'message' => 'no_admin_permission',
                ]);
            } else {
                return response()->json([
                    'status' => $e->getCode(),
                    'error' => get_class($e),
                    'message' => 'updated_by_others',
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'internal_error',
            ]);
        }
    }

    /**
     * 案件明細から依頼を作成する
     *
     * @param Request $req
     * @return JsonResponse
     */
    public function createRequests(Request $req): JsonResponse
    {
        $user = \Auth::user();
        $datetime_now = Carbon::now();

        $order_detail_id = $req->get('order_detail_id');
        $business_id = $req->get('business_id');
        $step_id = BusinessFlow::where('business_id', $business_id)->orderBy('seq_no', 'asc')->value('step_id');

        $system_deadline = Step::calculateSystemDeadline($datetime_now->copy(), $step_id);
        $mail_subject = $req->get('mail_subject');
        $mail_body = $req->get('mail_body');
        $mail_attachments = $req->input('mail_attachments') ? json_decode($req->input('mail_attachments'), true) : [];

        \DB::beginTransaction();
        try {
            $order_id = DB::table('order_details')
                ->where('id', $order_detail_id)
                ->value('order_id');

            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }

            $request = new RequestModel;
            $request->name = $mail_subject;
            $request->business_id = $business_id;
            $request->client_name = $user->name;
            $request->deadline = $system_deadline;
            $request->system_deadline = $system_deadline;
            $request->created_at = $datetime_now;
            $request->created_user_id = $user->id;
            $request->updated_at = $datetime_now;
            $request->updated_user_id = $user->id;
            $request->save();

            $order_detail = OrderDetail::find($order_detail_id);
            $order_detail->requests()->attach($request->id, [
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id
            ]);

            $request_work = new RequestWork;
            $request_work->name = $mail_subject;
            $request_work->request_id = $request->id;
            $request_work->step_id = $step_id;
            $request_work->client_name = $user->name;
            $request_work->deadline = $system_deadline;
            $request_work->system_deadline = $system_deadline;
            $request_work->created_at = $datetime_now;
            $request_work->created_user_id = $user->id;
            $request_work->updated_at = $datetime_now;
            $request_work->updated_user_id = $user->id;
            $request_work->save();

            // NOTE: 依頼作成も一緒に行ってしまうので、依頼作成ステータス「完了」で登録する
            $request_mail = new RequestMail;
            $request_mail->create_status = 1;
            $request_mail->from = $user->name;
            // $request_mail->to = $mail_to;
            // $request_mail->cc = $mail_cc;
            $request_mail->subject =  $mail_subject;
            $request_mail->content_type = \Config::get('const.CONTENT_TYPE.HTML');
            $request_mail->body = $mail_body;
            $request_mail->recieved_at = $datetime_now;
            $request_mail->created_at = $datetime_now;
            $request_mail->created_user_id = $user->id;
            $request_mail->updated_at = $datetime_now;
            $request_mail->updated_user_id = $user->id;
            $request_mail->save();

            $request_work->requestMails()->attach($request_mail->id, [
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id
            ]);

            if (is_array($mail_attachments)) {
                foreach ($mail_attachments as $attachment) {
                    if ($attachment['file_path'] == null) {
                        // ファイルのアップロード
                        $upload_data = array(
                            'file' => $attachment
                        );
                        $file_path = Utilities::uploadFile($upload_data['file'], 'base64');
                        $attachment['file_path'] = $file_path;
                    }
                    $request_mail_attachment = new RequestMailAttachment;
                    $request_mail_attachment->request_mail_id = $request_mail->id;
                    $request_mail_attachment->name = $attachment['file_name'];
                    $request_mail_attachment->file_path = $attachment['file_path'];
                    $request_mail_attachment->created_at = $datetime_now;
                    $request_mail_attachment->created_user_id = $user->id;
                    $request_mail_attachment->updated_at = $datetime_now;
                    $request_mail_attachment->updated_user_id = $user->id;
                    $request_mail_attachment->save();
                }
            }

            $request_log_attributes = [
                'request_id' => $request->id,
                'type' => \Config::get('const.REQUEST_LOG_TYPE.IMPORT_COMPLETED'),
                'request_work_id' => $request_work->id,
                'created_user_id' => $user->id,
                'updated_user_id' => $user->id,
            ];
            $this->storeRequestLog($request_log_attributes);

            $queue = new Queue;
            $queue->process_type = config('const.QUEUE_TYPE.ALLOCATE');
            $queue->argument = json_encode(['request_work_id' => $request_work->id, 'operators' => []]);
            $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
            $queue->created_at = $datetime_now;
            $queue->created_user_id = $user->id;
            $queue->updated_at = $datetime_now;
            $queue->updated_user_id = $user->id;
            $queue->save();

            $order_id = DB::table('order_details')
                ->where('id', $order_detail_id)
                ->value('order_id');

            \DB::table('order_related_mails')->insert([
                'order_id' => $order_id,
                'order_detail_id' => $order_detail_id,
                'request_mail_id' => $request_mail->id,
                'send_mail_id' => null,
                'is_open_to_client' => false,
                'created_at' => $datetime_now,
                'created_user_id' => $user->id,
                'updated_at' => $datetime_now,
                'updated_user_id' => $user->id
            ]);

            \DB::commit();
            return response()->json([
                'status' => 200
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'no_admin_permission',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 案件一覧から依頼を一括作成する
     *
     * @param Request $req
     * @return JsonResponse
     */
    public function bulkCreateRequests(Request $req): JsonResponse
    {
        $user = \Auth::user();
        $datetime_now = Carbon::now();

        $order_id = $req->get('order_id');
        $order_details = $req->input('order_details') ? json_decode($req->input('order_details'), true) : [];
        $business_id = $req->get('business_id');
        $step_id = BusinessFlow::where('business_id', $business_id)->orderBy('seq_no', 'asc')->value('step_id');

        $system_deadline = Step::calculateSystemDeadline($datetime_now->copy(), $step_id);

        \DB::beginTransaction();
        try {
            $is_admin = DB::table('orders_administrators')
                    ->where('order_id', $order_id)
                    ->where('user_id', $user->id)
                    ->count() !== 0;
            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }

            foreach ($order_details as $order_detail) {
                $request = new RequestModel;
                $request->name = $order_detail['subject'];
                $request->business_id = $business_id;
                $request->client_name = $user->name;
                $request->deadline = $system_deadline;
                $request->system_deadline = $system_deadline;
                $request->created_at = $datetime_now;
                $request->created_user_id = $user->id;
                $request->updated_at = $datetime_now;
                $request->updated_user_id = $user->id;
                $request->save();

                $order_detail_model = OrderDetail::find($order_detail['id']);
                $order_detail_model->requests()->attach($request->id, [
                    'created_user_id' => $user->id,
                    'updated_user_id' => $user->id
                ]);

                $request_work = new RequestWork;
                $request_work->name = $order_detail['subject'];
                $request_work->request_id = $request->id;
                $request_work->step_id = $step_id;
                $request_work->client_name = $user->name;
                $request_work->deadline = $system_deadline;
                $request_work->system_deadline = $system_deadline;
                $request_work->created_at = $datetime_now;
                $request_work->created_user_id = $user->id;
                $request_work->updated_at = $datetime_now;
                $request_work->updated_user_id = $user->id;
                $request_work->save();

                // NOTE: 依頼作成も一緒に行ってしまうので、依頼作成ステータス「完了」で登録する
                $request_mail = new RequestMail;
                $request_mail->create_status = 1;
                $request_mail->from = $user->name;
                // $request_mail->to = $mail_to;
                // $request_mail->cc = $mail_cc;
                $request_mail->subject = $order_detail['subject'];
                $request_mail->content_type = \Config::get('const.CONTENT_TYPE.HTML');
                $request_mail->body = $order_detail['body'];
                $request_mail->recieved_at = $datetime_now;
                $request_mail->created_at = $datetime_now;
                $request_mail->created_user_id = $user->id;
                $request_mail->updated_at = $datetime_now;
                $request_mail->updated_user_id = $user->id;
                $request_mail->save();

                $request_work->requestMails()->attach($request_mail->id, [
                    'created_user_id' => $user->id,
                    'updated_user_id' => $user->id
                ]);

                $request_log_attributes = [
                    'request_id' => $request->id,
                    'type' => \Config::get('const.REQUEST_LOG_TYPE.IMPORT_COMPLETED'),
                    'request_work_id' => $request_work->id,
                    'created_user_id' => $user->id,
                    'updated_user_id' => $user->id,
                ];
                $this->storeRequestLog($request_log_attributes);

                $queue = new Queue;
                $queue->process_type = config('const.QUEUE_TYPE.ALLOCATE');
                $queue->argument = json_encode(['request_work_id' => $request_work->id, 'operators' => []]);
                $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
                $queue->created_at = $datetime_now;
                $queue->created_user_id = $user->id;
                $queue->updated_at = $datetime_now;
                $queue->updated_user_id = $user->id;
                $queue->save();

                $order_id = DB::table('order_details')
                    ->where('id', $order_detail['id'])
                    ->value('order_id');

                \DB::table('order_related_mails')->insert([
                    'order_id' => $order_id,
                    'order_detail_id' => $order_detail['id'],
                    'request_mail_id' => $request_mail->id,
                    'send_mail_id' => null,
                    'is_open_to_client' => false,
                    'created_at' => $datetime_now,
                    'created_user_id' => $user->id,
                    'updated_at' => $datetime_now,
                    'updated_user_id' => $user->id
                ]);
            }
            \DB::commit();
            return response()->json([
                'status' => 200
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'no_admin_permission',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 案件明細から送信メールを作成する
     *
     * @param Request $req
     * @return JsonResponse
     */
    public function createSendMail(Request $req): JsonResponse
    {
        $user = \Auth::user();

        $form = [
            'to' => $req->input('to'),
            'cc' => $req->input('cc'),
            'subject' => $req->input('subject'),
            'body' => $req->input('body'),
            'attachments' => $req->input('attachments') ? json_decode($req->input('attachments'), true) : [],
            'additional_attachments' => $req->input('additional_attachments') ? json_decode($req->input('additional_attachments'), true) : [],
            'order_detail_id' => $req->input('orderDetailId'),
        ];

        \DB::beginTransaction();
        try {
            $order_id = DB::table('order_details')
                ->where('id', $form['order_detail_id'])
                ->value('order_id');

            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }

            $register_time = Carbon::now();
            $user = \Auth::user();
            $send_mail = new SendMail;
            $send_mail->from = sprintf('%s <%s>', \Config::get('mail.from.name'), \Config::get('mail.from.address'));
            $send_mail->to = $form['to'];
            $send_mail->cc = $form['cc'];
            $send_mail->subject =  $form['subject'];
            $send_mail->content_type = \Config::get('const.CONTENT_TYPE.HTML');
            $send_mail->body = $form['body'] === null ? '' : $form['body'];
            $send_mail->created_at = $register_time;
            $send_mail->created_user_id = $user->id;
            $send_mail->updated_at = $register_time;
            $send_mail->updated_user_id = $user->id;
            $send_mail->save();

            // 添付ファイル
            if (is_array($form['attachments'])) {
                foreach ($form['attachments'] as $attachment) {
                    list(, $fileData) = explode(';', $attachment['file_data']);
                    list(, $fileData) = explode(',', $fileData);
                    $file_contents = base64_decode($fileData);
                    // ファイルのアップロード
                    Uploader::tryUploadAndSave(
                        $file_contents,
                        'send_mail_attachments/'. Carbon::now()->format('Ymd') .'/'. md5(microtime()) .'/'. $attachment['file_name'],
                        SendMailAttachment::class,
                        ['send_mail_id' => $send_mail->id]
                    );
                }
            }
            // 補足情報の添付ファイルをメールの添付ファイルに追加する
            if (is_array($form['additional_attachments'])) {
                foreach ($form['additional_attachments'] as $attachment) {
                    // ファイルのアップロード
                    Uploader::tryUploadAndSave(
                        Downloader::getFileContentFromS3($attachment['file_path']),
                        'send_mail_attachments/'. Carbon::now()->format('Ymd') .'/'. md5(microtime()) .'/'. $attachment['name'],
                        SendMailAttachment::class,
                        ['send_mail_id' => $send_mail->id]
                    );
                }
            }

            // 処理キュー登録
            $queue = new Queue;
            $queue->process_type = config('const.QUEUE_TYPE.MAIL_SEND');
            $queue->argument = json_encode(['mail_id' => $send_mail->id]);
            $queue->queue_status = config('const.QUEUE_STATUS.PREVIOUS');
            $queue->created_at = $register_time;
            $queue->created_user_id = $user->id;
            $queue->updated_at = $register_time;
            $queue->updated_user_id = $user->id;
            $queue->save();

            \DB::table('order_related_mails')->insert([
                'order_id' => $order_id,
                'order_detail_id' => $form['order_detail_id'],
                'request_mail_id' => null,
                'send_mail_id' => $send_mail->id,
                'is_open_to_client' => false,
                'created_at' => $register_time,
                'created_user_id' => $user->id,
                'updated_at' => $register_time,
                'updated_user_id' => $user->id
            ]);

            \DB::commit();
            return response()->json([
                'status' => 200
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'no_admin_permission',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 案件明細に紐づく依頼を取得する
     *
     * @param Request $req
     * @return JsonResponse
     */
    public function searchRequests(Request $req): JsonResponse
    {
        $user = \Auth::user();
        $order_detail_id = $req->get('order_detail_id');

        $order_detail = OrderDetail::find($order_detail_id);
        $requests = $order_detail->requests()
            ->select(
                'businesses.name as business_name',
                \DB::raw('(SELECT count(*) FROM businesses_admin WHERE businesses_admin.business_id = businesses.id AND businesses_admin.user_id = ' . $user->id . ') as admin_flg'),
                'requests.*'
            )
            ->join('businesses', 'requests.business_id', 'businesses.id')
            ->get();

        return response()->json([
            'status' => 200,
            'requests' => $requests
        ]);
    }

    /**
     * 業務情報を取得する
     *
     * @param Request $req
     * @return JsonResponse
     */
    public function searchBusinesses(Request $req): JsonResponse
    {
        $user = \Auth::user();

        $businessesInCharge = User::find($user->id)
            ->businessesInCharge()
            ->select(
                'businesses.id as business_id',
                'businesses.name as business_name'
            )
            ->get();

        return response()->json([
            'status' => 200,
            'businesses_in_charge' => $businessesInCharge
        ]);
    }

    /**
     * 関連メールを取得
     * @param Request $req
     * @return JsonResponse
     */
    public function getRelatedMailList(Request $req)
    {
        $order_detail_id = $req->input('order_detail_id');
        $order_related_mails = \DB::table('order_related_mails')
            ->where('order_detail_id', $order_detail_id)
            ->where('is_deleted', \Config::get('const.FLG.INACTIVE'))
            ->get();
        $related_mails = [];
        foreach ($order_related_mails as $order_related_mail) {
            if ($order_related_mail->request_mail_id !== null) {// 依頼メール
                $request_mail = RequestMail::find($order_related_mail->request_mail_id);
                if ($request_mail === null) {
                    continue;
                }
                $request_mail->attachments = $request_mail->requestMailAttachments;
                unset($request_mail->requestMailAttachments);
                $request_mail->original_body = $request_mail->body;
                $request_mail->pivot = [
                    'id' => $order_related_mail->id,
                    'from' => $order_related_mail->from,
                    'created_user_id' => $order_related_mail->created_user_id,
                    'updated_user_id' => $order_related_mail->updated_user_id
                ];
                if ($request_mail->content_type === RequestMail::CONTENT_TYPE_HTML) {
                    $request_mail->body = strip_tags($request_mail->body, '<br>');
                } else {
                    $request_mail->body = nl2br(e($request_mail->body));
                }
                array_push($related_mails, $request_mail);
            } else {// 送信メール
                $send_mail = SendMail::find($order_related_mail->send_mail_id);
                if ($send_mail === null) {
                    continue;
                }
                $send_mail->load('sendMailAttachments');
                $send_mail->attachments = $send_mail->sendMailAttachments;
                unset($send_mail->sendMailAttachments);
                $send_mail->original_body = $send_mail->body;
                $send_mail->pivot = [
                    'id' => $order_related_mail->id,
                    'from' => $order_related_mail->from,
                    'created_user_id' => $order_related_mail->created_user_id,
                    'updated_user_id' => $order_related_mail->updated_user_id
                ];
                if ($send_mail->mail_content_type === RequestMail::CONTENT_TYPE_HTML) {
                    $send_mail->body = strip_tags($send_mail->body, '<br>');
                } else {
                    $send_mail->body = nl2br(e($send_mail->body));
                }
                array_push($related_mails, $send_mail);
            }
        }
        return response()->json([
            'status' => 200,
            'related_mails' => $related_mails,
            'started_at' => Carbon::now()
        ]);
    }

    /**
     * 関連メールを削除
     * @param Request $req
     * @return JsonResponse
     */
    public function deleteOrderRelatedMail(Request $req)
    {
        $form = [
            'order_related_mail_id' => $req->input('order_related_mail_id'),
            'started_at' => $req->input('started_at')
        ];

        try {
            $order_id = \DB::table('order_related_mails')
                ->where('id', $form['order_related_mail_id'])
                ->value('order_id');

            $user = \Auth::user();

            $is_admin = \DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }

            // 排他制御--------------------------------------
            $can_update = \DB::table('order_related_mails')
                ->where('updated_at', '>=', $form['started_at'])
                ->count() === 0;
            if (!$can_update) {
                throw new ExclusiveException("The data was updated by another user");
            }
            // 排他制御--------------------------------------

            $register_time = Carbon::now();
            \DB::table('order_related_mails')
                ->where('id', $form['order_related_mail_id'])
                ->update([
                    'is_deleted' => \Config::get('const.FLG.ACTIVE'),
                    'updated_at' => $register_time,
                    'updated_user_id' => $user->id
                ]);

            \DB::commit();
            return response()->json([
                'status' => 200
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage() === 'no admin permission' ? 'no_admin_permission' : 'updated_by_others',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'internal_error',
            ]);
        }
    }

    /**
     * 関連メール取込用のエイリアスを生成
     * @param Request $req
     * @return JsonResponse
     */
    public function aliasMailAddress(Request $req)
    {
        $user = \Auth::user();

        \DB::beginTransaction();
        try {
            $order_id = DB::table('order_details')
                ->where('id', $req->order_detail_id)
                ->value('order_id');

            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }

            $alias = ImportOrderRelatedMailAlias::tryCreateAlias($order_id, $req->order_detail_id);
            ImportOrderRelatedMailAlias::create(
                [
                    'order_id' => $order_id,
                    'order_detail_id' => $req->order_detail_id,
                    'from' => $req->custom_from,
                    'alias' => $alias,
                    'created_user_id' => $user->id,
                ]
            );

            \DB::commit();

            return response()->json([
                'status' => 200,
                'alias' => $alias,
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'no_admin_permission',
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'internal_error',
            ]);
        }
    }

    /**
     * 表示形式を設定する
     *
     * @param Request $req
     * @return JsonResponse
     */
    public function settingDisplayFormat(Request $req): JsonResponse
    {
        $form = [
            'order_id' => $req->input('order_id'),
            'setting_display_formats' => $req->input('setting_display_formats'),
            'started_at' => $req->input('started_at')
        ];
        $user = \Auth::user();

        \DB::beginTransaction();
        try {
            $is_admin = \DB::table('orders_administrators')
                ->where('order_id', $form['order_id'])
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }

            $register_time = Carbon::now();
            foreach ($form['setting_display_formats'] as $setting_display_format) {
                // 排他制御--------------------------------------
                $can_update = OrderFileImportColumnConfig::where('id', $setting_display_format['orderFileImportColumnConfigId'])
                    ->where('updated_at', '>=', $form['started_at'])
                    ->count() === 0;

                if (!$can_update) {
                    throw new ExclusiveException("The order details data was updated by another user");
                }
                // 排他制御--------------------------------------

                OrderFileImportColumnConfig::where('id', $setting_display_format['orderFileImportColumnConfigId'])
                    ->update([
                        'display_format' => array_sum($setting_display_format['selectedDisplayFormats']),
                        'updated_user_id' => $user->id,
                        'updated_at' => $register_time
                    ]);
            }

            \DB::commit();
            return response()->json([
                'status' => 200
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage() === 'no admin permission' ? 'no_admin_permission' : 'updated_by_others',
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 依頼作成の際に必要なデータを取得する
     * @param Request $req
     * @return JsonResponse
     */
    public function getCreateRequestData(Request $req): JsonResponse
    {
        $label_ids = $req->input('label_ids') ? json_decode($req->input('label_ids'), true) : [];
        $order_detail_ids = $req->input('order_detail_ids') ? json_decode($req->input('order_detail_ids'), true) : [];

        $order_detail_columns = DB::table('order_file_import_column_configs')
            ->select(
                'order_detail_values.order_detail_id AS order_detail_id',
                'order_detail_values.value AS value',
                'order_file_import_column_configs.label_id AS label_id',
                'order_file_import_column_configs.item_type AS item_type'
            )
            ->join('order_file_import_column_configs_order_detail_values', 'order_file_import_column_configs.id', '=', 'order_file_import_column_configs_order_detail_values.order_file_import_column_config_id')
            ->join('order_detail_values', 'order_file_import_column_configs_order_detail_values.order_detail_value_id', '=', 'order_detail_values.id')
            ->whereIn('order_detail_values.order_detail_id', $order_detail_ids)
            ->get()
            ->groupBy('order_detail_id')
            ->toArray();

        $order_detail_names = DB::table('order_details')
            ->select('id', 'name')
            ->whereIn('id', $order_detail_ids)
            ->pluck('name', 'id');

        return response()->json([
            'status' => 200,
            'order_detail_names' => $order_detail_names,
            'order_detail_columns' => $order_detail_columns,
            'label_data' => Label::getLangKeySetByIds($label_ids),
        ]);
    }
}
