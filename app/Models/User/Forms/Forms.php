<?php

namespace App\Models\User\Forms;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class Forms extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // Carbonインスタンス（日付）に自動的に変換
    protected $dates = [
        'display_from',
        'display_to',
        'regist_from',
        'regist_to',
    ];

    // 更新する項目の定義
    protected $fillable = [
        'bucket_id',
        'forms_name',
        'form_mode',
        'entry_limit',
        'entry_limit_over_message',
        'display_control_flag',
        'display_from',
        'display_to',
        'regist_control_flag',
        'regist_from',
        'regist_to',
        'can_view_inputs_moderator',
        'mail_send_flag',
        'mail_send_address',
        'user_mail_send_flag',
        'mail_subject',
        'mail_format',
        'data_save_flag',
        'after_message',
        'numbering_use_flag',
        'numbering_prefix'
    ];
}
