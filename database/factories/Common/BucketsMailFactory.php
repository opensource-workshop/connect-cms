<?php

namespace Database\Factories\Common;

use App\Models\Common\Buckets;
use Illuminate\Database\Eloquent\Factories\Factory;

class BucketsMailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'buckets_id' => Buckets::factory()->create(),
            'timing' => 0,  // 0:即時送信、画面項目なし
            'notice_on' => 0,
            'notice_create' => 0,
            'notice_update' => 0,
            'notice_delete' => 0,
            'notice_addresses' => $this->faker->safeEmail(),
            'notice_everyone' => 0,
            'notice_groups' => null,
            'notice_roles' => null,   // 画面項目なし
            'notice_subject' => $this->faker->title(),
            'notice_body' => $this->faker->realText(),
            'relate_on' => 0,
            'relate_subject' => $this->faker->title(),
            'relate_body' => $this->faker->realText(),
            'approval_on' => 0,
            'approval_addresses' => $this->faker->safeEmail(),
            'approval_groups' => null,
            'approval_subject' => $this->faker->title(),
            'approval_body' => $this->faker->realText(),
            'approved_on' => 0,
            'approved_author' => 0,
            'approved_addresses' => $this->faker->safeEmail(),
            'approved_groups' => null,
            'approved_subject' => $this->faker->title(),
            'approved_body' => $this->faker->realText(),
        ];
    }
}
