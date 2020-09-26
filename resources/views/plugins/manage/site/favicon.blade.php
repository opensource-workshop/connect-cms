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

    @if (session('save_favicon'))
    <div class="alert alert-info" role="alert">
        {!!session('save_favicon')!!}
    </div>
    @endif

    <form action="{{url('/')}}/manage/site/saveFavicon" method="POST" enctype="multipart/form-data">
        {{csrf_field()}}
        <div class="form-group row">
            <label for="theme_name" class="col-md-3 text-md-right">現在のファイル</label>
            <div class="col-md-9">
                @if ($favicon)
                    <a href="{{url('/')}}/uploads/favicon/favicon.ico" target="_blank">favicon.ico</a>
                @else
                    Favicon が設定されていません。
                @endif
            </div>
        </div>

        <div class="form-group row">
            <label for="theme_name" class="col-md-3 text-md-right">ファビコン・ファイル</label>
            <div class="col-md-9">
                <input type="file" name="favicon" id="favicon" value="{{old('favicon')}}">
                @if ($errors && $errors->has('favicon_error')) <div class="text-danger">{{$errors->first('favicon_error')}}</div> @endif
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

@endsection
