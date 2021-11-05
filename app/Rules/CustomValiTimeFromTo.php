<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Illuminate\Support\Facades\Lang;

/**
 * Carbonインスタンスの時刻比較
 */
class CustomValiTimeFromTo implements Rule
{
    protected $carbon_time_from;
    protected $carbon_time_to;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($carbon_time_from, $carbon_time_to)
    {
        $this->carbon_time_from = $carbon_time_from;
        $this->carbon_time_to = $carbon_time_to;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->carbon_time_to->gte($this->carbon_time_from);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return Lang::get('messages.entered_time_is_invalid');
    }
}
