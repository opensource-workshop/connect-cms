<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

/**
 * Connect-CMS 独自バリデーション
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 */
class ValidatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * WYSIWYG 最大サイズのバリデーション
         *
         * @return bool
         */
        Validator::extend(
            'wysiwyg_max',
            function ($attribute, $value, $parameters, $validator) {
                // 65,535 バイトまではOK
                return (strlen($value) > config('connect.WYSIWYG_MAX_BYTE')) ? false : true;
            }
        );
    }
}
