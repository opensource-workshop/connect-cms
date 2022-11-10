<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Nc3MailSetting extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'mail_settings';

    /**
     * block_keyでメール設定 取得
     */
    public static function getMailSettingsByBlockKeys($block_keys): Collection
    {
        return Nc3MailSetting::select('mail_settings.*', 'mail_setting_fixed_phrases.mail_fixed_phrase_subject', 'mail_setting_fixed_phrases.mail_fixed_phrase_body')
            ->join('mail_setting_fixed_phrases', function ($join) {
                $join->on('mail_setting_fixed_phrases.mail_setting_id', '=', 'mail_settings.id')
                    ->where('mail_setting_fixed_phrases.language_id', Nc3Language::language_id_ja);
            })
            ->whereIn('mail_settings.block_key', $block_keys)
            ->get();
    }
}
