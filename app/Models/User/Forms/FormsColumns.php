<?php

namespace App\Models\User\Forms;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class FormsColumns extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = ['forms_id', 'column_type', 'column_name', 'required', 'frame_col', 'caption', 'caption_color', 'place_holder', 'minutes_increments', 'minutes_increments_from', 'minutes_increments_to', 'rule_allowed_numeric', 'rule_allowed_alpha_numeric', 'rule_digits_or_less', 'rule_max', 'rule_min', 'rule_word_count', 'rule_date_after_equal', 'display_sequence'];
}
