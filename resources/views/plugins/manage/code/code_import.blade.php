{{--
 * CSVインポート画面テンプレート
--}}

{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.code.code_manage_tab')
    </div>
    <div class="card-body">

    {{-- 登録後メッセージ表示 --}}
    @include('plugins.common.flash_message')

{{--
    <div class="alert alert-info" role="alert">
        <ul class="pl-3">
            CSVファイルを使って、コード管理へ一括登録できます。詳細は<a href="https://connect-cms.jp/manual/user/database#frame-178" target="_blank">こちら</a>を参照してください。
        </ul>
    </div>
--}}

    {{-- ダウンロード用フォーム --}}
    <form action="{{url('/')}}/manage/code/downloadCsvFormat" method="post" name="database_download_csv_format">
        {{ csrf_field() }}
        <input type="hidden" name="character_code" value="">
    </form>

    <script type="text/javascript">
        {{-- ダウンロードのsubmit JavaScript --}}
        function submit_download_csv_format_shift_jis() {
            database_download_csv_format.character_code.value = '{{CsvCharacterCode::sjis_win}}';
            database_download_csv_format.submit();
        }
        function submit_download_csv_format_utf_8() {
            database_download_csv_format.character_code.value = '{{CsvCharacterCode::utf_8}}';
            database_download_csv_format.submit();
        }
    </script>

    {{-- post先 --}}
    <form action="{{url('/')}}/manage/code/uploadCsv" method="POST" class="form-horizontal" enctype="multipart/form-data">
        {{ csrf_field() }}

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
                        <a class="dropdown-item" href="#" onclick="submit_download_csv_format_utf_8(); return false;">CSVファイルのフォーマット（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group form-row">
            <label for="buckets_id" class="col-md-3 col-form-label text-md-right">CSVファイル <span class="badge badge-danger">必須</span></label>
            <div class="col-md-9">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="codes_csv" name="codes_csv" accept=".csv">
                    <label class="custom-file-label" for="codes_csv" data-browse="参照"></label>
                </div>
                @if ($errors && $errors->has('codes_csv'))
                    @foreach ($errors->get('codes_csv') as $message)
                        <div class="text-danger">{{$message}}</div>
                    @endforeach
                @endif
                <small class="text-muted">※ アップロードできる１ファイルの最大サイズ: {{ini_get('upload_max_filesize')}}</small><br />
            </div>
        </div>

        <div class="form-group form-row">
            <label for="buckets_id" class="col-md-3 col-form-label text-md-right">文字コード</label>
            <div class="col-md-9">
                <select name="character_code" class="form-control">
                    @foreach (CsvCharacterCode::getSelectMembers() as $character_code => $character_code_display)
                        <option value="{{$character_code}}"@if(old('character_code') == $character_code) selected @endif>{{$character_code_display}}</option>
                    @endforeach
                </select>
                <small class="text-muted">
                    ※ UTF-8はBOM付・BOMなしどちらにも対応しています。
                </small>
                @if ($errors && $errors->has('character_code')) <div class="text-danger">{{$errors->first('character_code')}}</div> @endif
            </div>
        </div>

        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <div class="row">
                <div class="offset-sm-3 col-sm-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i>
                        <span class="" onclick="return confirm('インポートします。\nよろしいですか？')">
                            インポート
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

{{-- custom-file-inputクラスでファイル選択時にファイル名表示 --}}
<script>
    $('.custom-file-input').on('change',function(){
        $(this).next('.custom-file-label').html($(this)[0].files[0].name);
    })
</script>

@endsection
