{{--
 * 画像一覧テンプレート
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
    <div class="card-body">

    @if ($errors && $errors->has('not_extension'))
        <div class="alert alert-danger">
            <strong>{{ $errors->first('not_extension') }}</strong>
        </div>
    @endif

    <form action="{{url('/')}}/manage/theme/deleteImage" method="post" name="form_delete_image" class="d-inline">
        {{ csrf_field() }}
        <input type="hidden" name="dir_name" value="{{$dir_name}}">
        <input type="hidden" name="file_name" value="">
    </form>

    <script type="text/javascript">
        function delete_image(file_name)
        {
            if (confirm('画像を削除します。\nよろしいですか？')) {
                form_delete_image.file_name.value = file_name;
                form_delete_image.submit();
            }
        }
    </script>

    <ul class="list-group mb-3">
        <li class="list-group-item bg-light">{{$dir_name}}</li>
        @foreach($files as $file)
        <li class="list-group-item">
            {{$file}} <a href="javascript:delete_image('{{$file}}')">[削除]</a><br />
            <a href="{{url('/')}}/themes/Users/{{$dir_name}}/images/{{$file}}" target="_blank"><img src="{{url('/')}}/themes/Users/{{$dir_name}}/images/{{$file}}" class="img-fluid"></a>
        </li>
        @endforeach
    </ul>

    <form action="/manage/theme/uploadImage" method="POST" enctype="multipart/form-data">
        {{csrf_field()}}
        <input name="dir_name" type="hidden" value="{{$dir_name}}" />

        <div class="form-group row">
            <label for="theme_name" class="col-md-3 text-md-right">画像ファイル</label>
            <div class="col-md-9">
                <input type="file" name="image" id="image" value="{{old('image')}}">
                @if ($errors && $errors->has('image')) <div class="text-danger">{{$errors->first('image')}}</div> @endif
            </div>
        </div>

        <div class="offset-sm-3 col-sm-6">
            <div class="form-group mt-3">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/manage/theme/'"><i class="fas fa-times"></i> キャンセル</button>
                <button type="submit" class="btn btn-primary form-horizontal">
                    <i class="fas fa-check"></i> 画像ファイル追加
                </button>
            </div>
        </div>
    </form>
</div>

@endsection
