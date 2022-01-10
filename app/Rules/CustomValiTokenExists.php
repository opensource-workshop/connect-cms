<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\Utilities\Token\TokenUtils;

/**
 * トークン存在チェックバリデーション
 */
class CustomValiTokenExists implements Rule
{
    protected $record_token = null;
    protected $record_created_at = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($record_token, $record_created_at)
    {
        $this->record_token = $record_token;
        $this->record_created_at = $record_created_at;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute 項目名
     * @param  mixed  $value チェックするトークン
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return TokenUtils::tokenExists($value, $this->record_token, $this->record_created_at, 60);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '有効期限切れのため、そのURLはご利用できません。';
    }
}
