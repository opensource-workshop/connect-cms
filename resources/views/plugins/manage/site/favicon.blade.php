{{--
 * Favicon 設定のメインテンプレート
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

        <form action="{{url('/')}}/manage/site/saveFavicon" method="POST" enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="form-group row">
                <label class="col-md-3 text-md-right">現在のファイル</label>
                <div class="col-md-9">
                    @if ($favicon)
                        <a href="{{url('/')}}/uploads/favicon/favicon.ico" target="_blank">favicon.ico</a>
                    @else
                        Favicon が設定されていません。
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right">ファビコン・ファイル</label>
                <div class="col-md-9">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @if ($errors->has('favicon')) border-danger @endif" id="favicon" name="favicon" accept=".ico">
                        <label class="custom-file-label @if ($errors->has('favicon')) border-danger @endif" for="favicon" data-browse="参照">アイコンファイル(.ico)</label>
                    </div>
                    @include('plugins.common.errors_inline', ['name' => 'favicon'])
                </div>
            </div>

            {{-- ボタンエリア --}}
            <div class="form-group text-center">
                <div class="row">
                    <div class="col-xl-3"></div>
                    <div class="col-9 col-xl-6 mx-auto">
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/site')}}'"><i class="fas fa-times"></i> キャンセル</button>
                        <button type="submit" class="btn btn-primary form-horizontal">
                            <i class="fas fa-check"></i> @if ($favicon)ファビコン更新 @else ファビコン追加 @endif
                        </button>
                    </div>
                    @if ($favicon)
                        <div class="col-3 col-xl-3 text-right">
                            <a data-toggle="collapse" href="#collapse_delete">
                                <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="d-none d-md-inline"> 削除</span></span>
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

@if ($favicon)
    <div id="collapse_delete" class="collapse mt-3">
        <div class="card border-danger">
            <div class="card-body">
                <span class="text-danger">ファビコンを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/manage/site/deleteFavicon')}}" method="POST">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('ファビコンを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- custom-file-inputクラスでファイル選択時にファイル名表示 --}}
<script>
    $('.custom-file-input').on('change',function(){
        $(this).next('.custom-file-label').html($(this)[0].files[0].name);
    })
</script>

@endsection
