<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Illuminate\Support\Facades\Lang;

/**
 * 片方が入力されていたら両方必須
 */
class CustomValiBothRequired implements Rule
{
    protected $param1;
    protected $param2;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($param1, $param2)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
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
        if ($this->param1 || $this->param2) {
            if ($this->param1 && $this->param2) {
                // 両方、入力ありならTRUE
                $result = true;
            } else {
                // 片方、未入力ならFALSE
                $result = false;
            }
        } else {
            // 両方、未入力ならTRUE
            $result = true;
        }
        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return Lang::get('messages.both_required');
    }
}
