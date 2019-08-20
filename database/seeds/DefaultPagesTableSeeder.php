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
                        'page_name'=>'home',
                        'permanent_link'=>'/',
                        'base_display_flag'=>'1',
                        '_lft'=>'0',
                        '_rgt'=>'0',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                ]
            );
        }
    }
}
