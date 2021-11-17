<?php

use Illuminate\Database\Seeder;

use App\Models\User\Reservations\ReservationsCategory;

class DefaultReservationsCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (ReservationsCategory::count() == 0) {
            // 施設カテゴリのid=1は、カテゴリなしの特別なデータ。消せないように対応する。
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
