{{--
 * 編集画面(編集時の表示側画面)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}

{{-- 機能選択タブ --}}
<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.contents.frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

{{-- データ --}}
<p>
    <div class="panel panel-default">
        <div class="panel-body">
            {!! $contents->content_text !!}
        </div>
    </div>
</p>
<form action="/redirect/plugin/contents/destroy/{{$page->id}}/{{$frame_id}}/{{$contents->id}}" method="POST" class="form-horizontal">
    {{ csrf_field() }}
    <span class="text-danger">
    <p>
    データを削除します。<br />
    元に戻すことはできないため、よく確認して実行してください。
    </p>
    </span>

    <div class="row">
        <div class="col-md-6 col-md-push-3">
            <div class="panel panel-danger ">
                <div class="panel-body text-center checkbox">
                    <label>
                        <input type="checkbox" name="frame_delete_flag" value="1">フレームも同時に削除します。
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="form-group container-fluid row">
            <div class="text-center">
                <button type="submit" class="btn btn-danger form-horizontal" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')">
                    <span class="glyphicon glyphicon-ok"></span> データ削除
                </button>
                <button type="button" class="btn btn-default form-horizontal" onclick="location.href='{{URL::to($page->permanent_link)}}/'"><span class="glyphicon glyphicon-remove"></span> キャンセル</button>
            </div>
        </div>
    </div>
</form>
