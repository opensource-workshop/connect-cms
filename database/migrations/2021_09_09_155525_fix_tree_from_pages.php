<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Common\Page;

class FixTreeFromPages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // bugfix: 初期インストール時にDefaultPagesTableSeederで作成される、homeページのtree構造設定値（_lft, _rgtが両方0）が間違って設定されていたため、fixTree()で自動修正する
        Page::fixTree();
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
