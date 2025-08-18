{{--
 * サイト管理（meta情報）のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

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

    <form action="{{url('/')}}/manage/site/saveMeta" method="POST" enctype="multipart/form-data">
    {{csrf_field()}}

        {{-- 基本設定 --}}
        <div class="alert alert-info mb-3">
            <small>
                <strong><i class="fas fa-info-circle"></i> meta情報とは</strong><br>
                検索エンジンやSNSがWebページの内容を理解するための情報です。設定することで検索結果やSNSに表示される情報を追加することができます。
            </small>
        </div>
        <h5>基本設定</h5>
        <div class="form-group">
            <label class="control-label">サイト概要 <small class="text-muted">(meta description)</small></label>
            <textarea name="description" class="form-control" rows=2>{!! Configs::getConfigsValueAndOld($configs, 'description', null) !!}</textarea>
            <small class="form-text text-muted">検索結果に表示されるサイトの説明文</small>
        </div>

        {{-- OGP設定 --}}
        <hr class="mt-4 mb-3">
        <h5>OGP設定</h5>
        <div class="alert alert-info mb-3">
            <small>
                <strong><i class="fas fa-info-circle"></i> OGP（Open Graph Protocol）とは</strong><br>
                FacebookやTwitterなどのSNSでWebページがシェアされた際に、タイトル・説明文・画像などを適切に表示するための設定です。<br>
                <strong>※ og:url（ページURL）は自動的に生成されます。</strong>
            </small>
        </div>
        <div class="form-group">
            <label class="control-label">OGサイト名 <small class="text-muted">(og:site_name)</small></label>
            <input type="text" name="og_site_name" class="form-control" value="{!! Configs::getConfigsValueAndOld($configs, 'og_site_name', null) !!}">
            <small class="form-text text-muted">サイト全体の名前（例：会社名やサービス名）</small>
        </div>
        <div class="form-group">
            <label class="control-label">OGタイトル <small class="text-muted">(og:title)</small></label>
            <input type="text" name="og_title" class="form-control" value="{!! Configs::getConfigsValueAndOld($configs, 'og_title', null) !!}">
            <small class="form-text text-muted">SNSでシェアされた際のタイトル</small>
        </div>
        <div class="form-group">
            <label class="control-label">OG説明文 <small class="text-muted">(og:description)</small></label>
            <textarea name="og_description" class="form-control" rows=2>{!! Configs::getConfigsValueAndOld($configs, 'og_description', null) !!}</textarea>
            <small class="form-text text-muted">SNSでシェアされた際の説明文</small>
        </div>
        <div class="form-group">
            <label class="control-label">OG画像 <small class="text-muted">(og:image)</small></label>
            @if (Configs::getConfigsValueAndOld($configs, 'og_image', null))
                <div class="mb-2">
                    <small class="text-muted">現在の画像:</small><br>
                    <a href="{{url('/uploads/ogp/')}}/{!! Configs::getConfigsValueAndOld($configs, 'og_image', null) !!}" target="_blank">
                        <img src="{{url('/uploads/ogp/')}}/{!! Configs::getConfigsValueAndOld($configs, 'og_image', null) !!}" alt="OG画像" style="max-width: 200px; max-height: 100px;" class="img-thumbnail">
                    </a>
                </div>
            @endif
            <div class="custom-file">
                <input type="file" class="custom-file-input @if ($errors->has('og_image_file')) border-danger @endif" id="og_image_file" name="og_image_file" accept="image/*" onchange="handleOgImageFileSelect(this)">
                <label class="custom-file-label @if ($errors->has('og_image_file')) border-danger @endif" for="og_image_file" data-browse="参照">OG画像ファイル</label>
            </div>
            @include('plugins.common.errors_inline', ['name' => 'og_image_file'])
            <input type="hidden" name="og_image" value="{!! Configs::getConfigsValueAndOld($configs, 'og_image', null) !!}">
            <small class="form-text text-muted">SNSでシェアされた際の画像URL（1200x630px推奨、対応形式：jpg, jpeg, png）</small>
        </div>
        <div class="form-group">
            <label class="control-label">OGタイプ <small class="text-muted">(og:type)</small></label>
            <input type="text" name="og_type" class="form-control" value="{!! Configs::getConfigsValueAndOld($configs, 'og_type', 'website') !!}" placeholder="website">
            <small class="form-text text-muted">ページのタイプを指定（例：website, article, blog, video.movie等）※初期値：website</small>
        </div>

        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <div class="row">
                <div class="col-xl-3"></div>
                <div class="col-9 col-xl-6 mx-auto">
                    <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
                </div>
                @if (Configs::getConfigsValueAndOld($configs, 'og_image', null))
                    <div class="col-3 col-xl-3 text-right">
                        <a data-toggle="collapse" href="#collapse_delete_og_image">
                            <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="d-none d-md-inline"> OG画像削除</span></span>
                        </a>
                    </div>
                @else
                    <div class="col-xl-3"></div>
                @endif
            </div>
        </div>
    </form>
</div>
</div>

@if (Configs::getConfigsValueAndOld($configs, 'og_image', null))
    <div id="collapse_delete_og_image" class="collapse mt-3">
        <div class="card border-danger">
            <div class="card-body">
                <span class="text-danger">OG画像を削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/manage/site/deleteOgImage')}}" method="POST">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('OG画像を削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- custom-file-inputクラスでファイル選択時にファイル名表示 --}}
<script>
function handleOgImageFileSelect(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        
        // ファイル名をlabelに表示（ファビコンと同様）
        $(input).next('.custom-file-label').html(file.name);
        
        // プレビュー画像を表示
        var reader = new FileReader();
        reader.onload = function(e) {
            // 既存のプレビューを削除
            var existingPreview = document.querySelector('.og-image-preview');
            if (existingPreview) {
                existingPreview.remove();
            }
            
            // 新しいプレビューを作成
            var previewDiv = document.createElement('div');
            previewDiv.className = 'mb-2 og-image-preview';
            previewDiv.innerHTML = '<small class="text-muted">選択された画像:</small><br><img src="' + e.target.result + '" alt="プレビュー" style="max-width: 200px; max-height: 100px;" class="img-thumbnail">';
            
            // custom-fileの前に挿入
            var customFile = document.querySelector('.custom-file');
            customFile.parentNode.insertBefore(previewDiv, customFile);
        };
        reader.readAsDataURL(file);
    }
}
</script>

@endsection
