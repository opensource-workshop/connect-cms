<?php

namespace App\Models\User\Forms;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class Forms extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = [
        'bucket_id',
        'forms_name',
        'entry_limit',
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
