<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User\Cabinets\CabinetContent;

class CabinetValidDestinationFolder implements Rule
{
    /** @var int|null */
    private $expected_cabinet_id;

    /** @var string */
    private $message = '';

    /**
     * @param int|null $expected_cabinet_id 同一であるべきキャビネットID
     */
    public function __construct($expected_cabinet_id)
    {
        $this->expected_cabinet_id = $expected_cabinet_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // destination_id がフォルダか
        $destination = CabinetContent::find($value);
        if (empty($destination) || $destination->is_folder !== CabinetContent::is_folder_on) {
            $this->message = '移動先フォルダが不正です。';
            return false;
        }

        // キャビネット一致チェック
        if ($this->expected_cabinet_id !== null && $destination->cabinet_id != $this->expected_cabinet_id) {
            $this->message = '移動先フォルダのキャビネットが一致しません。';
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message ?: '移動先が不正です。';
    }
}
