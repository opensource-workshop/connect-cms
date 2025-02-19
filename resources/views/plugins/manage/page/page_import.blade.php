{{--
 * Page インポート画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分への挿入 --}}
@section('manage_content')

{{-- ダウンロード用フォーム --}}
<form action="" method="post" name="page_download_csv_format">
    {{ csrf_field() }}
    <input type="hidden" name="character_code" value="{{CsvCharacterCode::sjis_win}}">
</form>

<script type="text/javascript">
    /** フォーマットダウンロード */
    function submit_download_csv_format_shift_jis() {
        page_download_csv_format.action = '{{url('/manage/page/downloadCsvFormat')}}';
        page_download_csv_format.submit();
    }

    /** サンプルダウンロード */
    function submit_download_csv_format_shift_jis_sample() {
        page_download_csv_format.action = '{{url('/manage/page/downloadCsvSample')}}';
        page_download_csv_format.submit();
    }
</script>

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
            <i class="fas fa-exclamation-circle"></i> CSVファイルを使って、ページを一括登録できます。詳細は<a href="https://manual.connect-cms.jp/manage/page/upload/index.html" target="_blank">オンラインマニュアルのページ管理ページ <i class="fas fa-external-link-alt"></i></a>を参照してください。
        </div>

        <div class="form-group row">
            <div class="col text-right">
                <div class="btn-group">
                    <a href="#" onclick="submit_download_csv_format_shift_jis(); return false;">
                        CSVファイルのフォーマット
                    </a>
                    <button type="button" class="btn btn-sm btn-link dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="sr-only">ドロップダウンボタン</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#" onclick="submit_download_csv_format_shift_jis(); return false;">CSVファイルのフォーマット（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                        <a class="dropdown-item" href="#" onclick="submit_download_csv_format_shift_jis_sample(); return false;">CSVファイルのサンプル（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- インポート画面(入力フォーム) --}}
        <form action="{{url('/manage/page/upload')}}" method="post" class="form-horizontal" enctype="multipart/form-data">
            {{csrf_field()}}

            <div class="form-group row">
                <label for="page_name" class="col-md-3 col-form-label text-md-right">CSVファイル</label>
                <div class="col-md-9">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="page_csv" name="page_csv" accept=".csv">
                        <label class="custom-file-label @if ($errors->has('page_csv')) border-danger @endif" for="page_csv" data-browse="参照"></label>
                    </div>
                    @include('plugins.common.errors_inline', ['name' => 'page_csv'])
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

{{-- custom-file-inputクラスでファイル選択時にファイル名表示 --}}
<script>
    $('.custom-file-input').on('change',function(){
        $(this).next('.custom-file-label').html($(this)[0].files[0].name);
    })
</script>

@endsection
