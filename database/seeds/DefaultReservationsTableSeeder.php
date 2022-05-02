<?php

use Illuminate\Database\Seeder;

use App\Models\User\Reservations\ReservationsCategory;
use App\Models\User\Reservations\ReservationsColumn;
use App\Models\User\Reservations\ReservationsColumnsSet;

use App\Enums\NotShowType;
use App\Enums\ReservationColumnType;
use App\Enums\Required;

/**
 * 施設管理系のSeeder
 *
 * 基本は、下記マイグレーションでデータ投入します。
 * database\migrations\2021_12_03_151901_init_and_migration_from_reservations_columns_set_and_reservations_category.php
 * @see InitAndMigrationFromReservationsColumnsSetAndReservationsCategory （上記マイグレーション）
 *
 * もしDBの全データ削除（Truncate）した場合等に、基本データを投入するためのSeederです。
 */
class DefaultReservationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 施設カテゴリ
        if (ReservationsCategory::count() == 0) {
            ReservationsCategory::insert([
                'id' => 1,
                'category' => 'カテゴリなし',
                'display_sequence' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // 項目セット
        if (ReservationsColumnsSet::count() == 0) {
            /* 初期データ登録
            ----------------------------------------------*/

            // 項目セット登録
            $columns_set_basic = ReservationsColumnsSet::create([
                'name'             => '基本',
                'display_sequence' => 1,
            ]);

            // 項目設定にセット
            // 件名
            $column = new ReservationsColumn([
                'columns_set_id'   => $columns_set_basic->id,
                'column_type'      => ReservationColumnType::text,
                'column_name'      => '件名',
                'required'         => Required::on,
                'hide_flag'        => NotShowType::show,
                'title_flag'       => 1,
                'display_sequence' => 1,
            ]);
            $column->save();

            // 登録者（表示のみ）
            $column = new ReservationsColumn([
                'columns_set_id'   => $columns_set_basic->id,
                'column_type'      => ReservationColumnType::created_name,
                'column_name'      => '登録者',
                'required'         => Required::off,
                'hide_flag'        => NotShowType::show,
                'title_flag'       => 0,
                'display_sequence' => 2,
            ]);
            $column->save();

            // 更新日（表示のみ）
            $column = new ReservationsColumn([
                'columns_set_id'   => $columns_set_basic->id,
                'column_type'      => ReservationColumnType::updated,
                'column_name'      => '更新日',
                'required'         => Required::off,
                'hide_flag'        => NotShowType::show,
                'title_flag'       => 0,
                'display_sequence' => 3,
            ]);
            $column->save();
        }

    }
}
