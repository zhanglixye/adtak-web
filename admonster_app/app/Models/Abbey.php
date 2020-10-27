<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Abbey extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'id',
        'abbey_id',
        'specification',
        'specification_2',
        'purpose',
        'width',
        'hight',
        'file_size',
        'file_size_unit',
        'file_format',
        'total_bit_rate',
        'animation',
        'alt_text',
        'link',
        'target_Loudness',
        'text',
        'title_text',
        'branding_text',
        'search_full_text',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * 検索wordが
     */
    public static function searchBySearchWord($search_word)
    {
        $query = DB::table('abbey')
        ->select('*')
        ->whereRaw('MATCH(`search_full_text`,`search_full_text_2`) AGAINST(:search_word IN BOOLEAN MODE)', ['search_word' => $search_word]);
        return $query->get();
    }

    public static function searchByAbbeyId($id)
    {
        $query = DB::table('abbey')
        ->select('*')
        ->whereIn('abbey_id', $id);
        return $query->get();
    }

    /**
     * 検索wordが
     */
    public static function getAllAbbeyList()
    {
        $query = DB::table('abbey')
        ->select('*');
        return $query->get();
    }

    /* -------------------- relations ------------------------- */

    public function requestWork()
    {
        return $this->belongsTo(RequestWork::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }
}
