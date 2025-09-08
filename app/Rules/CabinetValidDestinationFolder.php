<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User\Cabinets\CabinetContent;

class CabinetValidDestinationFolder implements Rule
{
    /** @var int|null */
    private $expectedCabinetId;

    /** @var string */
    private $message = '';

    /**
     * @param int|null $expectedCabinetId 同一であるべきキャビネットID
     */
    public function __construct($expectedCabinetId)
    {
        $this->expectedCabinetId = $expectedCabinetId;
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
        if ($this->expectedCabinetId !== null && $destination->cabinet_id != $this->expectedCabinetId) {
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

