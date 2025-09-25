<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Enums\PageMetaRobots;

/**
 * meta robots値のバリデーション
 */
class CustomValiMetaRobots implements Rule
{
    /**
     * @var array<string>
     */
    private $invalidValues = [];

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value)
    {
        if (is_null($value) || $value === '') {
            return true;
        }

        $values = [];

        if (is_array($value)) {
            $values = $value;
        } else {
            $values = [$value];
        }

        $values = array_filter($values, function ($item) {
            return $item !== null && $item !== '';
        });

        if (empty($values)) {
            return true;
        }

        $allowed = PageMetaRobots::getMemberKeys();

        $this->invalidValues = array_diff($values, $allowed);

        return empty($this->invalidValues);
    }

    /**
     * Get the validation error message.
     */
    public function message()
    {
        if (!empty($this->invalidValues)) {
            return ':attributeに不正な値（' . implode(', ', $this->invalidValues) . '）が含まれています。';
        }

        return ':attributeに不正な値が含まれています。';
    }
}

