<select class="form-control" name="view_type" onchange="javascript:submit(this.form);">
    <option value="">閲覧種類</option>
    <optgroup label="日別状況表">
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'table_daily_confirmed_desc', 'option_caption' => '感染者数　降順'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'table_daily_confirmed_asc',  'option_caption' => '感染者数　昇順'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'table_daily_deaths_desc',    'option_caption' => '死亡者数　降順'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'table_daily_deaths_asc',     'option_caption' => '死亡者数　昇順'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'table_daily_recovered_desc', 'option_caption' => '回復者数　降順'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'table_daily_recovered_asc',  'option_caption' => '回復者数　昇順'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'table_daily_active_desc',    'option_caption' => '感染中数　降順'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'table_daily_active_asc',     'option_caption' => '感染中数　昇順'])
    </optgroup>
    <optgroup label="日別計算表">
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'table_daily_fatality_rate_moment_desc', 'option_caption' => '致死率(計算日)　降順'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'table_daily_fatality_rate_estimation_desc', 'option_caption' => '致死率(予測)　降順'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'table_daily_deaths_estimation_desc', 'option_caption' => '死亡者数(予測)　降順'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'table_daily_active_rate_desc', 'option_caption' => 'Active率　降順'])
    </optgroup>
    <optgroup label="グラフ">
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'graph_confirmed', 'option_caption' => '感染者推移グラフ'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'graph_deaths',    'option_caption' => '死亡者推移グラフ'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'graph_recovered', 'option_caption' => '回復者推移グラフ'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'graph_active',    'option_caption' => '感染中推移グラフ'])
    </optgroup>
    <optgroup label="グラフ(計算値)">
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'graph_fatality_rate_moment', 'option_caption' => '致死率(計算日)推移グラフ'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'graph_fatality_rate_estimation', 'option_caption' => '致死率(予測)推移グラフ'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'graph_deaths_estimation', 'option_caption' => '死亡者数(予測)推移グラフ'])
        @include($option_blade_path, ['select_value' => $view_type, 'option_value' => 'graph_active_rate', 'option_caption' => 'Active率推移グラフ'])
    </optgroup>
</select>
