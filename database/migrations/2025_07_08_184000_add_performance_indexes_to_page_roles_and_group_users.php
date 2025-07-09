<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexesToPageRolesAndGroupUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // サイト内検索プラグインのPageRole::getPageRoles()クエリ最適化用インデックス
        
        // page_roles テーブルに複合インデックス追加
        // WHERE page_roles.page_id IN (...) AND page_roles.group_id = ? のクエリを最適化
        Schema::table('page_roles', function (Blueprint $table) {
            $table->index(['page_id', 'group_id'], 'idx_page_roles_on_page_id_and_group_id');
        });

        // group_users テーブルに複合インデックス追加  
        // WHERE group_users.user_id = ? AND group_users.group_id = ? のクエリを最適化
        Schema::table('group_users', function (Blueprint $table) {
            $table->index(['user_id', 'group_id'], 'idx_group_users_on_user_id_and_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // インデックスを削除
        Schema::table('page_roles', function (Blueprint $table) {
            $table->dropIndex('idx_page_roles_on_page_id_and_group_id');
        });

        Schema::table('group_users', function (Blueprint $table) {
            $table->dropIndex('idx_group_users_on_user_id_and_group_id');
        });
    }
}