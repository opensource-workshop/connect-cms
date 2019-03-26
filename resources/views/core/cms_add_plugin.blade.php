{{--
 * プラグイン追加 画面パーツ
 *
 * @param obj $current_page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 --}}
{{-- Todo：今は認証有無のみチェック ＞ 最終的には権限をチェック --}}
@auth
<form action="/core/frame/addPlugin/{{$current_page->id}}" name="form_add_plugin" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="action" value="plugin_add">
    <div class="col-sm-3 pull-right" style="padding-right: 0; margin-bottom: 5px;">
        <select name="add_plugin" class="form-control" onchange="javascript:form_add_plugin.submit();">
            <option value="">add plugin...</option>

            {{-- Todo：今は個別にプラグイン記述 ＞ 最終的にはインストールされているプラグインを同期に追加 --}}
            <option value="contents">contents</option>
            <option value="forms">forms</option>
        </select>
    </div>
</form>
@endauth
