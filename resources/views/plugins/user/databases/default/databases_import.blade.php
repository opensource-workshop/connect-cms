{{--
 * CSVインポート画面テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@php
use App\Utilities\Zip\UnzipUtils;
@endphp

@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.databases.databases_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

@if (session('flash_message'))
    <div class="alert alert-success">
        {{ session('flash_message') }}
    </div>
@endif

<div class="alert alert-info" role="alert">
    <i class="fas fa-exclamation-circle"></i> CSVファイルを使って、データベースへ一括登録できます。詳細は<a href="https://connect-cms.jp/manual/user/database#frame-178" target="_blank">オンラインマニュアルのデータベースページ <i class="fas fa-external-link-alt"></i></a>を参照してください。
</div>

{{-- ダウンロード用フォーム --}}
<form action="{{url('/')}}/download/plugin/databases/downloadCsvFormat/{{$page->id}}/{{$frame_id}}/{{$database->id}}" method="post" name="database_download_csv_format">
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
<form action="{{url('/')}}/redirect/plugin/databases/uploadCsv/{{$page->id}}/{{$frame_id}}/{{$database->id}}#frame-{{$frame_id}}" method="POST" class="form-horizontal" enctype="multipart/form-data">
    {{ csrf_field() }}
    {{-- post後、再表示するURL --}}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/databases/import/{{$page->id}}/{{$frame_id}}/{{$database->id}}#frame-{{$frame_id}}">

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

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">データベース名</label>
        <div class="{{$frame->getSettingInputClass()}}">
            {{$database->databases_name}}
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">CSVファイル <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            @if (UnzipUtils::useZipArchive())
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="databases_csv{{$frame->id}}" name="databases_csv" accept=".csv, .zip">
                    <label class="custom-file-label" for="databases_csv{{$frame->id}}" data-browse="参照"></label>
                </div>
            @else
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="databases_csv{{$frame->id}}" name="databases_csv" accept=".csv">
                    <label class="custom-file-label" for="databases_csv{{$frame->id}}" data-browse="参照"></label>
                </div>
            @endif
            @if ($errors && $errors->has('databases_csv'))
                @foreach ($errors->get('databases_csv') as $message)
                    <div class="text-danger">{{$message}}</div>
                @endforeach
            @endif
            @if (UnzipUtils::useZipArchive())
                <small class="text-muted">※ CSVファイル・ZIPファイルに対応しています。</small><br />
            @endif
            <small class="text-muted">※ アップロードできる最大サイズ: {{ini_get('upload_max_filesize')}}</small><br />
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">文字コード</label>
        <div class="{{$frame->getSettingInputClass()}}">
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
        <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="btn btn-secondary mr-2">
            <i class="fas fa-angle-left"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> DB選択へ</span>
        </a>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-check"></i><span onclick="return confirm('インポートします。\nよろしいですか？')"> インポート</span>
        </button>
    </div>
</form>

{{-- custom-file-inputクラスでファイル選択時にファイル名表示 --}}
<script>
    $('.custom-file-input').on('change',function(){
        $(this).next('.custom-file-label').html($(this)[0].files[0].name);
    })
</script>

@endsection
