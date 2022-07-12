<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\User;
use App\Models\Common\GroupUser;

class MigrateDeletedUserFromGroupUsers extends Migration
{
    /**
     * ユーザ削除時にGroupUserを削除していなかったため、グループ管理の一覧で参加ユーザ数が多い（削除したユーザ含んでいた）
     * 削除したユーザの参加グループは削除するデータ修正パッチ
     * @see https://github.com/opensource-workshop/connect-cms/issues/1357
     *
     * @return void
     */
    public function up()
    {
        // 参加グループ削除
        $group_user_ids = GroupUser::whereNotIn('user_id', User::pluck('id'))->pluck('id');
        GroupUser::destroy($group_user_ids);
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
