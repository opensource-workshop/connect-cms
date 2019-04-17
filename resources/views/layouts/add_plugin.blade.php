{{--
 * プラグイン追加 画面パーツ
 *
 * @param obj $current_page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 --}}
{{-- Todo：今は個別にプラグイン記述 ＞ 最終的にはインストールされているプラグインを同期に追加 --}}
<script type="text/javascript">
    function submit_form_add_plugin{{$area_no}}() {
        form_add_plugin{{$area_no}}.submit();
    }
</script>
<form action="{{url('/core/frame/addPlugin')}}/{{$current_page->id}}" name="form_add_plugin{{$area_no}}" id="form_add_plugin{{$area_no}}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="action" value="plugin_add">
    <input type="hidden" name="area_no" value="{{$area_no}}">
    <select name="add_plugin" class="form-control" onchange="submit_form_add_plugin{{$area_no}}();">
        <option value="">{{$area_name}}に追加</option>
        <option value="contents">contents</option>
        <option value="forms">forms</option>
        <option value="sampleforms">sampleforms</option>
    </select>
</form>
