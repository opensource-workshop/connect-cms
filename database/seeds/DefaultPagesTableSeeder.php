<?php

use Illuminate\Database\Seeder;

class DefaultPagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('pages')->count() == 0) {
            DB::table('pages')->insert(
                [
                    /** 初期ページ */
                    [
                        'page_name' => 'home',
                        'permanent_link' => '/',
                        'base_display_flag' => '1',
                        // bugfix: tree構造で _lft, _rgtが両方0は間違った値で、上から2番目のページを上に移動するとNode must exists.エラーになるため修正
                        // '_lft'=>'0',
                        // '_rgt'=>'0',
                        '_lft' => '1',
                        '_rgt' => '2',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                ]
            );
        }
    }
}
