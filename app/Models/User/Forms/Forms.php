<?php

namespace App\Models\User\Forms;

use App\UserableNohistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forms extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use HasFactory;
    use UserableNohistory;

    /**
     * キャストする必要のある属性
     */
    protected $casts = [
        'display_from' => 'datetime',
        'display_to' => 'datetime',
        'regist_from' => 'datetime',
        'regist_to' => 'datetime',
    ];

    // 更新する項目の定義
    protected $fillable = [
        'bucket_id',
        'forms_name',
        'form_mode',
        'access_limit_type',
        'form_password',
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
        'numbering_prefix',
        'use_spam_filter_flag',
        'spam_filter_message',
    ];

    /**
     *  指定したFrame が表示対象か判定
     *
     */
    public function isTargetFrame($frame_id)
    {
        if (in_array($frame_id, explode(',', $this->target_frame_ids))) {
            return true;
        }
        return false;
    }
}
