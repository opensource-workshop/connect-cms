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

                // UsersColumnsSet登録時のUsersColumns初期登録
                UsersColumns::initInsertForRegistUsersColumnsSet($columns_set->id);

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
