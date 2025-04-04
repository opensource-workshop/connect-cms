<?php

namespace Database\Factories\Common;

use App\Models\Common\Group;
use App\Models\Common\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'page_id' => Page::factory(),
            'group_id' => Group::factory(),
            'target' => 'base',
            'role_name' => 'role_guest',
            'role_value' => 1,
        ];
    }
}
