{{--
 * CSVダウンロード画面テンプレート
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
        {{-- ダウンロード用フォーム --}}
        <form action="{{url('/')}}/manage/code/downloadCsv" method="post" name="database_download_csv">
            {{ csrf_field() }}
            <input type="hidden" name="character_code" value="">
        </form>

        <script type="text/javascript">
            {{-- ダウンロードのsubmit JavaScript --}}
            function submit_download_shift_jis() {
                database_download_csv.character_code.value = '{{CsvCharacterCode::sjis_win}}';
                database_download_csv.submit();
            }
            function submit_download_utf_8() {
                database_download_csv.character_code.value = '{{CsvCharacterCode::utf_8}}';
                database_download_csv.submit();
            }
        </script>

        {{-- post先 --}}
        <form action="{{url('/')}}/manage/code/uploadCsv" method="POST" class="form-horizontal" enctype="multipart/form-data">
            {{ csrf_field() }}
            {{-- post後、再表示するURL --}}
            <input type="hidden" name="redirect_path" value="{{url('/')}}/manage/code/import">

            {{-- Submitボタン --}}
            <div class="form-group text-center">
                <div class="row">
                    <div class="offset-sm-3 col-sm-6">

                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" onclick="submit_download_shift_jis({{$plugin->id}});">
                                <i class="fas fa-file-download"></i> ダウンロード
                            </button>
                            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="sr-only">ドロップダウンボタン</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="submit_download_shift_jis({{$plugin->id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                                <a class="dropdown-item" href="#" onclick="submit_download_utf_8({{$plugin->id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection
