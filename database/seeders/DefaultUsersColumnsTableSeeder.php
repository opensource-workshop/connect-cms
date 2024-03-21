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

                // 固定項目を項目順の頭に追加して、追加項目はその後の表示順に更新する事で、現在の並び順を再現する。

                $users_columns = UsersColumns::where('columns_set_id', $columns_set->id)->orderBy('display_sequence')->get();

                // UsersColumnsSet登録時のUsersColumns初期登録
                UsersColumns::initInsertForRegistUsersColumnsSet($columns_set->id);

                foreach ($users_columns as $users_column) {
                    $users_column->display_sequence = $users_column->display_sequence + 5;
                    $users_column->save();
                }
            }
        }
    }
}
