<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * フォトアルバムのフレーム設定項目
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
 * @package Controller
 */
final class PhotoalbumFrameConfig extends EnumsBase
{
    // 定数メンバ
    //const view_count = 'view_count';
    const posted_at = 'posted_at';
    //const shooting_at = 'shooting_at';
    const sort_folder = 'sort_folder';
    const sort_file = 'sort_file';
    const download = 'download';
    const embed_code = 'embed_code';

    // key/valueの連想配列
    const enum = [
        //self::view_count => '1ページの表示件数',
        self::posted_at => '投稿日',
        //self::shooting_at => '撮影日',
        self::sort_folder => 'アルバム並び順',
        self::sort_file => '写真並び順',
        self::download => 'ダウンロード',
        self::embed_code => '動画埋め込みコード',
    ];
}
