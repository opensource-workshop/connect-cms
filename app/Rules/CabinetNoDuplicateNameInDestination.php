<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User\Cabinets\CabinetContent;

class CabinetNoDuplicateNameInDestination implements Rule
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
        if (empty($this->destinationId)) {
            return true;
        }
        $node = CabinetContent::find($value);
        $destination = CabinetContent::find($this->destinationId);
        if (empty($node) || empty($destination)) {
            return true;
        }

        $exists = CabinetContent::where('parent_id', $destination->id)
            ->where('name', $node->name)
            ->where('id', '!=', $node->id)
            ->exists();
        if ($exists) {
            $this->message = '移動先に同名のアイテムが存在します。';
            return false;
        }
        return true;
    }

    public function message()
    {
        return $this->message ?: '移動先に同名があります。';
    }
}
