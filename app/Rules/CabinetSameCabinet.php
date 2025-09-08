<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User\Cabinets\CabinetContent;

class CabinetSameCabinet implements Rule
{
    /** @var int */
    private $expectedCabinetId;

    /** @var string */
    private $message = '';

    public function __construct($expectedCabinetId)
    {
        $this->expectedCabinetId = $expectedCabinetId;
    }

    public function passes($attribute, $value)
    {
        $node = CabinetContent::find($value);
        if (empty($node)) {
            // 別ルール（exists）で捕捉される想定
            $this->message = '移動対象が不正です。';
            return false;
        }

        if ($node->cabinet_id != $this->expectedCabinetId) {
            $this->message = '移動対象とキャビネットが一致しません。';
            return false;
        }
        return true;
    }

    public function message()
    {
        return $this->message ?: '移動対象が不正です。';
    }
}
