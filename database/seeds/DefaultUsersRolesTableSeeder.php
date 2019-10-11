<?php

use Illuminate\Database\Seeder;

class DefaultUsersRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('users_roles')->count() == 0) {
            DB::table('users_roles')->insert(
                [
                    /** 初期管理者用（記事関連の権限） */
                    ['users_id'=>'1','target'=>'base','role_name'=>'role_article_admin','role_value'=>'1','created_at' => date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')],
                    ['users_id'=>'1','target'=>'base','role_name'=>'role_arrangement','role_value'=>'1','created_at' => date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')],
                    ['users_id'=>'1','target'=>'base','role_name'=>'role_reporter','role_value'=>'1','created_at' => date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')],
                    ['users_id'=>'1','target'=>'base','role_name'=>'role_approval','role_value'=>'1','created_at' => date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')],
                    ['users_id'=>'1','target'=>'base','role_name'=>'role_article','role_value'=>'1','created_at' => date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')],
                    /** 初期管理者用（管理権限） */
                    ['users_id'=>'1','target'=>'manage','role_name'=>'admin_system','role_value'=>'1','created_at' => date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')],
                    ['users_id'=>'1','target'=>'manage','role_name'=>'admin_page','role_value'=>'1','created_at' => date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')],
                    ['users_id'=>'1','target'=>'manage','role_name'=>'admin_site','role_value'=>'1','created_at' => date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')],
                    ['users_id'=>'1','target'=>'manage','role_name'=>'admin_user','role_value'=>'1','created_at' => date('Y-m-d H:i:s'),'updated_at' => date('Y-m-d H:i:s')],
                ]
            );
        }
    }
}
