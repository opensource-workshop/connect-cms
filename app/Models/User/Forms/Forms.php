<?php

namespace App\Models\User\Forms;

use Illuminate\Database\Eloquent\Model;

class Forms extends Model
{
    // 更新する項目の定義
    protected $fillable = ['bucket_id', 'forms_name', 'mail_send_flag', 'mail_send_address', 'user_mail_send_flag', 'mail_subject', 'mail_format', 'data_save_flag', 'after_message', 'numbering_use_flag', 'numbering_prefix'];
}
