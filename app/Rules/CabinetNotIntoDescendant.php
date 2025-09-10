<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User\Cabinets\CabinetContent;

class CabinetNotIntoDescendant implements Rule
{
    /** @var int|null */
    private $destinationId;

    /** @var string */
    private $message = '';

    public function __construct($destinationId)
    {
        $this->destinationId = $destinationId;
    }

    public function passes($attribute, $value)
    {
        // 宛先未指定や不正は他のルールで検出するため、ここではスキップ
        if (empty($this->destinationId)) {
            return true;
        }
        $node = CabinetContent::find($value);
        $destination = CabinetContent::find($this->destinationId);
        if (empty($node) || empty($destination)) {
            return true;
        }

        // 自身および配下へは移動不可
        $ngIds = CabinetContent::descendantsAndSelf($node->id)->pluck('id')->all();
        if (in_array($destination->id, $ngIds, true)) {
            $this->message = '自身または配下へは移動できません。';
            return false;
        }
        return true;
    }

    public function message()
    {
        return $this->message ?: '不正な移動先です。';
    }
}
