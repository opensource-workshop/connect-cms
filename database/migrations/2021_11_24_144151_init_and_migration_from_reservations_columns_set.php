<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User\Reservations\Reservation;
use App\Models\User\Reservations\ReservationsColumn;
use App\Models\User\Reservations\ReservationsColumnsSelect;
use App\Models\User\Reservations\ReservationsColumnsSet;
use App\Models\User\Reservations\ReservationsFacility;

use App\Enums\NotShowType;
use App\Enums\Required;
use App\Enums\ReservationColumnType;

class InitAndMigrationFromReservationsColumnsSet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 各テーブルの reservations_id は今後削除する見込みのため、マイグレーションが走り終わったらseederではなく、マイグレーションで移行PGを作成する。
        // ReservationsColumn.reservations_id
        // ReservationsColumnsSelect.reservations_id
        // ReservationsFacility.reservations_id

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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
