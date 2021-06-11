{{--
 * 項目の設定画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.databases.databases_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
    @auth
        @if (empty($databases_id))
            <div class="alert alert-warning mt-2">
                <i class="fas fa-exclamation-circle"></i> データベース選択画面から選択するか、データベース新規作成で作成してください。
            </div>
        @else
            <script type="text/javascript">
                /**
                 * 項目の追加ボタン押下
                 */
                function submit_add_column() {
                    database_columns.action = "{{url('/')}}/plugin/databases/addColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                    database_columns.submit();
                }

                /**
                 * 項目の削除ボタン押下
                 */
                function submit_delete_column(column_id) {
                    if(confirm('項目を削除します。\nよろしいですか？')){
                        database_columns.action = "{{url('/')}}/plugin/databases/deleteColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                        database_columns.column_id.value = column_id;
                        database_columns.submit();
                    }
                    return false;
                }

                /**
                 * 項目の更新ボタン押下
                 */
                function submit_update_column(column_id) {
                    database_columns.action = "{{url('/')}}/plugin/databases/updateColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                    database_columns.column_id.value = column_id;
                    database_columns.submit();
                }

                /**
                 * 項目の表示順操作ボタン押下
                 */
                function submit_display_sequence(column_id, display_sequence, display_sequence_operation) {
                    database_columns.action = "{{url('/')}}/plugin/databases/updateColumnSequence/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                    database_columns.column_id.value = column_id;
                    database_columns.display_sequence.value = display_sequence;
                    database_columns.display_sequence_operation.value = display_sequence_operation;
                    database_columns.submit();
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

            {{-- キャンセル用のフォーム。キャンセル時はセッションをクリアするため、トークン付きでPOST でsubmit したい。 --}}
            <form action="{{url('/')}}/redirect/plugin/databases/cancel/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="databases_cancel" method="POST" class="visible-lg-inline visible-md-inline visible-sm-inline visible-xs-inline">
                {{ csrf_field() }}
            </form>

            <!-- Add or Update Database Button -->
            <div class="form-group">
                <form action="" id="database_columns" name="database_columns" method="POST">
                    {{ csrf_field() }}
                    <input type="hidden" name="databases_id" value="{{$databases_id}}">
                    <input type="hidden" name="return_frame_action" value="edit">
                    <input type="hidden" name="column_id" value="">
                    <input type="hidden" name="display_sequence" value="">
                    <input type="hidden" name="display_sequence_operation" value="">

                    {{-- メッセージエリア --}}
                    <div class="alert alert-info mt-2">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message ? $message : 'ユーザが登録時の項目を設定します。' }}
                    </div>

                    {{-- ワーニングメッセージエリア --}}
                    @if (! $title_flag)
                        <div class="alert alert-warning mt-2">
                            <i class="fas fa-exclamation-circle"></i>
                            新着情報等でタイトル表示する項目が未設定です。いずれかの項目の「詳細」よりタイトル設定をしてください。
                        </div>
                    @endif

                    {{-- エラーメッセージエリア --}}
                    @if ($errors && $errors->any())
                        <div class="alert alert-danger mt-2">
                            @foreach ($errors->all() as $error)
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    @endif

                    <div class="table-responsive">
                        {{-- 項目の一覧 --}}
                        <table class="table table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center text-nowrap">表示順</th>
                                    <th class="text-center text-nowrap" style="min-width: 165px;">項目名</th>
                                    <th class="text-center text-nowrap" style="min-width: 165px;">型</th>
                                    <th class="text-center text-nowrap">必須</th>
                                    <th class="text-center">行<a href="#frame-{{$frame_id}}" data-toggle="tooltip" data-placement="top" title="行グループ">...</a></th>
                                    <th class="text-center">列<a href="#frame-{{$frame_id}}" data-toggle="tooltip" data-placement="top" title="列グループ">...</a></th>
                                    <th class="text-center text-nowrap">詳細
                                        <a href="https://connect-cms.jp/manual/user/database#frame-179" target="_brank">
                                            <i class="fas fa-question-circle" data-toggle="tooltip" title="オンラインマニュアルはこちら"></i>
                                        </a>
                                    </th>
                                    <th class="text-center text-nowrap">更新</th>
                                    <th class="text-center text-nowrap">削除</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- 更新用の行 --}}
                                @foreach($columns as $column)
                                    @include('plugins.user.databases.default.databases_edit_row')
                                @endforeach

                                {{-- 新規登録用の行 --}}
                                <tr class="thead-light">
                                    <th colspan="9">【項目の追加行】</th>
                                </tr>
                                @include('plugins.user.databases.default.databases_edit_row_add')
                            </tbody>
                        </table>
                    </div>

                    {{-- ボタンエリア --}}
                    <div class="text-center mt-3 mt-md-0">

                        {{-- キャンセルボタン --}}
                        <button type="button" class="btn btn-secondary mr-2" onclick="javascript:databases_cancel.submit();">
                            <i class="fas fa-times"></i>
                            <span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endauth
@endsection
