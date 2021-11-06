<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 新着情報のフレーム設定項目
 */
final class WhatsnewFrameConfig extends EnumsBase
{
    // 定数メンバ
    const thumbnail = 'thumbnail';
    const thumbnail_size = 'thumbnail_size';
    const border = 'border';
    const post_detail = 'post_detail';
    const post_detail_length = 'post_detail_length';

    // key/valueの連想配列
    const enum = [
        self::thumbnail => 'サムネイル画像',
        self::thumbnail_size => '画像サイズ',
        self::border => '記事間の罫線',
        self::post_detail => '記事本文',
        self::post_detail_length => '記事本文の文字数',
    ];
}