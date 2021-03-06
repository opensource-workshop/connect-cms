{{--
 * プラグイン追加 画面パーツ
 *
 * @param obj $page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 --}}
{{-- Todo：今は個別にプラグイン記述 ＞ 最終的にはインストールされているプラグインを同期に追加 --}}
<script type="text/javascript">
    function submit_form_add_plugin{{$area_id}}() {
        form_add_plugin{{$area_id}}.submit();
    }
</script>
<form action="{{url('/core/frame/addPlugin')}}/{{$page->id}}" name="form_add_plugin{{$area_id}}" id="form_add_plugin{{$area_id}}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="action" value="plugin_add">
    <input type="hidden" name="area_id" value="{{$area_id}}">
    @if (isset($disabled) && ($disabled))
        <select name="add_plugin" class="form-control" onchange="submit_form_add_plugin{{$area_id}}();" disabled>
    @else
        <select name="add_plugin" class="form-control" onchange="submit_form_add_plugin{{$area_id}}();">
    @endif
        <option value="">{{$area_name}}に追加</option>
        @foreach($plugins as $plugin)
            <option value="{{strtolower($plugin->plugin_name)}}">{{$plugin->plugin_name_full}}</option>
        @endforeach
    </select>
</form>
