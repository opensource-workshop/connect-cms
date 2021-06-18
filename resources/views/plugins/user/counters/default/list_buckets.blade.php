{{--
 * 編集画面(データ選択)テンプレート
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.counters.counters_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- ダウンロード用フォーム --}}
<script type="text/javascript">
    {{-- ダウンロードのsubmit JavaScript --}}
    function submit_download_shift_jis(id) {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}でカウントデータをダウンロードします。\nよろしいですか？') ) {
            return;
        }
        database_download.action = "{{url('/')}}/download/plugin/counters/downloadCsv/{{$page->id}}/{{$frame_id}}/" + id;
        database_download.character_code.value = '{{CsvCharacterCode::sjis_win}}';
        database_download.submit();
    }
    function submit_download_utf_8(id) {
        if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}でカウントデータをダウンロードします。\nよろしいですか？') ) {
            return;
        }
        database_download.action = "{{url('/')}}/download/plugin/counters/downloadCsv/{{$page->id}}/{{$frame_id}}/" + id;
        database_download.character_code.value = '{{CsvCharacterCode::utf_8}}';
        database_download.submit();
    }
</script>
<form action="" method="post" name="database_download">
    {{ csrf_field() }}
    <input type="hidden" name="character_code" value="">
</form>

<form action="{{url('/')}}/redirect/plugin/counters/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/counters/listBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
    <div class="form-group">
        <table class="table table-hover {{$frame->getSettingTableClass()}}">
        <thead>
            <tr>
                <th></th>
                <th>カウンター名</th>
                <th>累計</th>
                <th>詳細</th>
                <th>作成日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($plugin_buckets as $plugin_bucket)
            <tr @if ($plugin_bucket->bucket_id == $frame->bucket_id) class="cc-active-tr"@endif>
                <td class="d-table-cell">
                    <input type="radio" value="{{$plugin_bucket->bucket_id}}" name="select_bucket"@if ($plugin_bucket->bucket_id == $frame->bucket_id) checked @endif>
                </td>
                <td><span class="{{$frame->getSettingCaptionClass()}}">カウンター名：</span>{{$plugin_bucket->name}}</td>
                <td><span class="{{$frame->getSettingCaptionClass()}}">累計：</span>{{$plugin_bucket->total_count}}</td>
                <td nowrap>
                    <span class="{{$frame->getSettingCaptionClass()}}">詳細：</span>

                    <a class="btn btn-success btn-sm mr-1" href="{{url('/')}}/plugin/counters/listCounters/{{$page->id}}/{{$frame_id}}/{{$plugin_bucket->id}}#frame-{{$frame_id}}">
                        <i class="fas fa-list"></i> <span class="{{$frame->getSettingButtonCaptionClass()}}">カウント</span>一覧
                    </a>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" onclick="submit_download_shift_jis({{$plugin_bucket->id}});">
                            <i class="fas fa-file-download"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> ダウンロード</span>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="sr-only">ドロップダウンボタン</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#" onclick="submit_download_shift_jis({{$plugin_bucket->id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                            <a class="dropdown-item" href="#" onclick="submit_download_utf_8({{$plugin_bucket->id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
                        </div>
                    </div>

                </td>
                <td><span class="{{$frame->getSettingCaptionClass()}}">作成日：</span>{{$plugin_bucket->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <div class="text-center">
        {{ $plugin_buckets->fragment('frame-' . $frame_id)->links() }}
    </div>

    <div class="form-group text-center mt-3">
        <a href="{{URL::to($page->permanent_link)}}" class="btn btn-secondary mr-2">
            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
        </a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示カウンター変更</button>
    </div>
</form>

@endsection
