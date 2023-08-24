<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Enums\UserColumnType;
use App\Models\Core\UsersColumns;
use App\Models\Core\UsersColumnsSet;

class MigrationFixedColumnFromUsersColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $columns_sets = UsersColumnsSet::get();
        foreach ($columns_sets as $columns_set) {
            if (UsersColumns::where('columns_set_id', $columns_set->id)->where('column_type', UserColumnType::user_name)->count() == 0) {

                // 固定項目を項目順の頭に追加して、追加項目はその後の表示順に更新する事で、現在の並び順を再現する。

                $users_columns = UsersColumns::where('columns_set_id', $columns_set->id)->orderBy('display_sequence')->get();

                UsersColumns::insert([
                    [
                        'columns_set_id'        => $columns_set->id,
                        'column_type'           => UserColumnType::user_name,
                        'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::user_name),
                        'is_fixed_column'       => 1,
                        'is_show_auto_regist'   => 1,
                        'is_show_my_page'       => 1,
                        'is_edit_my_page'       => 0,
                        'required'              => 1,
                        'display_sequence'      => 1,
                    ],
                    [
                        'columns_set_id'        => $columns_set->id,
                        'column_type'           => UserColumnType::login_id,
                        'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::login_id),
                        'is_fixed_column'       => 1,                           // (画面からいじれない項目)
                        'is_show_auto_regist'   => 1,
                        'is_show_my_page'       => 1,
                        'is_edit_my_page'       => 0,
                        'required'              => 1,                           // 設定するけど is_fixed_column=1 はおそらく参照しない
                        'display_sequence'      => 2,
                    ],
                    [
                        'columns_set_id'        => $columns_set->id,
                        'column_type'           => UserColumnType::user_email,
                        'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::user_email),
                        'is_fixed_column'       => 1,
                        'is_show_auto_regist'   => 1,
                        'is_show_my_page'       => 1,
                        'is_edit_my_page'       => 1,
                        'required'              => 1,
                        'display_sequence'      => 3,
                    ],
                    [
                        'columns_set_id'        => $columns_set->id,
                        'column_type'           => UserColumnType::user_password,
                        'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::user_password),
                        'is_fixed_column'       => 1,
                        'is_show_auto_regist'   => 1,
                        'is_show_my_page'       => 0,
                        'is_edit_my_page'       => 1,
                        'required'              => 1,
                        'display_sequence'      => 4,
                    ],
                    [
                        'columns_set_id'        => $columns_set->id,
                        'column_type'           => UserColumnType::created_at,
                        'column_name'           => UserColumnType::getDescriptionFixed(UserColumnType::created_at),
                        'is_fixed_column'       => 0,
                        'is_show_auto_regist'   => 0,
                        'is_show_my_page'       => 1,
                        'is_edit_my_page'       => 0,
                        'required'              => 0,
                        'display_sequence'      => 5,
                    ],
                ]);

                foreach ($users_columns as $users_column) {
                    $users_column->display_sequence = $users_column->display_sequence + 5;
                    $users_column->save();
                }
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
