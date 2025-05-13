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
                        <table class="table table-hover table-sm" id="sortable-columns">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center text-nowrap">
                                        表示順 <a class="fas fa-info-circle" data-toggle="tooltip" data-html="true" title="<i class='fa-solid fa-grip-vertical'></i> をつまんで移動(ドラック＆ドロップ)すると表示順を変更できます。"></a>
                                    </th>
                                    <th class="text-center text-nowrap" style="min-width: 165px;">項目名</th>
                                    <th class="text-center text-nowrap" style="min-width: 165px;">型</th>
                                    <th class="text-center text-nowrap">必須</th>
                                    <th class="text-center">行<a href="#frame-{{$frame_id}}" data-toggle="tooltip" data-placement="top" title="行グループ">...</a></th>
                                    <th class="text-center">列<a href="#frame-{{$frame_id}}" data-toggle="tooltip" data-placement="top" title="列グループ">...</a></th>
                                    <th class="text-center text-nowrap">詳細
                                        <a href="https://manual.connect-cms.jp/user/databases/editColumnDetail/index.html" target="_brank">
                                            <i class="fas fa-question-circle" data-toggle="tooltip" title="オンラインマニュアルはこちら"></i>
                                        </a>
                                    </th>
                                    <th class="text-center text-nowrap">更新</th>
                                    <th class="text-center text-nowrap">削除</th>
                                </tr>
                            </thead>
                                {{-- 更新用の行 --}}
                                @foreach($columns as $column)
                                    <tbody>
                                        @include('plugins.user.databases.default.databases_edit_row')
                                    </tbody>
                                @endforeach
                            <tfoot>
                                {{-- 新規登録用の行 --}}
                                <tr class="thead-light">
                                    <th colspan="9">【項目の追加行】</th>
                                </tr>
                                @include('plugins.user.databases.default.databases_edit_row_add')
                            </tfoot>
                        </table>
                    </div>

                    <script>
                        // ドラック＆ドロップで表示順変更
                        let el = document.getElementById('sortable-columns');
                        new Sortable(el, {
                            handle: '.sortable-handle',
                            animation: 150,
                            onUpdate: function (evt) {
                                database_columns.action = "{{url('/')}}/plugin/databases/updateColumnSequenceAll/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                                database_columns.submit();
                            },
                        });
                    </script>

                    {{-- ボタンエリア --}}
                    <div class="text-center mt-3 mt-md-0">
                        {{-- キャンセルボタン --}}
                        <a class="btn btn-secondary" href="{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}">
                            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                        </a>
                    </div>
                </form>
            </div>
        @endif
    @endauth
@endsection
