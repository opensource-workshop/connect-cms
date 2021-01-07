<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Core\Configs;

class InsertUseMypageConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // マイページの使用. DBになければinsert, あればupdate(基本新しい設定のため、inserのみ動作の想定)
        $configs = Configs::updateOrCreate(
            ['name' => 'use_mypage'],
            [
                'category' => 'general',
                'value' => 1
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
