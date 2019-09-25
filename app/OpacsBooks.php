<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpacsBooks extends Model
{
    // 日付型の場合、$dates にカラムを指定しておく。
    //protected $dates = ['posted_at'];
    protected $dates = ['accept_date', 'storage_life', 'remove_date', 'last_lending_date', 'posted_at'];
}
