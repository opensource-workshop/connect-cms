<?php

namespace App\Models\User\Learningtasks;

use App\Enums\RoleName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LearningtasksPosts extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;
    use HasFactory;

    // Carbonインスタンス（日付）に自動的に変換
    protected $dates = ['posted_at'];

    /**
     * 課題管理を取得
     */
    public function learningtask()
    {
        return $this->belongsTo(Learningtasks::class, 'learningtasks_id', 'id');
    }

    /**
     * 使用設定を取得
     */
    public function post_settings()
    {
        return $this->hasMany(LearningtasksUseSettings::class, 'post_id', 'id');
    }

    /**
     * 学生を取得する
     */
    public function students()
    {
        return $this->hasMany(LearningtasksUsers::class, 'post_id', 'id')->where('role_name', RoleName::student)->orderBy('user_id');
    }

    /**
     * 教員を取得する
     */
    public function teachers()
    {
        return $this->hasMany(LearningtasksUsers::class, 'post_id', 'id')->where('role_name', RoleName::teacher)->orderBy('user_id');
    }

    /**
     * リスト表示用タイトル
     * 改行を取り除いたもの。
     */
    public function getNobrPostTitle()
    {
        return str_ireplace(['<br>', '<br />'], ' ', $this->post_title);
    }
}
