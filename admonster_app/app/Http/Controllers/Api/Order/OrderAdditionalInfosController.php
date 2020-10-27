<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Models\OrderAdditionalInfo;
use App\Models\OrderAdditionalAttachment;
use App\Models\OrdersAdministrator;
use App\Services\Traits\RequestLogStoreTrait;
use App\Services\UploadFileManager\Uploader;
use App\Exceptions\ExclusiveException;

class OrderAdditionalInfosController extends Controller
{
    use RequestLogStoreTrait;

    /**
     * [GET]案件補足情報一覧のデータ取得API
     *
     * @param Request $req
     * @return JsonResponse
     */
    public function index(Request $req): JsonResponse
    {
        $user = \Auth::user();

        // 検索条件の取得
        $order_id =  $req->input('order_id');
        $form = [
            'order_detail_id' => $req->input('order_detail_id'),
        ];

        $order_additional_infos = OrderAdditionalInfo::getSearchListByOrderId($order_id, $form)->with([
            'orderAdditionalAttachments' => function ($query) {
                $query->where('is_deleted', \Config::get('const.FLG.INACTIVE'));
            }
        ])->get();

        return response()->json([
            'status' => 200,
            'order_additional_infos' => $order_additional_infos,
        ]);
    }

    /**
     * [POST]案件補足情報のデータ登録API
     *
     * @param Request $req
     * @return JsonResponse
     */
    public function store(Request $req): JsonResponse
    {
        $user = \Auth::user();

        \DB::beginTransaction();
        try {
            // 排他制御--------------------------------------
            $is_admin = OrdersAdministrator::where('order_id', $req->order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;
            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }
            // 排他制御--------------------------------------

            $order_additional_info = new OrderAdditionalInfo;
            $order_additional_info->order_id = $req->order_id;
            $order_additional_info->order_detail_id = $req->input('order_detail_id');
            $order_additional_info->content = $req->content;
            $order_additional_info->is_open_to_client = $req->is_open_to_client;
            $order_additional_info->created_user_id = $user->id;
            $order_additional_info->updated_user_id = $user->id;
            $order_additional_info->save();

            // ファイル保存
            $files = array_values(array_filter($req->order_additional_attachments));
            foreach ($files as &$file) {
                $root_folder = substr($file['file_path'], 0, strlen('order_additional_attachments'));

                // ファイルアップロードされていないものだけを保存
                if (!$file['file_path'] || $root_folder !== 'order_additional_attachments') {
                    $upload_data = [
                        'order_additional_info_id' => $order_additional_info->id,
                        'user_id' => $user->id,
                        'file' => $file,
                    ];
                    $order_additional_info->order_additional_attachments = $this->uploadFile($upload_data, 'base64');
                }
            }

            \DB::commit();

            return response()->json([
                'status' => 200,
                'order_additional_info' => $order_additional_info,
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);

            $message = $e->getMessage();
            if ($e->getMessage() === 'no admin permission') {
                $message = 'no_admin_permission';
            }
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $message,
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
     * [POST]案件補足情報のデータ更新API
     *
     * @param Request $req
     * @return JsonResponse
     */
    public function update(Request $req): JsonResponse
    {
        $user = \Auth::user();

        $order_additional_info_id = $req->order_additional_info_id;
        // DB登録
        \DB::beginTransaction();
        try {
            // 排他制御--------------------------------------
            $is_admin = OrdersAdministrator::where('order_id', $req->order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;
            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }
            // 排他制御--------------------------------------

            $order_additional_info = OrderAdditionalInfo::find($order_additional_info_id);
            $order_additional_info->content = $req->content;
            $order_additional_info->is_open_to_client = $req->is_open_to_client;
            $order_additional_info->updated_user_id = $user->id;
            $order_additional_info->save();

            // ファイル保存
            $files = array_values(array_filter($req->order_additional_attachments));
            foreach ($files as &$file) {
                $root_folder = substr($file['file_path'], 0, strlen('order_additional_info_attachments'));

                // ファイルアップロードされていないものだけを保存
                if (!$file['file_path'] || $root_folder !== 'order_additional_info_attachments') {
                    $upload_data = array(
                        'order_additional_info_id' => $order_additional_info->id,
                        'user_id' => $user->id,
                        'file' => $file
                    );
                    $order_additional_info->order_additional_attachments = $this->uploadFile($upload_data, 'base64');
                }
            }

            \DB::commit();

            return response()->json([
                'status' => 200,
                'order_additional_info' => $order_additional_info,
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);

            $message = $e->getMessage();
            if ($e->getMessage() === 'no admin permission') {
                $message = 'no_admin_permission';
            }
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $message,
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
     * [POST]案件補足情報のデータ削除API
     *
     * @param Request $req
     * @return JsonResponse
     */
    public function delete(Request $req): JsonResponse
    {
        $user = \Auth::user();

        \DB::beginTransaction();
        try {
            // 排他制御--------------------------------------
            $is_admin = OrdersAdministrator::where('order_id', $req->order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;
            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }
            // 排他制御--------------------------------------

            $order_additional_info = OrderAdditionalInfo::find($req->order_additional_info_id);

            // 自分のデータ以外は削除不可とする
            if ($user->id != $order_additional_info->created_user_id) {
                throw new \Exception('user is not matched.');
            }

            $order_additional_info->is_deleted = \Config::get('const.FLG.ACTIVE');
            $order_additional_info->updated_user_id = $user->id;
            $order_additional_info->save();

            // 案件補足情報ファイルもdelete
            $order_additional_attachments = $order_additional_info->orderAdditionalAttachments;
            foreach ($order_additional_attachments as $order_additional_attachment) {
                $order_additional_attachment->is_deleted = \Config::get('const.FLG.ACTIVE');
                $order_additional_attachment->updated_user_id = $user->id;
                $order_additional_attachment->save();
            }

            \DB::commit();

            return response()->json([
                'status' => 200,
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);

            $message = $e->getMessage();
            if ($e->getMessage() === 'no admin permission') {
                $message = 'no_admin_permission';
            }
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $message,
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
     * [POST]案件補足情報ファイルのデータ削除API
     *
     * @param Request $req
     * @return JsonResponse
     */
    public function deleteAttachment(Request $req): JsonResponse
    {
        $user = \Auth::user();

        \DB::beginTransaction();
        try {
            // 排他制御--------------------------------------
            $is_admin = OrdersAdministrator::where('order_id', $req->order_id)
                ->where('user_id', $user->id)
                ->count() !== 0;
            if (!$is_admin) {
                throw new ExclusiveException("no admin permission");
            }
            // 排他制御--------------------------------------

            $order_additional_attachment = OrderAdditionalAttachment::find($req->order_additional_attachment_id);

            // 自分のデータ以外は削除不可とする
            if ($user->id != $order_additional_attachment->created_user_id) {
                throw new \Exception('user is not matched.');
            }

            $order_additional_attachment->is_deleted = \Config::get('const.FLG.ACTIVE');
            $order_additional_attachment->updated_user_id = $user->id;
            $order_additional_attachment->save();

            \DB::commit();

            return response()->json([
                'status' => 200,
            ]);
        } catch (ExclusiveException $e) {
            \DB::rollback();
            report($e);

            $message = $e->getMessage();
            if ($e->getMessage() === 'no admin permission') {
                $message = 'no_admin_permission';
            }
            return response()->json([
                'status' => $e->getCode(),
                'error' => get_class($e),
                'message' => $message,
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
     * 添付ファイルのアップロード処理
     *
     * @param array $data
     * @param string $type
     * @return OrderAdditionalAttachment
     * @throws \Exception
     */
    private function uploadFile($data, $type = 'content')
    {
        $user_id = $data['user_id'];
        $order_additional_info_id = $data['order_additional_info_id'];
        // ファイル情報
        $file = $data['file'];
        $file_name = $file['file_name'];
        $file_path = 'order_additional_attachments/'. $order_additional_info_id .'/'. Carbon::now()->format('Ymd') .'/'. md5(strval(time())) .'/'. $file_name;
        $file_contents = '';

        switch ($type) {
            case 'base64':
                list(, $fileData) = explode(';', $file['file_data']);
                list(, $fileData) = explode(',', $fileData);
                $file_contents = base64_decode($fileData);
                break;
            case 'content':
                $file_contents = $file['file_data'];
                break;
            default:
                throw new \Exception('type is not matched: '. $type);
        }

        // 登録
        // ファイルのアップロード
        /** @var OrderAdditionalAttachment */
        $order_additional_attachment = Uploader::tryUploadAndSave(
            $file_contents,
            $file_path,
            OrderAdditionalAttachment::class,
            [
                'order_additional_info_id' => $order_additional_info_id,
                'created_user_id' => $user_id,
                'updated_user_id' => $user_id,
            ]
        );

        return $order_additional_attachment;
    }
}
