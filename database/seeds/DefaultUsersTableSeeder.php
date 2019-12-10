<?php

use Illuminate\Database\Seeder;

class DefaultUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('users')->count() == 0) {
            DB::table('users')->insert(
                [
                    /** 初期管理者 */
                    [
                        'name'=>'システム管理者',
                        'email'=>'info@opensource-workshop.jp',
                        'userid'=>'admin',
                        'password'=>bcrypt('C-admin'),
                        'remember_token'=>'',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                ]
            );
        }
    }
}
