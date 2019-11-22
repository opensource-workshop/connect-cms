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
    @include('plugins.user.contents.contents_frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

{{-- データ --}}
<p>
    <div class="card">
        <div class="card-body">
            {!! $contents->content_text !!}
        </div>
    </div>
</p>
@can('posts.delete',[[$contents, 'contents']])
<form action="/redirect/plugin/contents/delete/{{$page->id}}/{{$frame_id}}/{{$contents->id}}" method="POST" class="form-horizontal">
    {{ csrf_field() }}
    <span class="text-danger">
    <p>
    データを削除します。<br />
    元に戻すことはできないため、よく確認して実行してください。
    </p>
    </span>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card border-danger">
                <div class="card-body text-center p-2">
                    <label class="mb-0">
                        <input type="checkbox" name="frame_delete_flag" value="1">フレームも同時に削除します。
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="row form-group mx-auto mt-3">
            <div>
                <button type="button" class="btn btn-default btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}/'"><i class="fas fa-times"></i> キャンセル</button>
                <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')">
                    <i class="fas fa-check"></i> データ削除
                </button>
            </div>
        </div>
    </div>
</form>
@endcan
