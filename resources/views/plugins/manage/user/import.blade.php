{{--
 * CSVインポート画面テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
--}}

{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

{{-- ダウンロード用フォーム --}}
<form method="post" name="user_download" action="{{url('/')}}/manage/user/downloadCsv">
    {{ csrf_field() }}
    <input type="hidden" name="character_code" value="">
</form>

<script type="text/javascript">
    /** ダウンロードのsubmit shift_jis */
    function submit_download_shift_jis(columns_set_id) {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}でユーザをダウンロードします。\nよろしいですか？\n※ 抽出条件はユーザ一覧の絞り込み条件が適用されます。') ) {
            return;
        }
        user_download.character_code.value = '{{CsvCharacterCode::sjis_win}}';
        user_download.action = "{{url('/')}}/manage/user/downloadCsv/" + columns_set_id;
        user_download.submit();
    }
    /** ダウンロードのsubmit utf_8 */
    function submit_download_utf_8(columns_set_id) {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}でユーザをダウンロードします。\nよろしいですか？\n※ 抽出条件はユーザ一覧の絞り込み条件が適用されます。') ) {
            return;
        }
        user_download.character_code.value = '{{CsvCharacterCode::utf_8}}';
        user_download.action = "{{url('/')}}/manage/user/downloadCsv/" + columns_set_id;
        user_download.submit();
    }
