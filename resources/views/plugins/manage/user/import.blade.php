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
        @include('plugins.manage.user.user_manage_tab')
    </div>
    <div class="card-body">

        {{-- 共通エラーメッセージ 呼び出し --}}
        @include('common.errors_form_line')

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        <div class="alert alert-info" role="alert">
            <i class="fas fa-exclamation-circle"></i> CSVファイルを使って、ユーザを一括登録できます。詳細は<a href="https://connect-cms.jp/manual/manager/user" target="_blank">オンラインマニュアルのユーザ管理ページ <i class="fas fa-external-link-alt"></i></a>を参照してください。
        </div>

        {{-- ダウンロード用フォーム --}}
        <form action="{{url('/')}}/manage/user/downloadCsvFormat" method="post" name="database_download_csv_format">
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
        <form action="{{url('/')}}/manage/user/uploadCsv" method="POST" class="form-horizontal" enctype="multipart/form-data">
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
                <label class="col-md-3 col-form-label text-md-right">CSVファイル <span class="badge badge-danger">必須</span></label>
                <div class="col-md-9">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @if ($errors->has('users_csv')) border-danger @endif" id="users_csv" name="users_csv">
                        <label class="custom-file-label @if ($errors->has('users_csv')) border-danger @endif" for="users_csv" data-browse="参照"></label>
                    </div>
                    @if ($errors && $errors->has('users_csv'))
                        @foreach ($errors->get('users_csv') as $message)
                            <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$message}}</div>
                        @endforeach
                    @endif
                    <small class="text-muted">※ アップロードできる１ファイルの最大サイズ: {{ini_get('upload_max_filesize')}}</small><br />
                </div>
            </div>

            <div class="form-group form-row">
                <label class="col-md-3 col-form-label text-md-right">文字コード</label>
                <div class="col-md-9">
                    <select name="character_code" class="form-control @if ($errors->has('character_code')) border-danger @endif">
                        @foreach (CsvCharacterCode::getSelectMembers() as $character_code => $character_code_display)
                            <option value="{{$character_code}}"@if(old('character_code') == $character_code) selected @endif>{{$character_code_display}}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">
                        ※ UTF-8はBOM付・BOMなしどちらにも対応しています。
                    </small>
                    @include('common.errors_inline', ['name' => 'character_code'])
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
</div>

{{-- custom-file-inputクラスでファイル選択時にファイル名表示 --}}
<script>
    $('.custom-file-input').on('change',function(){
        $(this).next('.custom-file-label').html($(this)[0].files[0].name);
    })
</script>

@endsection
