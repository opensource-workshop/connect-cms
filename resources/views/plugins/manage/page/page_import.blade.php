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

        {{-- 共通エラーメッセージ 呼び出し --}}
        @include('plugins.common.errors_form_line')

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        <div class="alert alert-info" role="alert">
            <i class="fas fa-exclamation-circle"></i> CSVファイルを使って、ページを一括登録できます。詳細は<a href="https://connect-cms.jp/manual/manager/page#frame-377" target="_blank">オンラインマニュアルのページ管理ページ <i class="fas fa-external-link-alt"></i></a>を参照してください。
        </div>

        {{-- インポート画面(入力フォーム) --}}
        <form action="{{url('/manage/page/upload')}}" method="POST" class="form-horizontal" enctype="multipart/form-data">
            {{csrf_field()}}

            <div class="form-group row">
                <label for="page_name" class="col-md-3 col-form-label text-md-right">CSVファイル</label>
                <div class="col-md-9">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @if ($errors->has('page_csv')) border-danger @endif" id="page_csv" name="page_csv" accept=".csv">
                        <label class="custom-file-label @if ($errors->has('page_csv')) border-danger @endif" for="page_csv" data-browse="参照"></label>
                    </div>
                    @if ($errors->has('page_csv'))
                        @foreach ($errors->get('page_csv') as $message)
                            <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$message}}</div>
                        @endforeach
                    @endif
                    <small class="text-muted">※ アップロードできる１ファイルの最大サイズ: {{ini_get('upload_max_filesize')}}</small><br />
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

{{-- custom-file-inputクラスでファイル選択時にファイル名表示 --}}
<script>
    $('.custom-file-input').on('change',function(){
        $(this).next('.custom-file-label').html($(this)[0].files[0].name);
    })
</script>

@endsection
