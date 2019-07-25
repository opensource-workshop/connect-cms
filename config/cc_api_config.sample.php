<?php

// 本来はDB管理
$sites = array();
$sites['A001']['salt'] = 'xxxxxx';
$sites['A001']['url']  = 'https://xxxxxxxxx/';

$sites['A002']['salt'] = 'xxxxxx';
$sites['A002']['url']  = 'http://xxxxxxxxx/';

return [

    /*
    |--------------------------------------------------------------------------
    | API Config
    |--------------------------------------------------------------------------
    |
    | Connect-CMS API Config
    |
    */

    'CC_API_CONFIGS' => $sites
];
