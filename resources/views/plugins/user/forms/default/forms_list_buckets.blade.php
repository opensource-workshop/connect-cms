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
    @include('plugins.user.forms.forms_frame_edit_tab')
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
    <form action="" method="post" name="form_download" class="d-inline">
        {{ csrf_field() }}
        <input type="hidden" name="character_code" value="">
    </form>

    <script type="text/javascript">
        {{-- ダウンロードのsubmit JavaScript --}}
        function submit_download_shift_jis(id) {
            if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::sjis_win]}}で登録データをダウンロードします。\nよろしいですか？') ) {
                return;
            }
            form_download.action = "{{url('/')}}/download/plugin/forms/downloadCsv/{{$page->id}}/{{$frame_id}}/" + id;
            form_download.character_code.value = '{{CsvCharacterCode::sjis_win}}';
            form_download.submit();
        }
        function submit_download_utf_8(id) {
            if( !confirm('{{CsvCharacterCode::enum[CsvCharacterCode::utf_8]}}で登録データをダウンロードします。\nよろしいですか？') ) {
                return;
            }
            form_download.action = "{{url('/')}}/download/plugin/forms/downloadCsv/{{$page->id}}/{{$frame_id}}/" + id;
            form_download.character_code.value = '{{CsvCharacterCode::utf_8}}';
            form_download.submit();
        }

        /**
         * ツールチップ
         */
        $(function () {
            // 有効化
            $('[data-toggle="tooltip"]').tooltip()
            // 常時表示 ※表示の判定は項目側で実施
            $('[id^=detail-button-tip]').tooltip('show');
            $('#frame-col-tip').tooltip('show');
        })
    </script>

    <form action="{{url('/')}}/plugin/{{$frame->plugin_name}}/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
        {{ csrf_field() }}
        <div class="form-group">
            <table class="table table-hover table-responsive">
            <thead>
                <tr>
                    <th></th>
                    <th>{{$frame->plugin_name_full}}名</th>
                    <th>仮<a href="#frame-{{$frame_id}}" data-toggle="tooltip" data-placement="top" title="仮登録数">...</a></th>
                    <th>本<a href="#frame-{{$frame_id}}" data-toggle="tooltip" data-placement="top" title="本登録数">...</a></th>
                    <th>データ保存</th>
                    <th>詳細</th>
                    <th>作成日</th>
                </tr>
            </thead>
            <tbody>
            @foreach($plugins as $plugin)
                <tr @if ($plugin->id == $plugin_frame->id) class="active"@endif>
                    <td nowrap><input type="radio" value="{{$plugin->bucket_id}}" name="select_bucket"@if ($plugin_frame->bucket_id == $plugin->bucket_id) checked @endif></td>
                    <td nowrap>{{$plugin->plugin_bucket_name}}</td>
                    <td nowrap class="text-right">{{$plugin->tmp_entry_count}}</td>
                    <td nowrap class="text-right">{{$plugin->active_entry_count}}</td>
                    <td nowrap>@if ($plugin->data_save_flag) 保存する @else 保存しない @endif</td>
                    <td nowrap>
                        <a class="btn btn-success btn-sm mr-1" href="{{url('/')}}/plugin/forms/editBuckets/{{$page->id}}/{{$frame_id}}/{{$plugin->id}}#frame-{{$frame_id}}">
                            <i class="far fa-edit"></i> 設定変更
                        </a>

                        <a class="btn btn-success btn-sm mr-1" href="{{url('/')}}/plugin/forms/listInputs/{{$page->id}}/{{$frame_id}}/{{$plugin->id}}#frame-{{$frame_id}}">
                            <i class="fas fa-list"></i> 登録一覧
                        </a>

                        <div class="btn-group">
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
                    </td>
                    <td nowrap>{{$plugin->created_at}}</td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>

        {{-- ページング処理 --}}
        @include('plugins.common.user_paginate', ['posts' => $plugins, 'frame' => $frame, 'aria_label_name' => $frame->plugin_name_full . '選択', 'class' => 'form-group'])

        <div class="text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span></button>
            <button type="submit" class="btn btn-primary" id="button_list_buckets"><i class="fas fa-check"></i> 表示{{$frame->plugin_name_full}}変更</button>
        </div>
    </form>
@endauth
@endsection