</script>

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.user.user_manage_tab')
    </div>
    <div class="card-body">

        {{-- 共通エラーメッセージ 呼び出し --}}
        @include('plugins.common.errors_form_line')

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        <div class="alert alert-info" role="alert">
            <i class="fas fa-exclamation-circle"></i> CSVファイルを使って、ユーザを一括登録できます。詳細は<a href="https://manual.connect-cms.jp/manage/user/import/index.html" target="_blank">オンラインマニュアルのユーザ管理ページ <i class="fas fa-external-link-alt"></i></a>を参照してください。
        </div>

        {{-- ダウンロード用フォーム --}}
        <form action="{{url('/')}}/manage/user/downloadCsvFormat" method="post" name="database_download_csv_format">
            {{ csrf_field() }}
            <input type="hidden" name="character_code" value="">
        </form>

        <script type="text/javascript">
            {{-- ダウンロードのsubmit JavaScript --}}
            function submit_download_csv_format_shift_jis(columns_set_id) {
                database_download_csv_format.character_code.value = '{{CsvCharacterCode::sjis_win}}';
                database_download_csv_format.action = "{{url('/')}}/manage/user/downloadCsvFormat/" + columns_set_id;
                database_download_csv_format.submit();
            }
            function submit_download_csv_format_utf_8(columns_set_id) {
                database_download_csv_format.character_code.value = '{{CsvCharacterCode::utf_8}}';
                database_download_csv_format.action = "{{url('/')}}/manage/user/downloadCsvFormat/" + columns_set_id;
                database_download_csv_format.submit();
            }
        </script>

        {{-- post先 --}}
        <form action="{{url('/')}}/manage/user/uploadCsv" method="POST" class="form-horizontal" enctype="multipart/form-data">
            {{ csrf_field() }}

            <div class="form-group row">
                <div class="col text-right">
                    <div class="btn-group">
                        @if (config('connect.USE_USERS_COLUMNS_SET'))
                            <button type="button" class="btn btn-sm btn-link dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                CSVファイルのフォーマット
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                @foreach($columns_sets as $columns_set)
                                    <a class="dropdown-item" href="#" onclick="submit_download_csv_format_utf_8({{$columns_set->id}}); return false;">ユーザ一覧({{$columns_set->name}})ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                                    <a class="dropdown-item" href="#" onclick="submit_download_csv_format_utf_8({{$columns_set->id}}); return false;">ユーザ一覧({{$columns_set->name}})ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
                                @endforeach
                            </div>
                        @else
                            <a href="#" onclick="submit_download_csv_format_shift_jis({{$columns_set_id}}); return false;" class="btn btn-sm btn-link">
                                CSVファイルのフォーマット
                            </a>
                            <button type="button" class="btn btn-sm btn-link dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="sr-only">ドロップダウンボタン</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#" onclick="submit_download_csv_format_utf_8({{$columns_set_id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                                <a class="dropdown-item" href="#" onclick="submit_download_csv_format_utf_8({{$columns_set_id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if (config('connect.USE_USERS_COLUMNS_SET'))
                <div class="form-group form-row">
                    <label for="columns_set_id" class="col-md-3 col-form-label text-md-right">項目セット <span class="badge badge-danger">必須</span></label>
                    <div class="col-md-9">
                        <select name="columns_set_id" id="columns_set_id" class="form-control @if ($errors->has('columns_set_id')) border-danger @endif">
                            <option value=""></option>
                            @foreach ($columns_sets as $columns_set)
                                <option value="{{$columns_set->id}}" @if (old('columns_set_id') == $columns_set->id) selected="selected" @endif>{{$columns_set->name}}</option>
                            @endforeach
                        </select>
                        @include('plugins.common.errors_inline', ['name' => 'columns_set_id'])
                    </div>
                </div>
            @else
                <input type="hidden" name="columns_set_id" value="{{$columns_set_id}}">
            @endif

            <div class="form-group form-row">
                <label class="col-md-3 col-form-label text-md-right">CSVファイル <span class="badge badge-danger">必須</span></label>
                <div class="col-md-9">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @if ($errors->has('users_csv')) is-invalid @endif" id="users_csv" name="users_csv" accept=".csv">
                        <label class="custom-file-label" for="users_csv" data-browse="参照"></label>
                    </div>
                    @if ($errors && $errors->has('users_csv'))
                        @foreach ($errors->get('users_csv') as $message)
                            <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$message}}</div>
                        @endforeach
                    @endif
                    <small class="text-muted">※ アップロードできる１ファイルの最大サイズ: {{ini_get('upload_max_filesize')}}</small><br />
                    <small class="text-muted">※ ログインユーザ（自分）の更新はできません。ログインユーザの更新はユーザ一覧より更新してください。</small><br />
                    <small class="text-muted">※ ユーザを新規登録するする場合、「id」列には「""(空)」を設定してください。</small><br />
                    <small class="text-muted">
                        ※ 既存のユーザを更新する場合、「id」列には既存ユーザのidを設定してください。既存ユーザのidは下部のダウンロードファイルで確認できます。
                        <div class="col text-left">
                            {{-- (右側)ダウンロードボタン --}}
                            <div class="btn-group">
                                @if (config('connect.USE_USERS_COLUMNS_SET'))
                                    <button type="button" class="btn btn-link dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-file-download"></i> ダウンロード
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        @foreach($columns_sets as $columns_set)
                                            <a class="dropdown-item" href="#" onclick="submit_download_shift_jis({{$columns_set->id}}); return false;">ユーザ一覧({{$columns_set->name}})ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                                            <a class="dropdown-item" href="#" onclick="submit_download_utf_8({{$columns_set->id}}); return false;">ユーザ一覧({{$columns_set->name}})ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
                                        @endforeach
                                        <a class="dropdown-item" href="https://manual.connect-cms.jp/manage/user/index.html" target="_brank">
                                            <span class="btn btn-link"><i class="fas fa-question-circle"></i> オンラインマニュアル</span>
                                        </a>
                                    </div>
                                @else
                                    <button type="button" class="btn btn-link" onclick="submit_download_shift_jis({{$columns_set_id}});">
                                        <i class="fas fa-file-download"></i> ダウンロード
                                    </button>
                                    <button type="button" class="btn btn-link dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="sr-only">ドロップダウンボタン</span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item" href="#" onclick="submit_download_shift_jis({{$columns_set_id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                                        <a class="dropdown-item" href="#" onclick="submit_download_utf_8({{$columns_set_id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
                                        <a class="dropdown-item" href="https://manual.connect-cms.jp/manage/user/index.html" target="_brank">
                                            <span class="btn btn-link"><i class="fas fa-question-circle"></i> オンラインマニュアル</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </small>
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
                    @include('plugins.common.errors_inline', ['name' => 'character_code'])
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
