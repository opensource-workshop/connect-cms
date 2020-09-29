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
    <ul class="pl-3">
        <li>CSVファイルを使って、データベースへ一括登録できます。詳細は<a href="https://connect-cms.jp/manual/user/database#frame-178" target="_blank">こちら</a>を参照してください。</li>
    </ul>
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
                <div class="dropdown-menu">
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
            <input type="file" name="databases_csv" class="form-control-file">
            @if (UnzipUtils::useZipArchive())
                <small class="text-muted">
                    ※ CSVファイル・ZIPファイルに対応しています。
                </small>
            @endif
            @if ($errors && $errors->has('databases_csv'))
                @foreach ($errors->get('databases_csv') as $message)
                    <div class="text-danger">{{$message}}</div>
                @endforeach
            @endif
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
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame_id}}'">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i>
                    <span class="{{$frame->getSettingButtonCaptionClass()}}" onclick="return confirm('インポートします。\nよろしいですか？')">
                        インポート
                    </span>
                </button>
            </div>

        </div>
    </div>
</form>

@endsection
