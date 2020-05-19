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

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.page.page_manage_tab')
    </div>
    <div class="card-body">

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
        <form action="{{url('/manage/page/upload')}}" method="POST" class="form-horizontal" enctype="multipart/form-data">
            {{csrf_field()}}

            <div class="form-group row">
                <label for="page_name" class="col-md-3 col-form-label text-md-right">CSVファイル</label>
                <div class="col-md-9 d-sm-flex align-items-center">
                    <input type="file" name="page_csv" class="form-control-file" id="File">
                </div>
            </div>

            <div class="form-group row">
                <label for="page_name" class="col-md-3 col-form-label text-md-right">初期配置</label>
                <div class="col-md-9 d-sm-flex align-items-center">
                    <div class="custom-control custom-checkbox">
                        <input name="deploy_content_plugin" value="1" type="checkbox" class="custom-control-input" id="deploy_content_plugin">
                        <label class="custom-control-label" for="deploy_content_plugin">インポートする各ページに1つ、「固定記事プラグイン」を配置する。</label>
                    </div>
                </div>
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
