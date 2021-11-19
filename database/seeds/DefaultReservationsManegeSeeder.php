<?php

use Illuminate\Database\Seeder;

use App\Models\User\Reservations\Reservation;
use App\Models\User\Reservations\ReservationsCategory;
use App\Models\User\Reservations\ReservationsColumn;
use App\Models\User\Reservations\ReservationsColumnsSelect;
use App\Models\User\Reservations\ReservationsColumnsSet;
use App\Models\User\Reservations\ReservationsFacility;

use App\Enums\NotShowType;
use App\Enums\Required;
use App\Enums\ReservationColumnType;

class DefaultReservationsManegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 施設管理系のSeeder
     *
     * @return void
     */
    public function run()
    {
        // 施設カテゴリ
        if (ReservationsCategory::count() == 0) {
            // 施設カテゴリのid=1は、カテゴリなしで特別なデータ。消せないように対応する。
            ReservationsCategory::insert([
                'id' => 1,
                'category' => 'カテゴリなし',
                'display_sequence' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // 項目セット
        // 項目セット（reservations_columns_sets）が無ければ reservations から移し替え
        if (ReservationsColumnsSet::count() == 0) {
            /* 初期データ登録
            ----------------------------------------------*/

            // 項目セット登録
            $columns_set = ReservationsColumnsSet::create([
                'name'             => '基本',
                'display_sequence' => 1,
            ]);

            // 項目設定にセット
            // 件名
            $column = ReservationsColumn::create([
                'reservations_id'  => 0,
                'columns_set_id'   => $columns_set->id,
                'column_type'      => ReservationColumnType::text,
                'column_name'      => '件名',
                'required'         => Required::on,
                'hide_flag'        => NotShowType::show,
                'title_flag'       => 1,
                'display_sequence' => 1,
            ]);
            // 登録者（表示のみ）
            $column = ReservationsColumn::create([
                'reservations_id'  => 0,
                'columns_set_id'   => $columns_set->id,
                'column_type'      => ReservationColumnType::created_name,
                'column_name'      => '登録者',
                'required'         => Required::off,
                'hide_flag'        => NotShowType::show,
                'title_flag'       => 0,
                'display_sequence' => 2,
            ]);
            // 更新日（表示のみ）
            $column = ReservationsColumn::create([
                'reservations_id'  => 0,
                'columns_set_id'   => $columns_set->id,
                'column_type'      => ReservationColumnType::updated,
                'column_name'      => '更新日',
                'required'         => Required::off,
                'hide_flag'        => NotShowType::show,
                'title_flag'       => 0,
                'display_sequence' => 3,
            ]);

            /* 移行
            ----------------------------------------------*/

            $reservations = Reservation::get();
            foreach ($reservations as $i => $reservation) {

                // カラムありバケツのみ、項目セット作成
                if (ReservationsColumn::where('reservations_id', $reservation->id)->count() == 0) {
                    continue;
                }

                // 項目セット登録
                $columns_set = ReservationsColumnsSet::create([
                    'name'             => $reservation->reservation_name,
                    'display_sequence' => $i + 2,
                ]);

                // 項目設定にセット
                ReservationsColumn::where('reservations_id', $reservation->id)->update(['columns_set_id' => $columns_set->id]);

                // 項目の選択肢にセット
                ReservationsColumnsSelect::where('reservations_id', $reservation->id)->update(['columns_set_id' => $columns_set->id]);

                // 施設にセット
                ReservationsFacility::where('reservations_id', $reservation->id)->update(['columns_set_id' => $columns_set->id]);

                // [TODO]
                // 後でdrop: reservations_columns.reservations_id
                // 後でdrop: reservations_columns_selects.reservations_id
            }
        }

    }
}
