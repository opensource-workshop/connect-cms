{{--
 * ページエラー設定のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<script type="text/javascript">
    /** NoImageの削除ボタン押下 */
    function delete_no_image(btn) {
        if (confirm('No Image画像を削除します。\nよろしいですか？')) {
            btn.disabled = true;
            no_image_form.submit();
        }
    }
</script>

{{-- NoImage削除フォーム --}}
<form action="{{url('/manage/site/deleteNoImage')}}" method="post" name="no_image_form">
    {{csrf_field()}}
</form>

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.site.site_manage_tab')
    </div>
    <div class="card-body">

        {{-- 共通エラーメッセージ 呼び出し --}}
        @include('plugins.common.errors_form_line')
        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        <form action="{{url('/')}}/manage/site/savePageError" method="post" enctype="multipart/form-data">
            {{csrf_field()}}

            {{-- 403 --}}
            <div class="form-group">
                <label class="col-form-label">IPアドレス制限などで権限がない場合の表示ページ</label>
                <input type="text" name="page_permanent_link_403" value="{{ Configs::getConfigsValueAndOld($configs, 'page_permanent_link_403', null) }}" class="form-control">
            </div>

            {{-- 404 --}}
            <div class="form-group">
                <label class="col-form-label">指定ページがない場合の表示ページ</label>
                <input type="text" name="page_permanent_link_404" value="{{ Configs::getConfigsValueAndOld($configs, 'page_permanent_link_404', null) }}" class="form-control">
            </div>

            <div class="card card-body bg-light p-2 mb-3">
                <ul>
                    <li>エラー設定の対象は一般画面です。管理画面は対象外です。</li>
                </ul>
            </div>

            <div class="form-group">
                <label class="col-form-label">No Image画像</label>
                <div>
                    @php
                        $no_image = Configs::getConfigsValueAndOld($configs, 'no_image', null);
                    @endphp
                    @if ($no_image)
                        <div class="form-group">
                            <a href="{{url('/')}}/uploads/no_image/{{$no_image}}" target="_blank">{{$no_image}}</a>
                            <!-- 削除ボタン -->
                            <button type="button"
                                class="btn btn-outline-danger btn-sm"
                                onclick="javascript:return delete_no_image(this);"
                            >
                                <i class="fas fa-trash-alt"></i> 削除
                            </button>
                        </div>
                    @endif
                    <div class="custom-file">
                        {{-- laravelでアップできる拡張子と同じにする。see) \Illuminate\Validation\Concerns\ValidatesAttributes::validateImage() --}}
                        <input type="file" class="custom-file-input" id="id_no_image" name="no_image" accept=".jpeg, .jpg, .png, .gif, .bmp, .svg, .webp">
                        <label class="custom-file-label @if ($errors->has('no_image')) border-danger @endif" for="id_no_image" data-browse="参照"></label>
                    </div>
                    @include('plugins.common.errors_inline', ['name' => 'no_image'])
                    <small class="form-text text-muted">
                        設定するとNo Image画像を変更できます。<br />
                        変更後のNo Image画像を確認するには、ブラウザキャッシュをクリアしてください。<br />
                    </small>
                </div>
            </div>

            {{-- Submitボタン --}}
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>
        </form>
    </div>
</div>

<script>
    {{-- custom-file-inputクラスでファイル選択時にファイル名表示 --}}
    $('.custom-file-input').on('change',function(){
        $(this).next('.custom-file-label').html($(this)[0].files[0].name);
    })
</script>

@endsection
