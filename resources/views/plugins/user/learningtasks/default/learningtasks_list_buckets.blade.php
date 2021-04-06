{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.learningtasks.learningtasks_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- ダウンロード用フォーム --}}
<form action="" method="post" name="learningtask_download" class="d-inline">
    {{ csrf_field() }}
    <input type="hidden" name="character_code" value="">
</form>

<script type="text/javascript">
    {{-- ダウンロードのsubmit JavaScript --}}
    function submit_download_shift_jis(id) {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}で試験申し込み者一覧をダウンロードします。\nよろしいですか？') ) {
            return;
        }
        learningtask_download.action = "{{url('/')}}/download/plugin/learningtasks/downloadCsv/{{$page->id}}/{{$frame_id}}/" + id;
        learningtask_download.character_code.value = '{{CsvCharacterCode::sjis_win}}';
        learningtask_download.submit();
    }
    function submit_download_utf_8(id) {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}で試験申し込み者一覧をダウンロードします。\nよろしいですか？') ) {
            return;
        }
        learningtask_download.action = "{{url('/')}}/download/plugin/learningtasks/downloadCsv/{{$page->id}}/{{$frame_id}}/" + id;
        learningtask_download.character_code.value = '{{CsvCharacterCode::utf_8}}';
        learningtask_download.submit();
    }
</script>

<form action="{{url('/')}}/plugin/learningtasks/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group">
        <table class="table table-hover {{$frame->getSettingTableClass()}}">
        <thead>
            <tr>
                <th></th>
                <th>課題管理名</th>
                <th>詳細</th>
                <th>作成日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($learningtasks as $learningtask)
            <tr @if ($learningtasks_frame->bucket_id == $learningtask->bucket_id) class="cc-active-tr"@endif>
                <td>
                    <input type="radio" value="{{$learningtask->bucket_id}}" name="select_bucket"@if ($learningtasks_frame->bucket_id == $learningtask->bucket_id) checked @endif>
                    <span class="{{$frame->getSettingCaptionClass()}}">{{$learningtask->learningtasks_name}}</span>
                </td>
                <td class="{{$frame->getNarrowDisplayNone()}}">{{$learningtask->learningtasks_name}}</td>
                <td nowrap>
                    <div class="btn-group mr-1">
                        <button type="button" class="btn btn-primary btn-sm" onclick="submit_download_shift_jis({{$learningtask->id}});">
                            <i class="fas fa-file-download"></i> ダウンロード
                        </button>
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="sr-only">ドロップダウンボタン</span>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" onclick="submit_download_shift_jis({{$learningtask->id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                            <a class="dropdown-item" href="#" onclick="submit_download_utf_8({{$learningtask->id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
                        </div>
                    </div>
                </td>
                <td>{{$learningtask->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <div class="text-center">
        {{ $learningtasks->fragment('frame-' . $frame_id)->links() }}
    </div>

    <div class="form-group text-center mt-3">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示課題管理変更</button>
    </div>
</form>
@endsection
