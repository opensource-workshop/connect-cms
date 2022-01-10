<?php

namespace App\Models\User\Opacs;

use Illuminate\Database\Eloquent\Model;

class OpacsBooks extends Model
{
    // 日付型の場合、$dates にカラムを指定しておく。
    protected $dates = ['accept_date', 'storage_life', 'remove_date', 'last_lending_date', 'posted_at'];

    // 更新する項目の定義
    protected $fillable = [
        'barcode',
        'opacs_id',
        'isbn',
        'marc',
        'title',
        'title_read',
        'subtitle',
        'series',
        'ndc',
        'creator',
        'publisher',
        'publication_year',
        'class',
        'size',
        'page_number',
        'type',
        'shelf',
        'lend_flag',
        'accept_flag',
        'accept_date',
        'accept_price',
        'storage_life',
        'remove_flag',
        'remove_date',
        'possession',
        'library',
        'last_lending_date',
        'total_lends',
    ];
}
