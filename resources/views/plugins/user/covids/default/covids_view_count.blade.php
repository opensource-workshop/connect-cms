<select class="form-control" name="view_count" onchange="javascript:submit(this.form);">
    <option value="">表示件数</option>
    @include($option_blade_path, ['select_value' => $view_count, 'option_value' => '5',   'option_caption' => '5件'])
    @include($option_blade_path, ['select_value' => $view_count, 'option_value' => '10',  'option_caption' => '10件'])
    @include($option_blade_path, ['select_value' => $view_count, 'option_value' => '25',  'option_caption' => '25件'])
    @include($option_blade_path, ['select_value' => $view_count, 'option_value' => '50',  'option_caption' => '50件'])
    @include($option_blade_path, ['select_value' => $view_count, 'option_value' => '100', 'option_caption' => '100件'])
    @include($option_blade_path, ['select_value' => $view_count, 'option_value' => 'all', 'option_caption' => '全件'])
</select>
