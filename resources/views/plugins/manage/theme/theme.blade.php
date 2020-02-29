{{--
 * テーマ管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category テーマ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card mb-3">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.theme.theme_manage_tab')
    </div>
</div>

<form action="{{url('/')}}/manage/theme/editCss" method="post" name="form_css" class="d-inline">
    {{ csrf_field() }}
    <input type="hidden" name="dir_name" value="">
</form>

<form action="{{url('/')}}/manage/theme/editJs" method="post" name="form_js" class="d-inline">
    {{ csrf_field() }}
    <input type="hidden" name="dir_name" value="">
</form>

<form action="{{url('/')}}/manage/theme/editName" method="post" name="form_name" class="d-inline">
    {{ csrf_field() }}
    <input type="hidden" name="dir_name" value="">
</form>

<form action="{{url('/')}}/manage/theme/listImages" method="post" name="form_images" class="d-inline">
    {{ csrf_field() }}
    <input type="hidden" name="dir_name" value="">
</form>

<script type="text/javascript">
    // CSS 編集画面へ
    function view_css_edit(dir_name)
    {
        form_css.dir_name.value = dir_name;
        form_css.submit();
    }
    // Javascript 編集画面へ
    function view_js_edit(dir_name)
    {
        form_js.dir_name.value = dir_name;
        form_js.submit();
    }
    // テーマ名編集画面へ
    function view_name_edit(dir_name)
    {
        form_name.dir_name.value = dir_name;
        form_name.submit();
    }
    // 画像一覧画面へ
    function view_list_images(dir_name)
    {
        form_images.dir_name.value = dir_name;
        form_images.submit();
    }
</script>

<ul class="list-group mb-3">
    <li class="list-group-item bg-light">ユーザ・テーマ一覧</li>
    @foreach($dirs as $dir)
        <li class="list-group-item">
            {{$dir['dir']}}（{{$dir['theme_name']}}）　
               <a href="javascript:view_css_edit('{{$dir['dir']}}');">［CSS編集］</a>
               <a href="javascript:view_js_edit('{{$dir['dir']}}');">［JavaScript編集］</a>
               <a href="javascript:view_list_images('{{$dir['dir']}}');">［画像管理］</a>
               <a href="javascript:view_name_edit('{{$dir['dir']}}');">［テーマ編集］</a>
        </li>
    @endforeach
</ul>

<div class="card">
    <div class="card-header">
        新規作成
    </div>
    <div class="card-body">
        <form action="/manage/theme/create" method="POST">
            {{csrf_field()}}

            {{-- ディレクトリ名 --}}
            <div class="form-group row">
                <label for="dir_name" class="col-md-3 col-form-label text-md-right">ディレクトリ名</label>
                <div class="col-md-9">
                    @if ($errors)
                    <input type="text" name="dir_name" id="dir_name" value="{{old('dir_name', '')}}" class="form-control">
                    @else
                    <input type="text" name="dir_name" id="dir_name" value="" class="form-control">
                    @endif
                    @if ($errors && $errors->has('dir_name')) <div class="text-danger">{{$errors->first('dir_name')}}</div> @endif
                </div>
            </div>
            {{-- テーマ名 --}}
            <div class="form-group row">
                <label for="theme_name" class="col-md-3 col-form-label text-md-right">テーマ名</label>
                <div class="col-md-9">
                    @if ($errors)
                    <input type="text" name="theme_name" id="theme_name" value="{{old('theme_name', '')}}" class="form-control">
                    @else
                    <input type="text" name="theme_name" id="theme_name" value="" class="form-control">
                    @endif
                    @if ($errors && $errors->has('theme_name')) <div class="text-danger">{{$errors->first('theme_name')}}</div> @endif
                </div>
            </div>
            <div class="form-group row">
                <div class="offset-sm-3 col-sm-6">
                    <button type="submit" class="btn btn-primary form-horizontal">
                        <i class="fas fa-check"></i> 新規作成
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
