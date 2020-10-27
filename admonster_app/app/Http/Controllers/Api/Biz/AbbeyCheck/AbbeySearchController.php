<?php

namespace App\Http\Controllers\Api\Biz\AbbeyCheck;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Abbey;

class AbbeySearchController extends Controller
{
    /**
     * データベース検索
     */
    public function index(Request $req)
    {

        try {
            $result = [];
            if (mb_strlen($req->search_word) > 0) {
                $search_word = $this->createNgramString($req->search_word);
                // 一文字検索で記号が入っていた場合エラーが起こる
                $result = Abbey::searchBySearchWord($search_word);
            } else {
                $result = Abbey::getAllAbbeyList();
            }

            foreach ($result as $result_key => &$row) {
                foreach ($row as $row_key => &$value) {
                    $value = nl2br(e($value));
                }
            }
            unset($row);
            unset($value);

            return response()->json([
                'result' => 'success',
                'abbey_list' => $result
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'result' => 'success',
                'abbey_list' => []
            ]);
        }
    }

    /**
     * 検索用の文字列を作成
     */
    private function createNgramString($keyword)
    {
        $keyword_length = mb_strlen($keyword);

        $ngram_byte_length = 2; // bi-gram

        if ($keyword_length < $ngram_byte_length) {
            // 文字数が分割数より少ない場合、前方一致検索にするため * をつけて返す
            return sprintf('%s*', str_replace('"', '""', $keyword));
        }

        $grams = [];

        // ngarmの数だけずれた文字列の配列を作成
        for ($start = 0; $start <= ($keyword_length - $ngram_byte_length); $start += 1) {
            $target = mb_substr($keyword, $start, $ngram_byte_length);
            $grams[] = sprintf('"%s"', str_replace('"', '""', $target));// OR
            // $grams[] = "+{$target}";// 必ず含めるようにする場合はこちら
            // "*D+"を配列の先頭入れるとAND検索になる
        }

        // フレーズ検索
        return sprintf('%s', implode(' ', $grams));
    }
}
