{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.databases.databases_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@auth
    {{-- 選択されているBucketがあるかの判定 --}}
    @php
        $plugin_selected = false;
    @endphp

    @foreach($plugins as $plugin)
        @if ($plugin_frame->bucket_id == $plugin->bucket_id)
            @php
                $plugin_selected = true;
            @endphp
        @endif
    @endforeach

    @if (!$plugin_selected)
        <div class="alert alert-warning" style="margin-top: 10px;">
            <i class="fas fa-exclamation-circle"></i>
            {{$frame->plugin_name_full}}を選択するか、{{$frame->plugin_name_full}}作成で作成してください。
        </div>
    @endif

    {{-- ダウンロード用フォーム --}}
    <form action="" method="post" name="database_download" class="d-inline">
        {{ csrf_field() }}
        <input type="hidden" name="character_code" value="">
    </form>

    <script type="text/javascript">
        {{-- ダウンロードのsubmit JavaScript --}}
        function submit_download_shift_jis(id) {
            if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}で登録データをダウンロードします。\nよろしいですか？') ) {
                return;
            }
            database_download.action = "{{url('/')}}/download/plugin/databases/downloadCsv/{{$page->id}}/{{$frame_id}}/" + id;
            database_download.character_code.value = '{{CsvCharacterCode::sjis_win}}';
            database_download.submit();
        }
        function submit_download_utf_8(id) {
            if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}で登録データをダウンロードします。\nよろしいですか？') ) {
                return;
            }
            database_download.action = "{{url('/')}}/download/plugin/databases/downloadCsv/{{$page->id}}/{{$frame_id}}/" + id;
            database_download.character_code.value = '{{CsvCharacterCode::utf_8}}';
            database_download.submit();
        }
    </script>

    <form action="{{url('/')}}/plugin/{{$frame->plugin_name}}/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" class="">
        {{ csrf_field() }}
        <div class="form-group">
            <table class="table table-hover table-responsive">
            <thead>
                <tr>
                    <th></th>
                    <th>{{$frame->plugin_name_full}}名</th>
                    <th>件数</th>
                    <th>詳細</th>
                    <th>作成日</th>
                </tr>
            </thead>
            <tbody>
            @foreach($plugins as $plugin)
                <tr @if ($plugin->id == $plugin_frame->id) class="active"@endif>
                    <td nowrap><input type="radio" value="{{$plugin->bucket_id}}" name="select_bucket"@if ($plugin_frame->bucket_id == $plugin->bucket_id) checked @endif></td>
                    <td nowrap>{{$plugin->plugin_bucket_name}}</td>
                    <td nowrap class="text-right">{{$plugin->entry_count}}</td>
                    <td nowrap>
                        <div class="btn-group mr-1">
                            <button class="btn btn-success btn-sm" type="button" onclick="location.href='{{url('/')}}/plugin/databases/editBuckets/{{$page->id}}/{{$frame_id}}/{{$plugin->id}}#frame-{{$frame_id}}'">
                                <i class="far fa-edit"></i> 設定変更
                            </button>
                            <button type="button" class="btn btn-success btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="sr-only">ドロップダウンボタン</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{url('/')}}/plugin/databases/createBuckets/{{$page->id}}/{{$frame_id}}/{{$plugin->id}}#frame-{{$frame_id}}">コピーしてDB作成へ</a>
                            </div>
                        </div>

                        <div class="btn-group mr-1">
                            <button type="button" class="btn btn-primary btn-sm" onclick="submit_download_shift_jis({{$plugin->id}});">
                                <i class="fas fa-file-download"></i> ダウンロード
                            </button>
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="sr-only">ドロップダウンボタン</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="submit_download_shift_jis({{$plugin->id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}）</a>
                                <a class="dropdown-item" href="#" onclick="submit_download_utf_8({{$plugin->id}}); return false;">ダウンロード（{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}）</a>
                            </div>
                        </div>

                        <button type="button" class="btn btn-success btn-sm" onclick="location.href='{{url('/')}}/plugin/databases/import/{{$page->id}}/{{$frame_id}}/{{$plugin->id}}#frame-{{$frame_id}}'">
                            <i class="fas fa-file-upload"></i> インポート
                        </button>
                    </td>
                    <td nowrap>{{$plugin->created_at}}</td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>

        <div class="text-center">
            {{ $plugins->fragment('frame-' . $frame_id)->links() }}
        </div>

        <div class="form-group text-center mt-3">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span></button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示{{$frame->plugin_name_full}}変更</button>
        </div>
    </form>
@endauth
@endsection
