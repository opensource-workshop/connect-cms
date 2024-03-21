{{--
 * 項目の設定画面
 *
 * horiguchi@opensource-workshop.jp
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category RSS・プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")

    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.rsses.rsses_frame_edit_tab')

@endsection

@section("plugin_setting_$frame->id")
    @auth
        @if (empty($rsses_id))
            {{-- バケツデータ未作成の場合 --}}
            <div class="alert alert-warning mt-2">
                <i class="fas fa-exclamation-circle"></i>
                RSS選択画面から選択するか、新規作成で作成してください。
            </div>
        @else
            {{-- バケツデータ作成済みの場合 --}}
            <div class="form-group" id="app_{{ $frame->id }}">
                {{-- 項目の追加、削除等処理用の汎用フォーム --}}
                <form action="" id="rss_urls" name="rss_urls" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="rsses_id" value="{{$rsses_id}}">
                    <input type="hidden" name="url_id" value="">
                    <input type="hidden" name="display_sequence" value="">
                    <input type="hidden" name="display_sequence_operation" value="">
                    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/rsses/editUrl/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">

                    {{-- エラーメッセージエリア ※共通blade呼び出し --}}
                    @include('plugins.common.errors_form_line')

                    {{-- エラーメッセージ --}}
                    @if (session('rsses_error_message'))
                    <div class="alert alert-danger mt-2">
                        <i class="fas fa-exclamation-circle"></i> {{ session('rsses_error_message') }}
                    </div>
                    @endif

                    {{-- メッセージエリア --}}
                    <div class="alert alert-info mt-2">
                        @if (session('flash_message'))
                            <i class="fas fa-exclamation-circle"></i>{{ session('flash_message') }}
                            <a href="https://manual.connect-cms.jp/user/rsses/index.html" target="_brank"><i class="fas fa-question-circle" data-toggle="tooltip" title="オンラインマニュアルはこちら"></i></a>
                        @else
                            <ul>
                                <li>
                                    ブロックに表示させるRSS（URL）を設定します。
                                    <a href="https://manual.connect-cms.jp/user/rsses/index.html" target="_brank"><i class="fas fa-question-circle" data-toggle="tooltip" title="オンラインマニュアルはこちら"></i></a>
                                </li>
                            </ul>
                        @endif
                    </div>

                    {{-- 項目一覧 --}}
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr class="d-none d-lg-table-row">
                                    <th class="text-center text-nowrap align-middle d-block d-lg-table-cell">表示順</th>
                                    <th class="text-center text-nowrap align-middle d-block d-lg-table-cell">表示</th>
                                    <th class="text-center text-nowrap align-middle d-block d-lg-table-cell">URL <label class="badge badge-danger">必須</label></th>
                                    <th class="text-center text-nowrap align-middle d-block d-lg-table-cell">タイトル</th>
                                    <th class="text-center text-nowrap align-middle d-block d-lg-table-cell">キャプション</th>
                                    <th class="text-center text-nowrap align-middle d-block d-lg-table-cell">表示データ数</th>
                                    <th class="text-center text-nowrap align-middle d-block d-lg-table-cell">削除</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- 新規登録用の行 --}}
                                <tr>
                                    <th colspan="7">【項目の追加行】</th>
                                </tr>
                                @include('plugins.user.rsses.default.rsses_edit_row_add')
                                <tr>
                                    <th colspan="7">【既存の設定行】</th>
                                </tr>
                                {{-- 更新用の行 --}}
                                @foreach($urls as $url)
                                    @include('plugins.user.rsses.default.rsses_edit_row')
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- ボタンエリア --}}
                    <div class="text-center mt-3 mt-md-0">
                        {{-- キャンセルボタン --}}
                        <button type="button"
                            class="btn btn-secondary mr-2"
                            onclick="location.href='{{URL::to($page->permanent_link)}}'"
                        >
                            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span>
                        </button>
                        @if ($urls->count() > 0)
                            {{-- 更新ボタン --}}
                            <button
                                type="button"
                                class="btn btn-primary mr-2"
                                onclick="javascript:return submit_update_urls();"
                            >
                                <i class="fas fa-check"></i> 更新
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        @endif

        <script>
            /**
             * 項目の追加ボタン押下
             */
            function submit_add_url() {
                rss_urls.action = "{{url('/')}}/redirect/plugin/rsses/addUrl/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                rss_urls.submit();
            }

            /**
             * 項目の削除ボタン押下
             */
            function submit_delete_url(url_id) {
                if(confirm('項目を削除します。\nよろしいですか？')){
                    rss_urls.action = "{{url('/')}}/redirect/plugin/rsses/deleteUrl/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                    rss_urls.url_id.value = url_id;
                    rss_urls.submit();
                }
                return false;
            }

            /**
             * 項目の更新ボタン押下
             */
            function submit_update_urls() {
                if(confirm('更新します。\nよろしいですか？')){
                    rss_urls.action = "{{url('/')}}/redirect/plugin/rsses/updateUrls/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                    rss_urls.submit();
                }
                return false;
            }

            /**
             * 項目の表示順操作ボタン押下
             */
            function submit_display_sequence(url_id, display_sequence, display_sequence_operation) {
                rss_urls.action = "{{url('/')}}/redirect/plugin/rsses/updateUrlSequence/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                rss_urls.url_id.value = url_id;
                rss_urls.display_sequence.value = display_sequence;
                rss_urls.display_sequence_operation.value = display_sequence_operation;
                rss_urls.submit();
            }

            /**
             * ツールチップ
             */
            $(function () {
                // 有効化
                $('[data-toggle="tooltip"]').tooltip()
            })
        </script>
    @endauth
@endsection
