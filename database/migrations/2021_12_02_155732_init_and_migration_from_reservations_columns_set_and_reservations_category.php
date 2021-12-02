<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User\Reservations\Reservation;
use App\Models\User\Reservations\ReservationsCategory;
use App\Models\User\Reservations\ReservationsChoiceCategory;
use App\Models\User\Reservations\ReservationsColumn;
use App\Models\User\Reservations\ReservationsColumnsSelect;
use App\Models\User\Reservations\ReservationsColumnsSet;
use App\Models\User\Reservations\ReservationsFacility;

use App\Enums\NotShowType;
use App\Enums\PermissionType;
use App\Enums\Required;
use App\Enums\ReservationColumnType;
use App\Enums\ShowType;

class InitAndMigrationFromReservationsColumnsSetAndReservationsCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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
            $columns_set_basic = ReservationsColumnsSet::create([
                'name'             => '基本',
                'display_sequence' => 1,
            ]);

            // 項目設定にセット
            // 件名
            $column = ReservationsColumn::create([
                'reservations_id'  => 0,
                'columns_set_id'   => $columns_set_basic->id,
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
                'columns_set_id'   => $columns_set_basic->id,
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
                'columns_set_id'   => $columns_set_basic->id,
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

                if (ReservationsColumn::where('reservations_id', $reservation->id)->count() == 0) {
                    // カラムなしバケツは、基本セット使用
                    $columns_set = $columns_set_basic;
                } else {
                    // カラムありバケツのみ、項目セット作成
                    $columns_set = ReservationsColumnsSet::create([
                        'name'             => $reservation->reservation_name . 'セット',
                        'display_sequence' => $i + 2,
                    ]);
                }

                // 施設カテゴリ登録
                // - 移行後は、バケツから施設カテゴリを選んで、そのカテゴリの施設を表示するようにする。
                // - 移行で現在と同じ状態にするには、バケツ名と同じカテゴリを作成して、それを表示する。
                $reservations_category = ReservationsCategory::create([
                    'category'         => $reservation->reservation_name . 'カテゴリ',
                    'display_sequence' => $i + 2,
                ]);

                // 項目設定にセット
                ReservationsColumn::where('reservations_id', $reservation->id)->update(['columns_set_id' => $columns_set->id]);

                // 項目の選択肢にセット
                ReservationsColumnsSelect::where('reservations_id', $reservation->id)->update(['columns_set_id' => $columns_set->id]);

                // 施設にセット
                ReservationsFacility::where('reservations_id', $reservation->id)->update([
                    'columns_set_id' => $columns_set->id,
                    'reservations_categories_id' => $reservations_category->id,
                    'is_allow_duplicate' => PermissionType::allowed,    // 重複許可はいままで可能だったため、重複許可した状態で移行する。
                ]);

                // バケツで使うカテゴリ配下の施設
                $columns_set = ReservationsChoiceCategory::create([
                    'reservations_id'            => $reservation->id,
                    'reservations_categories_id' => $reservations_category->id,
                    'view_flag'                  => ShowType::show,
                    'display_sequence'           => $i + 2,
                ]);

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
