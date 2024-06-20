<?php

namespace Database\Factories\Common;

use App\Enums\ContentOpenType;
use App\Enums\PluginName;
use App\Models\Common\Page;
use App\Models\Common\Buckets;
use Illuminate\Database\Eloquent\Factories\Factory;

class FrameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // app/Plugins/Manage/SiteManage/SiteManage.php よりコピー
        $area_ids = [0, 1, 2, 3, 4];

        // resources\views\core\cms_frame_edit.blade.php よりコピー
        $frame_design = [null, 'none', 'default', 'primary', 'secondary', 'success', 'info', 'warning', 'danger'];
        $frame_col = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        $plugin_name = PluginName::getPluginName(array_rand(PluginName::enum));

        return [
            'page_id' => Page::factory(),
            'area_id' => $area_ids[array_rand($area_ids)],
            'frame_title' => $this->faker->title(),
            'frame_design' => $frame_design[array_rand($frame_design)],
            'plugin_name' => $plugin_name,
            'frame_col' => $frame_col[array_rand($frame_col)],
            'template' => 'default',
            'plug_name' => null,
            'browser_width' => null,
            'disable_whatsnews' => 0,
            'disable_searchs' => 0,
            'page_only' => 0,
            'default_hidden' => 0,
            'classname' => $this->faker->word(),
            'classname_body' => $this->faker->word(),
            'none_hidden' => 0,
            'bucket_id' => Buckets::factory()->create([
                'plugin_name' => $plugin_name,
            ]),
            'display_sequence' => $this->faker->unique()->randomNumber(),
            'content_open_type' => array_rand(ContentOpenType::enum),
            'content_open_date_from' => $this->faker->dateTimeBetween('now', '+1 week'),
            'content_open_date_to' =>  $this->faker->dateTimeBetween('+1 week', '+2 week'),
        ];
    }
}
