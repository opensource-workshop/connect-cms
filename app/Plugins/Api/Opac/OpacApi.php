<?php

namespace App\Plugins\Api\Opac;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\User\Opacs\OpacsBooks;
use App\User;

use App\Traits\ConnectCommonTrait;
use App\Plugins\Api\ApiPluginBase;

/**
 * Opac関係APIクラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ関係API
 * @package Contoroller
 */
class OpacApi extends ApiPluginBase
{
    use ConnectCommonTrait;

    /**
     *  書籍情報表示
     */
    public function book($request, $opac_id, $key_column, $key_value)
    {
        // API 共通チェック
        $ret = $this->apiCallCheck($request);
        if (!empty($ret['code'])) {
            return $this->encodeJson($ret, $request);
        }

        // パラメータチェック（キー項目）
        if ($key_column == 'barcode' || $key_column == 'isbn') {
            // キーに指定してもOKな項目。続きへ。
        } else {
            $ret = array('code' => 400, 'message' => '指定された項目が正しくありません。');
            return $this->encodeJson($ret, $request);
        }

        // 返すデータ取得
        $opacs_books = OpacsBooks::where('opacs_id', $opac_id)->where($key_column, $key_value)->first();
        if (empty($opacs_books)) {
            $ret = array('code' => 404, 'message' => '指定された書籍が存在しません。');
            return $this->encodeJson($ret, $request);
        }

        $ret = array('code' => 200,
                     'message'          => '',
                     'title'            => $opacs_books->title,
                     'subtitle'         => $opacs_books->subtitle,
                     'creator'          => $opacs_books->creator,
                     'piblisher'        => $opacs_books->piblisher,
                     'lend_flag'        => $opacs_books->lend_flag,
                     'publication_year' => $opacs_books->publication_year,
                     'lent_flag'        => $opacs_books->lent_flag);
        return $this->encodeJson($ret, $request);
    }
}
