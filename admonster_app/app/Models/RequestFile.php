<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class RequestFile extends Model
{
    use \App\Services\Traits\SearchFormTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'step_id',
        'create_status',
        'err_description',
        'name',
        'file_path',
        'size',
        'width',
        'height',
        'file_type',
        'lf_code',
        'delimiter',
        'is_deleted',
        'created_at',
        'created_user_id',
        'updated_at',
        'updated_user_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static function getList(string $user_id, array $form)
    {
        $sub_query_business_flows = \DB::table('steps')
            ->select(
                'steps.id as step_id',
                'business_flows.business_id as business_id'
            )
            ->join('business_flows', 'steps.id', '=', 'business_flows.step_id')
            ->groupBy('business_flows.business_id', 'steps.id');

        $query = DB::table('request_files')
            ->select(
                'request_files.id as imported_file_id',
                'request_files.name as imported_file_name',
                'businesses.name as business_name',
                'request_files.created_user_id as importer_id',
                'request_files.created_at as created_at',
                DB::raw('count(requests.id) as imported_count'),
                DB::raw('count(distinct case'
                    .' when requests.is_deleted = '.\Config::get('const.FLG.ACTIVE').' and requests.status = '.\Config::get('const.REQUEST_STATUS.FINISH')
                    .' then requests.id end) as excluded_count'),
                DB::raw('count(distinct case'
                    .' when requests.is_deleted = '.\Config::get('const.FLG.INACTIVE').' and requests.status = '.\Config::get('const.REQUEST_STATUS.FINISH')
                    .' then requests.id end) as completed_count'),
                DB::raw('count(distinct case'
                    .' when requests.is_deleted = '.\Config::get('const.FLG.INACTIVE').' and requests.status = '.\Config::get('const.REQUEST_STATUS.DOING')
                    .' then requests.id end) as wip_count')
            )
            ->join(DB::raw('('. $sub_query_business_flows->toSql() .') as business_flows'), 'request_files.step_id', '=', 'business_flows.step_id')
            ->join('businesses', 'business_flows.business_id', '=', 'businesses.id')
            ->join('businesses_admin', 'businesses.id', '=', 'businesses_admin.business_id')
            ->join('request_work_files', 'request_files.id', '=', 'request_work_files.request_file_id')
            ->join('request_works', 'request_work_files.request_work_id', '=', 'request_works.id')
            ->join('requests', 'request_works.request_id', '=', 'requests.id')
            ->join('users', 'request_files.created_user_id', '=', 'users.id')
            ->where('businesses_admin.user_id', $user_id)
            ->groupBy('businesses.id', 'request_files.id');

        // ファイル名
        if ($form['imported_file_name']) {
            $query = $query->where('request_files.name', 'LIKE', '%'.$form['imported_file_name'].'%');
        }

        // ファイルID
        if ($form['imported_file_id']) {
            //全角数値の場合は半角数値へ変換
            $query = $query->where('request_files.id', mb_convert_kana($form['imported_file_id'], "n"));
        }

        // ファイル取込日時
        if ($form['from'] && $form['to']) {
            $query = $query->whereBetween('request_files.created_at', [$form['from'], $form['to']]);
        } elseif ($form['from'] && !$form['to']) {
            $query = $query->where('request_files.created_at', '>=', $form['from']);
        } elseif (!$form['from'] && $form['to']) {
            $query = $query->where('request_files.created_at', '<=', $form['to']);
        }

        // ステータス
        if ($form['status']) {
            if ($form['status'] == config('const.IMPORTED_FILE_STATUS.ALL')) {
            } elseif ($form['status'] == config('const.IMPORTED_FILE_STATUS.DOING')) {
                $query = $query->havingRaw('count(requests.is_deleted = '.\Config::get('const.FLG.INACTIVE').' and requests.status = '.\Config::get('const.REQUEST_STATUS.DOING').' or null) > 0');
            } elseif ($form['status'] == config('const.IMPORTED_FILE_STATUS.FINISH')) {
                $query = $query->havingRaw('count(requests.is_deleted = '.\Config::get('const.FLG.INACTIVE').' and requests.status = '.\Config::get('const.REQUEST_STATUS.DOING').' or null) = 0');
            }
        }

        // 業務名
        if ($form['business_name']) {
            // プレフィックス判定
            $prefix = substr($form['business_name'], 0, 1);
            if (config('const.MASTER_ID_PREFIX.TABLE_COLUMN.'. $prefix)) {
                // ID検索
                $query = self::getMasterIdSearchQuery($query, $form['business_name']);
            } else {
                $query = $query->where('businesses.name', 'LIKE', '%'.$form['business_name'].'%');
            }
        }

        // 取込担当者
        if ($form['importer']) {
            $query = $query->where('users.name', 'LIKE', '%'.$form['importer'].'%');
        }

        $form['sort_by'] = $form['sort_by'] === 'importer_id_no_image' ? 'importer_id': $form['sort_by'];

        // sort
        if ($form['sort_by']) {
            $query = $query->orderBy($form['sort_by'], $form['descending']);
        }

        $query = $query->orderBy('request_files.id', 'asc');

        $request_files = $query->paginate($form['rows_per_page'], ['*'], 'page', (int) $form['page']);

        return $request_files;
    }

    // 指定のユーザーが取込んだ最新n件を取得
    public static function getLatestListByUserId(string $user_id, $num)
    {
        $sub_query_business_flows = \DB::table('steps')
            ->select(
                'steps.id as step_id',
                'business_flows.business_id as business_id'
            )
            ->join('business_flows', 'steps.id', '=', 'business_flows.step_id')
            ->groupBy('business_flows.business_id', 'steps.id');

        $latest_request_files = self::select(
            'request_files.id as request_file_id',
            'request_files.name as request_file_name',
            'request_files.created_at as request_file_created_at',
            'businesses.name as business_name',
            'steps.name as step_name'
        )
            ->join(DB::raw('('. $sub_query_business_flows->toSql() .') as business_flows'), 'request_files.step_id', '=', 'business_flows.step_id')
            ->join('businesses', 'business_flows.business_id', '=', 'businesses.id')
            ->join('steps', 'request_files.step_id', '=', 'steps.id')
            ->where('request_files.is_deleted', \Config::get('const.FLG.INACTIVE'))
            ->where('request_files.created_user_id', $user_id)
            ->latest('request_files.created_at')
            ->limit($num)
            ->get();

        return $latest_request_files;
    }

    public static function getWithPivotByRequestWorkId(string $request_work_id)
    {
        $query = self::join('request_work_files', 'request_files.id', '=', 'request_work_files.request_file_id')
            ->where('request_work_files.request_work_id', $request_work_id);

        $request_file = $query->first();

        return $request_file;
    }

    /* -------------------- relations ------------------------- */

    public function requestWorks()
    {
        return $this->belongsToMany(RequestWork::class, 'request_work_files')
            ->withPivot('content', 'row_no')
            ->withTimestamps();
    }
}
