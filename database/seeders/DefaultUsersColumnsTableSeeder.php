<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Enums\UserColumnType;
use App\Models\Core\UsersColumns;
use App\Models\Core\UsersColumnsSet;

/**
 * UsersColumnsSet系のSeeder
 *
 * 基本は、下記マイグレーションでデータ投入します。
 * database\migrations\2023_08_10_114737_migration_fixed_column_from_users_columns.php
 * DefaultUsersColumnsSetTableSeeder の後に実行する必要があります。
 *
 * もしDBの全データ削除（Truncate）した場合等に、基本データを投入するためのSeederです。
 */
class DefaultUsersColumnsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $columns_sets = UsersColumnsSet::get();
        foreach ($columns_sets as $columns_set) {
            if (UsersColumns::where('columns_set_id', $columns_set->id)->where('column_type', UserColumnType::user_name)->count() == 0) {
                UsersColumns::insert([
                    [
                        'columns_set_id'        => $columns_set->id,
                        'column_type'           => UserColumnType::user_name,
                        'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::user_name),
                        'is_fixed_column'       => 1,
                        'is_show_auto_regist'   => 1,
                        'is_show_my_page'       => 1,
                        'is_edit_my_page'       => 0,
                        'required'              => 1,
                        'display_sequence'      => 1,
                    ],
                    [
                        'columns_set_id'        => $columns_set->id,
                        'column_type'           => UserColumnType::login_id,
                        'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::login_id),
                        'is_fixed_column'       => 1,                           // (画面からいじれない項目)
                        'is_show_auto_regist'   => 1,
                        'is_show_my_page'       => 1,
                        'is_edit_my_page'       => 0,
                        'required'              => 1,                           // 設定するけど is_fixed_column=1 はおそらく参照しない
                        'display_sequence'      => 2,
                    ],
                    [
                        'columns_set_id'        => $columns_set->id,
                        'column_type'           => UserColumnType::user_email,
                        'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::user_email),
                        'is_fixed_column'       => 1,
                        'is_show_auto_regist'   => 1,
                        'is_show_my_page'       => 1,
                        'is_edit_my_page'       => 1,
                        'required'              => 1,
                        'display_sequence'      => 3,
                    ],
                    [
                        'columns_set_id'        => $columns_set->id,
                        'column_type'           => UserColumnType::user_password,
                        'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::user_password),
                        'is_fixed_column'       => 1,
                        'is_show_auto_regist'   => 1,
                        'is_show_my_page'       => 0,
                        'is_edit_my_page'       => 1,
                        'required'              => 1,
                        'display_sequence'      => 4,
                    ],
                    [
                        'columns_set_id'        => $columns_set->id,
                        'column_type'           => UserColumnType::created_at,
                        'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::created_at),
                        'is_fixed_column'       => 0,
                        'is_show_auto_regist'   => 0,
                        'is_show_my_page'       => 1,
                        'is_edit_my_page'       => 0,
                        'required'              => 0,
                        'display_sequence'      => 5,
                    ],
                ]);
            }
        }
    }
}
