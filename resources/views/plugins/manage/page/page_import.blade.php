{{--
 * Page インポート画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分への挿入 --}}
@section('manage_content')

<div class="card mb-1">
<div class="card-header p-0">

{{-- 機能選択タブ --}}
@include('plugins.manage.page.page_manage_tab')

</div>
</div>

{{-- エラー表示 --}}
@if ($errors)
<div class="alert alert-danger my-3">
    @foreach($errors as $error)
        <i class="fas fa-exclamation-circle"></i>
        {{$error}}<br />
    @endforeach
</div>
@endif

{{-- インポート画面(入力フォーム) --}}
<div class="card">
    <div class="card-body">
        <form action="{{url('/manage/page/upload')}}" method="POST" class="form-horizontal" enctype="multipart/form-data">
            {{csrf_field()}}

            <div class="form-group mx-auto mt-2 mb-0">
                <label for="File">CSVファイル</label>
                <input type="file" name="page_csv" class="form-control-file" id="File">
            </div>

            <div class="form-group mx-auto col-md-3 mt-2 mb-0">
                <button type="submit" class="btn btn-primary form-horizontal" onclick="javascript:return confirm('ページをインポートします。\nよろしいですか？')">
                    <i class="fas fa-check"></i> インポート
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">CSVフォーマット</div>
    <div class="card-body">
<pre>
"page_name","permanent_link","background_color","header_color","theme","layout","base_display_flag"
"アップロード","/upload","NULL","NULL","NULL","NULL","1"
"アップロード2","/upload/2","NULL","NULL","NULL","NULL","1"
</pre>
※ 文字コードはShift_JIS

    </div>
</div>


@endsection
