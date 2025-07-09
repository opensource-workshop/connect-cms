<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Common\Categories;

class CustomValiUniqueClassname implements Rule
{
    private $exclude_id;

    /**
     * Create a new rule instance.
     *
     * @param int|null $exclude_id 除外するカテゴリID（既存編集時）
     */
    public function __construct($exclude_id = null)
    {
        $this->exclude_id = $exclude_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return true; // 空の場合は required で処理
        }

        $query = Categories::where('classname', $value);

        if ($this->exclude_id) {
            $query->where('id', '!=', $this->exclude_id);
        }

        return !$query->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'クラス名が重複しています。';
    }
}
