{{--
 * 課題管理 試験日時CSVインポート画面テンプレート
--}}
@extends('core.cms_frame_base')

{{-- 編集画面側のフレームメニュー --}}
@include('plugins.user.learningtasks.learningtasks_setting_edit_tab')

@section("plugin_contents_$frame->id")

{{-- 課題名 --}}
<div class="card mb-3 border-danger">
    <div class="card-body">
        <h5 class="mb-0">{!!$learningtasks_posts->post_title!!}</h5>
    </div>
</div>

@if (session('flash_message'))
    <div class="alert alert-success">
        {{ session('flash_message') }}
    </div>
@endif

<div class="alert alert-info" role="alert">
    <i class="fas fa-exclamation-circle"></i> CSVファイルを使って、試験日時を一括登録できます。
</div>

{{-- ダウンロード用フォーム --}}
<form action="{{url('/')}}/download/plugin/learningtasks/downloadCsvFormatExaminations/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}" method="post" name="learningtask_download_csv_format_examination">
    {{ csrf_field() }}
    <input type="hidden" name="character_code" value="">
</form>

<script type="text/javascript">
    {{-- ダウンロードのsubmit JavaScript --}}
    function submit_download_csv_format_shift_jis() {
        learningtask_download_csv_format_examination.character_code.value = '{{CsvCharacterCode::sjis_win}}';
        learningtask_download_csv_format_examination.submit();
    }
    function submit_download_csv_format_utf_8() {
        learningtask_download_csv_format_examination.character_code.value = '{{CsvCharacterCode::utf_8}}';
        learningtask_download_csv_format_examination.submit();
    }
</script>

{{-- post先 --}}
<form action="{{url('/')}}/redirect/plugin/learningtasks/uploadCsvExaminations/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" method="POST" class="form-horizontal" enctype="multipart/form-data">
    {{ csrf_field() }}
    {{-- post後、再表示するURL --}}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/importExaminations/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}">

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
        <label class="{{$frame->getSettingLabelClass()}} pt-0" for="examinations_csv">CSVファイル <span class="badge badge-danger">必須</span></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="examinations_csv" name="examinations_csv" accept=".csv">
                <label class="custom-file-label" for="examinations_csv" data-browse="参照"></label>
            </div>
            @if ($errors && $errors->has('examinations_csv'))
                @foreach ($errors->get('examinations_csv') as $message)
                    <div class="text-danger">{{$message}}</div>
                @endforeach
            @endif
            <small class="text-muted">※ アップロードできる１ファイルの最大サイズ: {{ini_get('upload_max_filesize')}}</small><br />
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
    <div class="text-center">
        <a href="{{url('/')}}/plugin/learningtasks/editExaminations/{{$page->id}}/{{$frame->id}}/{{$learningtasks_posts->id}}#frame-{{$frame->id}}" class="mr-2 btn btn-secondary">
            <i class="fas fa-angle-left"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> 試験設定へ</span>
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
