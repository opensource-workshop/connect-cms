<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Core\UsersColumnsSet;

/**
 * UsersColumnsSet系のSeeder
 *
 * 基本は、下記マイグレーションでデータ投入します。
 * database\migrations\2023_07_27_135430_init_and_migration_from_users_columns_sets.php
 *
 * もしDBの全データ削除（Truncate）した場合等に、基本データを投入するためのSeederです。
 */
class DefaultUsersColumnsSetTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 項目セット
        if (UsersColumnsSet::count() == 0) {
            /* 初期データ登録
            ----------------------------------------------*/

            // 項目セット登録
            // 項目セットのid=1は、基本データとして消せないように対応する。（idはfillable でガードされ、セットできないが、0件からのcreateのため、結果的にid=1になる）
            $columns_set_basic = UsersColumnsSet::create([
                'id'               => 1,
                'name'             => '基本',
                'display_sequence' => 1,
            ]);
        }

    }
}
