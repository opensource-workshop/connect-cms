<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Core\UsersColumns;
use App\Models\Core\UsersColumnsSelects;
use App\Models\Core\UsersColumnsSet;
use App\User;

class InitAndMigrationFromUsersColumnsSets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @see database\migrations\2021_12_03_151901_init_and_migration_from_reservations_columns_set_and_reservations_category.php is copy
     */
    public function up()
    {
        // 項目セット
        if (UsersColumnsSet::count() == 0) {
            /* 初期データ登録
            ----------------------------------------------*/

            // 項目セット登録
            // 項目セットのid=1は、基本データとして消せないように対応する。（idはfillable でガードされ、セットできないが、0件からのcreateのため、結果的にid=1になる）
            $columns_set_basic = UsersColumnsSet::create([
                'id'               => 1,
                'name'             => '基本',
                'display_sequence' => 1,
            ]);

            // 以下columns_set_id を Update
            // - users
            // - users_columns
            // - users_columns_selects
            UsersColumns::where('columns_set_id', 0)->update(['columns_set_id' => $columns_set_basic->id]);
            UsersColumnsSelects::where('columns_set_id', 0)->update(['columns_set_id' => $columns_set_basic->id]);
            User::where('columns_set_id', 0)->update(['columns_set_id' => $columns_set_basic->id]);
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
