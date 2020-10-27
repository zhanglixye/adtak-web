<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\CustomStatusAttribute;
use App\Models\CustomStatus;
use App\Models\User;
use App\Models\OrdersAdministrator;
use App\Services\FileManager\CreateOrderFile;
use App\Services\ZipFileManager\ZipService;
use Carbon\Carbon;
use App\Models\Label;
use App\Models\Language;
use App\Exceptions\ExclusiveException;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{

    /**
     * 案件一覧のデータ取得
     * @param Request $req
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $req): JsonResponse
    {
        $user = \Auth::user();

        // 検索条件の取得
        $form = [
            // 検索欄の項目
            'order_id' => $req->input('order_id'),
            'order_name' => $req->input('order_name'),
            'from' => $req->input('from'),
            'to' => $req->input('to'),
            'status' => $req->input('status'),
            'imported_file_name' => $req->input('imported_file_name'),
            'importer' => $req->input('importer'),

            // 一覧表示に関する項目
            'page' => $req->input('page'),
            'sort_by' => $req->get('sort_by'),
            'descending' => $req->get('descending') ? 'desc' : 'asc',
            'rows_per_page' => $req->get('rows_per_page'),
        ];

        $query = DB::table('orders')
            ->join('orders_administrators', 'orders.id', '=', 'orders_administrators.order_id')
            ->leftJoin('orders_sharers', 'orders.id', '=', 'orders_sharers.order_id')
            ->join('orders_order_files', function ($join) {
                $join->on('orders.id', '=', 'orders_order_files.order_id')
                    ->where('orders_order_files.import_type', '=', \Config::get('const.FILE_IMPORT_TYPE.NEW'));
            })
            ->join('order_files', 'orders_order_files.order_file_id', '=', 'order_files.id')
            ->join('users', 'orders.created_user_id', '=', 'users.id')
            ->where(function ($query) use ($user) {
                $query->where('orders_administrators.user_id', $user->id)
                    ->orWhere('orders_sharers.user_id', $user->id);
            })
            ->groupBy('orders.id', 'order_files.id');

        // 案件ID
        if ($form['order_id']) {
            $query = $query->where('orders.id', mb_convert_kana($form['order_id'], "n"));
        }

        // 件名
        if ($form['order_name']) {
            $query = $query->where('orders.name', 'LIKE', '%' . $form['order_name'] . '%');
        }

        // 取込日時
        if ($form['from'] && $form['to']) {
            $query = $query->whereBetween('orders.created_at', [$form['from'], $form['to']]);
        } elseif ($form['from'] && !$form['to']) {
            $query = $query->where('orders.created_at', '>=', $form['from']);
        } elseif (!$form['from'] && $form['to']) {
            $query = $query->where('orders.created_at', '<=', $form['to']);
        }

        // ステータス
        if (is_numeric($form['status'])) {
            if ($form['status'] === \Config::get('const.FLG.ACTIVE')) {
                $query->where('orders.is_active', '=', \Config::get('const.FLG.ACTIVE'));
            } elseif ($form['status'] === \Config::get('const.FLG.INACTIVE')) {
                $query->where('orders.is_active', '=', \Config::get('const.FLG.INACTIVE'));
            }
        }

        // 取込ファイル名
        if ($form['imported_file_name']) {
            $query = $query->where('order_files.name', 'LIKE', '%' . $form['imported_file_name'] . '%');
        }

        // 取込担当者
        if ($form['importer']) {
            $query = $query->where('users.name', 'LIKE', '%' . $form['importer'] . '%');
        }

        // 除外対象
        $query = $query->where('orders.is_deleted', \Config::get('const.FLG.INACTIVE'));

        $form['sort_by'] = $form['sort_by'] === 'importer_id_no_image' ? 'importer_id' : $form['sort_by'];

        $all_order_ids = $query->pluck('orders.id');

        $query->select(
            'orders.id AS order_id',
            'orders.name AS order_name',
            'orders.created_at AS imported_at',
            'orders.is_active AS status',
            'orders.created_user_id AS importer_id',
            'order_files.name AS imported_file_name',
            'order_files.id AS imported_file_id'
        );

        // sort
        if ($form['sort_by']) {
            $query = $query->orderBy($form['sort_by'], $form['descending']);
        }

        $query = $query->orderBy('order_id', 'asc');

        /** @var \Illuminate\Pagination\LengthAwarePaginator $list */
        $list = $query->paginate($form['rows_per_page'], ['*'], 'page', (int)$form['page']);

        // 全ユーザ情報を保持
        $candidates = User::select(
            'users.id',
            'users.name',
            'users.user_image_path'
        )->get();

        $orders_administrators = DB::table('orders_administrators')
            ->select('order_id')
            ->where('user_id', $user->id);

        $is_admin = $orders_administrators->count() !== 0;

        $all_admin_order_ids = $orders_administrators
            ->whereIn('order_id', $list->pluck('order_id'))
            ->pluck('order_id');

        return response()->json([
            'list' => $list, // 案件一覧
            'all_order_ids' => $all_order_ids, //全件作業ID
            'all_admin_order_ids' => $all_admin_order_ids, // 管理者権限を持つ案件ID
            'candidates' => $candidates,
            'is_admin' => $is_admin,
            'started_at' => Carbon::now(),
        ]);
    }

    /**
     * 案件の一括更新
     * @param Request $req
     * @return JsonResponse
     */
    public function bulkUpdate(Request $req): JsonResponse
    {
        $user = \Auth::user();

        // 検索条件の取得
        $form = [
            // 検索欄の項目
            'order_ids' => $req->input('order_ids'),
            'is_active' => $req->input('is_active'),
            'started_at' => $req->input('started_at'),
        ];

        DB::beginTransaction();
        try {
            // 排他制御--------------------------------------
            $can_update = \DB::table('orders')
                ->whereIn('id', $form['order_ids'])
                ->where('updated_at', '>=', $form['started_at'])
                ->count() === 0;
            if (!$can_update) {
                throw new ExclusiveException("The orders data was updated by another user");
            }
            // 排他制御--------------------------------------
            $admin_order_ids = \DB::table('orders_administrators')
                ->where('user_id', $user->id)
                ->pluck('order_id');
            Order::whereIn('id', $admin_order_ids)
                ->whereIn('id', $form['order_ids'])
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
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'updated_by_others',
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
     * 案件のDL機能
     * @param Request $req
     * @return JsonResponse
     */
    public function createEachOrderFile(Request $req): JsonResponse
    {
        $user = \Auth::user();
        $order_ids = $req->input('order_ids');

        try {
            $admin_order_ids = DB::table('orders_administrators')
                ->whereIn('order_id', $order_ids)
                ->where('user_id', $user->id)
                ->pluck('order_id')
                ->toArray();

            $sharer_order_ids = DB::table('orders_sharers')
                ->whereIn('order_id', $order_ids)
                ->where('user_id', $user->id)
                ->pluck('order_id')
                ->toArray();

            $admin_or_sharer_order_ids = array_unique(array_merge($admin_order_ids, $sharer_order_ids));

            if (count($admin_or_sharer_order_ids) !== count($order_ids)) {
                throw new ExclusiveException("no permission");
            }

            $order_detail_ids = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->whereIn('orders.id', $order_ids)
                ->where('order_details.is_deleted', \Config::get('const.FLG.INACTIVE'))
                ->pluck('order_details.id')->toArray();

            $file_paths = CreateOrderFile::create($order_detail_ids);
            if (count($file_paths) === 1) {
                return response()->json(
                    [
                        'status' => 200,
                        'file_path' => $file_paths[0]['local_path'],
                        'file_name' => $file_paths[0]['file_name']
                    ]
                );
            } else {
                $zip_info = ZipService::compress(
                    $file_paths,
                    str_replace('.', '-', strval(microtime(true))) . 'orders.zip'
                );
                $public_disk = \Storage::disk('public');
                $public_disk->delete(array_column($file_paths, 'local_path'));// 一時保存ファイルを削除
                // 作成したzipのパスを返却する
                return response()->json(
                    [
                        'status' => 200,
                        'file_path' => $zip_info['local_path'],
                        'file_name' => 'orders.zip'
                    ]
                );
            }
        } catch (ExclusiveException $e) {
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'no_permission',
            ]);
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
     * 案件設定で必要なデータを取得
     * @param Request $req
     * @return JsonResponse
     */
    public function edit(Request $req): JsonResponse
    {
        $user = \Auth::user();
        $order_id = $req->input('order_id');

        try {
            /** @var Order|null */
            $order = Order::where('is_deleted', false)->find($order_id);
            if (is_null($order)) {
                throw new \Exception('not found order id : ' . $order_id);
            }

            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }

            // ラベルデータ
            $label_ids = [];

            // カスタムステータス
            $custom_statuses = DB::table('custom_statuses')
                ->selectRaw(
                    'custom_statuses.id AS custom_status_id,' .
                    'custom_statuses.label_id AS label_id,' .
                    'CASE WHEN COUNT(order_details_related_custom_status_attributes.id) = 0 THEN ' . \Config::get('const.FLG.INACTIVE') .
                    ' WHEN COUNT(order_details_related_custom_status_attributes.id) != 0 THEN ' . \Config::get('const.FLG.ACTIVE') .
                    ' END AS is_use_custom_status' // 案件明細で使用されているかを判断
                )
                ->leftJoin(
                    'order_details_related_custom_status_attributes',
                    'custom_statuses.id',
                    '=',
                    'order_details_related_custom_status_attributes.custom_status_id'
                )
                ->where('custom_statuses.order_id', $order_id)
                ->where('custom_statuses.is_deleted', \Config::get('const.FLG.INACTIVE'))
                ->groupBy('custom_statuses.id', 'custom_statuses.label_id')
                ->orderBy('custom_statuses.sort')
                ->get();

            // ラベルデータ追加
            $label_ids = array_merge($label_ids, $custom_statuses->pluck('label_id')->toArray());

            // 属性の追加
            foreach ($custom_statuses as $custom_status) {
                // カスタムステータスに紐づく属性を取得
                $custom_status->attributes = DB::table('custom_status_attributes')
                    ->selectRaw(
                        'custom_status_attributes.id AS id,' .
                        'custom_status_attributes.label_id AS label_id,' .
                        'CASE WHEN COUNT(order_details_related_custom_status_attributes.id) = 0 THEN ' . \Config::get('const.FLG.INACTIVE') .
                        ' WHEN COUNT(order_details_related_custom_status_attributes.id) != 0 THEN ' . \Config::get('const.FLG.ACTIVE') .
                        ' END AS is_use_custom_status_attribute' // 案件明細で使用されているかを判断
                    )
                    ->leftJoin(
                        'order_details_related_custom_status_attributes',
                        'custom_status_attributes.id',
                        '=',
                        'order_details_related_custom_status_attributes.custom_status_attribute_id'
                    )
                    ->where('custom_status_attributes.custom_status_id', $custom_status->custom_status_id)
                    ->where('custom_status_attributes.is_deleted', \Config::get('const.FLG.INACTIVE'))
                    ->groupBy('custom_status_attributes.id', 'custom_status_attributes.label_id')
                    ->orderBy('custom_status_attributes.sort')
                    ->get();

                // ラベルデータ追加
                $label_ids = array_merge($label_ids, $custom_status->attributes->pluck('label_id')->toArray());
            }

            // ラベル一覧を取得
            $label_data = Label::getLangKeySetByIds($label_ids);

            return response()->json([
                'status' => 200,
                'order' => $order,
                'custom_statuses' => $custom_statuses,
                'label_data' => $label_data,
                'started_at' => Carbon::now(),
            ]);
        } catch (ExclusiveException $e) {
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'no_admin_permission',
            ]);
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
     * 案件設定のデータを更新
     * @param Request $req
     * @return JsonResponse
     */
    public function update(Request $req): JsonResponse
    {
        $order_id = $req->input('order.id');
        $order_name = $req->input('order.name');
        $custom_statuses = $req->input('order.custom_statuses') ? json_decode(
            $req->input('order.custom_statuses'),
            true
        ) : [];
        $delete_custom_status_ids = $req->input('order.delete_custom_status_ids') ? $req->input('order.delete_custom_status_ids') : [];
        $delete_custom_status_attribute_ids = $req->input('order.delete_custom_status_attribute_ids') ? $req->input('order.delete_custom_status_attribute_ids') : [];
        $started_at = $req->input('started_at');

        $user = \Auth::user();

        \DB::beginTransaction();
        try {
            /** @var Order|null */
            $order = Order::where('is_deleted', false)->find($order_id);
            if (is_null($order)) {
                throw new \Exception('not found order id : ' . $order_id);
            }

            // 排他制御
            $can_update = Order::where('id', $order->id)->where('updated_at', '>=', $started_at)->count() === 0;
            if (!$can_update) {
                throw new ExclusiveException("The order data was updated by another user");
            }

            if (!is_string($order_name) || mb_strlen($order_name) > 256) {
                throw new \Exception('error order name :' . $order_name);
            }

            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }

            // 各テーブルのcreated_at, updated_atに登録する値
            $register_time = Carbon::now();

            $order->fill([
                'name' => $order_name,
                'updated_at' => $register_time,
                'updated_user_id' => $user->id
            ])
                ->save();

            // カスタムステータス名及び、属性名は日本語で登録
            $language = DB::table('languages')
                ->where('languages.code', 'ja')
                ->first();

            // 論理削除
            CustomStatus::whereIn('id', $delete_custom_status_ids)
                ->update([
                    'sort' => null,
                    'is_deleted' => \Config::get('const.FLG.ACTIVE'),
                    'updated_at' => $register_time,
                    'updated_user_id' => $user->id
                ]);

            // 論理削除
            CustomStatusAttribute::whereIn('id', $delete_custom_status_attribute_ids)
                ->update([
                    'sort' => null,
                    'is_deleted' => \Config::get('const.FLG.ACTIVE'),
                    'updated_at' => $register_time,
                    'updated_user_id' => $user->id
                ]);

            // custom_status_attributesが論理削除された場合、関連するレコードは物理削除
            DB::table('order_details_related_custom_status_attributes')
                ->whereIn('custom_status_attribute_id', $delete_custom_status_attribute_ids)
                ->delete();

            if (!empty($custom_statuses)) {
                // 編集するcustom_statusのsortをnullに変更
                $update_custom_status_ids = array_filter(array_column($custom_statuses, 'customStatusId'));
                DB::table('custom_statuses')
                    ->whereIn('id', $update_custom_status_ids)
                    ->update(['sort' => null]);

                // 編集するcustom_status_attributeのsortをnullに変更
                $update_custom_status_attributes = call_user_func_array(
                    "array_merge",
                    array_column($custom_statuses, 'attributes')
                );
                $update_custom_status_attribute_ids = array_filter(array_column(
                    $update_custom_status_attributes,
                    'id'
                ));
                DB::table('custom_status_attributes')
                    ->whereIn('id', $update_custom_status_attribute_ids)
                    ->update(['sort' => null]);
            }

            // カスタムステータス登録
            $custom_status_sort = 1;
            foreach ($custom_statuses as $custom_status) {
                $label = new Label;
                if ($custom_status['labelId'] === null) { // insert
                    $label->language_id = $language->id;
                    $label->name = $custom_status['customStatusName'];
                    $label->created_at = $register_time;
                    $label->created_user_id = $user->id;
                    $label->updated_at = $register_time;
                    $label->updated_user_id = $user->id;
                    $label->save();
                } else { // update
                    DB::table('labels')
                        ->where('label_id', $custom_status['labelId'])
                        ->where('language_id', $language->id)
                        ->update(
                            [
                                'name' => $custom_status['customStatusName'],
                                'updated_at' => $register_time,
                                'updated_user_id' => $user->id
                            ]
                        );
                }

                $old_custom_status = CustomStatus::where('id', $custom_status['customStatusId'])
                    ->first();

                $upsert_custom_status = CustomStatus::updateOrCreate(
                    ['id' => $custom_status['customStatusId']],
                    [
                        'order_id' => $order_id,
                        'label_id' => $custom_status['labelId'] === null ? $label->id : $custom_status['labelId'],
                        'sort' => $custom_status_sort,
                        'created_at' => is_null($old_custom_status) ? $register_time : $old_custom_status->created_at,
                        'created_user_id' => is_null($old_custom_status) ? $user->id : $old_custom_status->created_user_id,
                        'updated_at' => $register_time,
                        'updated_user_id' => $user->id
                    ]
                );

                $custom_status_attribute_sort = 1;
                // カスタムステータスの属性登録
                foreach ($custom_status['attributes'] as $attribute) {
                    $attribute_label = new Label;
                    if ($attribute['labelId'] === null) { // insert
                        $attribute_label->language_id = $language->id;
                        $attribute_label->name = $attribute['name'];
                        $attribute_label->created_at = $register_time;
                        $attribute_label->created_user_id = $user->id;
                        $attribute_label->updated_at = $register_time;
                        $attribute_label->updated_user_id = $user->id;
                        $attribute_label->save();
                    } else { // update
                        DB::table('labels')
                            ->where('label_id', $attribute['labelId'])
                            ->where('language_id', $language->id)
                            ->update(
                                [
                                    'name' => $attribute['name'],
                                    'updated_at' => $register_time,
                                    'updated_user_id' => $user->id
                                ]
                            );
                    }

                    $old_custom_status_attribute = CustomStatusAttribute::where('id', $attribute['id'])
                        ->first();

                    CustomStatusAttribute::updateOrCreate(
                        ['id' => $attribute['id']],
                        [
                            'custom_status_id' => $upsert_custom_status->id,
                            'label_id' => $attribute['labelId'] === null ? $attribute_label->id : $attribute['labelId'],
                            'sort' => $custom_status_attribute_sort,
                            'created_at' => is_null($old_custom_status_attribute) ? $register_time : $old_custom_status_attribute->created_at,
                            'created_user_id' => is_null($old_custom_status_attribute) ? $user->id : $old_custom_status_attribute->created_user_id,
                            'updated_at' => $register_time,
                            'updated_user_id' => $user->id
                        ]
                    );
                    $custom_status_attribute_sort += 1;
                }
                $custom_status_sort += 1;
            }

            \DB::commit();

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
     * 項目のデータ取得
     * @param Request $req
     * @return JsonResponse
     */
    public function editItems(Request $req): JsonResponse
    {
        try {
            $order_id = $req->order_id;
            $user = \Auth::user();

            /** @var Order|null */
            $order = Order::where('is_deleted', false)->find($order_id);
            if (is_null($order)) {
                throw new \Exception('not found order id :' . $order_id);
            }

            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }

            /** @var \Illuminate\Database\Eloquent\Collection */
            $items = $order
                ->orderFiles()
                ->wherePivot('import_type', \Config::get('const.FILE_IMPORT_TYPE.NEW'))
                ->first()
                ->orderFileImportMainConfigs
                ->first()
                ->orderFileImportColumnConfigs()
                ->select('id', 'item', 'label_id')
                ->get();

            /** @var array */
            $label_data = $items->pluck('label_id')->toArray();

            $label_data = Label::getLangKeySetByIds($label_data);

            return response()->json([
                'status' => 200,
                'order' => $order,
                'items' => $items,
                'labelData' => $label_data,
                'started_at' => Carbon::now(),
            ]);
        } catch (ExclusiveException $e) {
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'no_admin_permission',
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => 'internal_error',
            ]);
        }
    }

    /**
     * 項目の更新
     * @param Request $req
     * @return JsonResponse
     */
    public function updateItems(Request $req): JsonResponse
    {
        /** @var int|null */
        $order_id = is_numeric($req->order_id) ? intval($req->order_id) : null;

        /** @var array */
        $items = json_decode($req->input('items'), true) ?? [];

        /** @var string */
        $started_at = $req->input('started_at');

        /** @var User */
        $user = \Auth::user();

        \DB::beginTransaction();
        try {
            /** @var Order|null */
            $order = Order::where('is_deleted', false)->find($order_id);
            if (is_null($order)) {
                throw new \Exception('not found order id :' . $order_id);
            }

            /** @var int|null */
            $language_id = Language::where('code', 'ja')->value('id');// 更新するLanguageテーブルを日本語に限定する

            // 排他制御------------------
            /** @var int */
            $label_ids = $order
                ->orderFiles()
                ->wherePivot('import_type', \Config::get('const.FILE_IMPORT_TYPE.NEW'))
                ->first()
                ->orderFileImportMainConfigs
                ->first()
                ->orderFileImportColumnConfigs()
                ->pluck('label_id');

            /** @var bool */
            $can_update = Label::whereIn('label_id', $label_ids)
                    ->where('language_id', $language_id)
                    ->where('updated_at', '>=', $started_at)
                    ->count() === 0;

            if (!$can_update) {
                throw new ExclusiveException("The data was updated by another user");
            }

            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }
            // !排他制御------------------

            $updated_at = Carbon::now();// 更新時間

            // ラベルの更新処理
            foreach ($items as $item) {
                /** @var int */
                $updated_record_count = Label::where('label_id', $item['label_id'])
                    ->where('language_id', $language_id)
                    ->update([
                        'name' => $item['new_display'],
                        'updated_user_id' => $user->id,
                        'updated_at' => $updated_at,
                    ]);
                if ($updated_record_count === 0) {
                    throw new \Exception('No updated');
                }
            }

            \DB::commit();

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
     *
     * 案件管理者と共有者を取得
     * @param Request $req
     * @return JsonResponse
     */
    public function orderCandidates(Request $req): JsonResponse
    {
        $user = \Auth::user();
        $order_id = $req->get('order_id');
        $order_admins = Order::find($order_id)
            ->administrators()
            ->select(
                'users.id',
                'users.name',
                'users.user_image_path'
            )->get();

        $order_sharers = Order::find($order_id)
            ->sharers()
            ->select(
                'users.id',
                'users.name',
                'users.user_image_path'
            )->get();
        return response()->json([
            'status' => 200,
            'order_admins' => $order_admins,
            'order_sharers' => $order_sharers
        ]);
    }

    /**
     *
     *案件管理候補者を取得
     * @param Request $req
     * @return JsonResponse
     */
    public function adminCandidateUsers(Request $req): JsonResponse
    {
        $user_id = \Auth::user()->id;
        $order_id = $req->get('order_id');
        $admin_candidate_users = DB::table('users')
            ->select(
                'users.id',
                'users.name',
                'users.user_image_path',
                'users.email'
            )
            ->join('businesses_candidates', 'businesses_candidates.user_id', '=', 'users.id')
            ->distinct()
            ->whereNotIn('users.id', function ($query) use ($order_id) {
                $query->select('orders_administrators.user_id')
                    ->from('orders_administrators')
                    ->where('orders_administrators.order_id', $order_id);
            })
            ->whereIn('businesses_candidates.business_id', function ($query) use ($user_id) {
                $query->select('businesses_candidates.business_id as business_id')
                    ->from('businesses_candidates')
                    ->where('businesses_candidates.user_id', $user_id);
            })->get();
        return response()->json([
            'status' => 200,
            'admin_candidate_users' => $admin_candidate_users
        ]);
    }

    /**
     *
     * 共有候補者を取得
     * @param Request $req
     * @return JsonResponse
     */
    public function sharerCandidateUsers(Request $req): JsonResponse
    {
        $user_id = \Auth::user()->id;
        $order_id = $req->get('order_id');
        $sharer_candidate_users = DB::table('users')
            ->select(
                'users.id',
                'users.name',
                'users.user_image_path',
                'users.email'
            )
            ->join('businesses_candidates', 'businesses_candidates.user_id', '=', 'users.id')
            ->distinct()
            ->whereNotIn('users.id', function ($query) use ($order_id) {
                $query->select('orders_sharers.user_id')
                    ->from('orders_sharers')
                    ->where('orders_sharers.order_id', $order_id);
            })
            ->whereIn('businesses_candidates.business_id', function ($query) use ($user_id) {
                $query->select('businesses_candidates.business_id as business_id')
                    ->from('businesses_candidates')
                    ->where('businesses_candidates.user_id', $user_id);
            })->get();
        return response()->json([
            'status' => 200,
            'sharer_candidate_users' => $sharer_candidate_users
        ]);
    }

    /**
     *
     *案件管理者を追加
     * @param Request $req
     * @return JsonResponse
     */
    public function addAdministrators(Request $req): JsonResponse
    {

        $order_admin_candidate_users = $req->get('order_admin_candidate_users');
        $order_id = $req->get('order_id');
        $orders_administrator_params = [];
        $time_now = Carbon::now();
        \DB::beginTransaction();
        try {
            $user = \Auth::user();

            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }

            foreach ($order_admin_candidate_users as $order_admin_candidate_user) {
                array_push($orders_administrator_params, [
                    'order_id' => $order_id,
                    'user_id' => $order_admin_candidate_user['id'],
                    'created_at' => $time_now,
                    'created_user_id' => $user->id,
                    'updated_at' => $time_now,
                    'updated_user_id'=>  $user->id
                ]);
            }
            if (count($orders_administrator_params)) {
                \DB::table('orders_administrators')->insert($orders_administrator_params);
            }
            \DB::commit();
            return response()->json([
                'status' => 200,
                'result' => 'success'
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
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     *
     * 案件共有者を追加
     * @param Request $req
     * @return JsonResponse
     */
    public function addSharers(Request $req): JsonResponse
    {
        $order_sharer_candidate_users = $req->get('order_sharer_candidate_users');
        $order_id = $req->get('order_id');
        $orders_sharer_params = [];
        $time_now = Carbon::now();
        \DB::beginTransaction();
        try {
            $user = \Auth::user();

            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }
            foreach ($order_sharer_candidate_users as $order_sharer_candidate_user) {
                array_push($orders_sharer_params, [
                    'order_id' => $order_id,
                    'user_id' => $order_sharer_candidate_user['id'],
                    'created_at' => $time_now,
                    'created_user_id' => $user->id,
                    'updated_at' => $time_now,
                    'updated_user_id'=>  $user->id
                ]);
            }
            if (count($orders_sharer_params)) {
                \DB::table('orders_sharers')->insert($orders_sharer_params);
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
            \DB::rollback();
            report($e);

            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     *
     * 案件共有者を削除
     * @param Request $req
     * @return JsonResponse
     */
    public function deleteSharers(Request $req): JsonResponse
    {
        $sharer_candidate_ids = $req->get('sharer_candidate_ids');
        $order_id = $req->get('order_id');
        \DB::beginTransaction();
        try {
            $user = \Auth::user();

            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }
            \DB::table('orders_sharers')
                ->where('order_id', $order_id)
                ->whereIn('user_id', $sharer_candidate_ids)
                ->delete();
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
            \DB::rollback();
            report($e);
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     *
     *案件管理者を削除
     * @param Request $req
     * @return JsonResponse
     */
    public function deleteAdministrators(Request $req): JsonResponse
    {
        $admin_candidate_ids = $req->get('admin_candidate_ids');
        $order_id = $req->get('order_id');
        \DB::beginTransaction();
        try {
            $user = \Auth::user();

            $is_admin = DB::table('orders_administrators')
                ->where('order_id', $order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;

            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }
            $delete_sum = OrdersAdministrator::where('order_id', $order_id)
                ->whereIn('user_id', $admin_candidate_ids)->delete();
            \DB::commit();
            return response()->json([
                'status' => 200,
                'sum' => $delete_sum
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
                'message' => $e->getMessage(),
            ]);
        }
    }
}
