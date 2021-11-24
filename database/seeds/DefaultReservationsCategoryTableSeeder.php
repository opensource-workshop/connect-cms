<?php

use Illuminate\Database\Seeder;

use App\Models\User\Reservations\ReservationsCategory;

class DefaultReservationsCategoryTableSeeder extends Seeder
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
    }
}
