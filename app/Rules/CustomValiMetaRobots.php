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
    private $invalid_values = [];

    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
    }

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

        $this->invalid_values = array_diff($values, $allowed);

        return empty($this->invalid_values);
    }

    /**
     * Get the validation error message.
     */
    public function message()
    {
        if (!empty($this->invalid_values)) {
            return ':attributeに不正な値（' . implode(', ', $this->invalid_values) . '）が含まれています。';
        }

        return ':attributeに不正な値が含まれています。';
    }
}
