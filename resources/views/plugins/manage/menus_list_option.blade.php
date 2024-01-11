{{--
 * オプション管理メニューリスト
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category オプション管理
--}}
@if (Auth::user()->can('admin_system'))
    @php
        $option_plugins = [];
        $option_directories = [];

        // オプション管理プラグインのディレクトリの取得
        if (File::exists(app_path() . '/PluginsOption/Manage')) {
            $option_directories = File::directories(app_path() . '/PluginsOption/Manage');
        }

        // プラグインのini ファイルの取得
        foreach ($option_directories as $dirkey => $directorie) {
            // オプション管理プラグインとして存在するか確認
            $plugin_manage = basename($directorie);
            $class_name = "App\PluginsOption\Manage\\" . ucfirst($plugin_manage) . "\\" . ucfirst($plugin_manage);
            if (class_exists($class_name)) {
                $option_plugins[] = [
                    // 'class_name'       => $class_name,
                    'plugin_name'      => defined("$class_name::plugin_name") ? $class_name::plugin_name : null,
                    'plugin_name_full' => defined("$class_name::plugin_name_full") ? $class_name::plugin_name_full : null,
                ];
            }
        }
    @endphp
    @if ($option_plugins)
        <div class="list-group mt-2">
            <div class="list-group-item bg-light">オプション管理系</div>

            @foreach ($option_plugins as $option_plugin)
                @if (isset($plugin_name) && $plugin_name == $option_plugin['plugin_name'])
                    <a href="{{url('/')}}/manage/{{$option_plugin['plugin_name']}}" class="list-group-item active">{{$option_plugin['plugin_name_full']}}</a>
                @else
                    <a href="{{url('/')}}/manage/{{$option_plugin['plugin_name']}}" class="list-group-item">{{$option_plugin['plugin_name_full']}}</a>
                @endif
            @endforeach
        </div>
    @endif
@endif
