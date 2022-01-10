{{--
 * 項目の設定画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.forms.forms_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
    @auth
        @if (empty($forms_id))
            <div class="alert alert-warning mt-2">
                <i class="fas fa-exclamation-circle"></i>
                フォーム選択画面から選択するか、フォーム新規作成で作成してください。
            </div>
        @else

<script type="text/javascript">

    /**
     * 項目の追加ボタン押下
     */
    function submit_add_column() {
        form_columns.action = "{{url('/')}}/plugin/forms/addColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        form_columns.submit();
    }

    /**
     * 項目の削除ボタン押下
     */
    function submit_delete_column(column_id) {
        if(confirm('項目を削除します。\nよろしいですか？')){
            form_columns.action = "{{url('/')}}/plugin/forms/deleteColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
            form_columns.column_id.value = column_id;
            form_columns.submit();
        }
        return false;
    }

    /**
     * 項目の更新ボタン押下
     */
    function submit_update_column(column_id) {
        form_columns.action = "{{url('/')}}/plugin/forms/updateColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        form_columns.column_id.value = column_id;
        form_columns.submit();
    }

    /**
     * 項目のコピーボタン押下
     */
    function submit_copy_column(column_id) {
        form_columns.action = "{{url('/')}}/plugin/forms/copyColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        form_columns.column_id.value = column_id;
        form_columns.submit();
    }

    /**
     * 項目の表示順操作ボタン押下
     */
    function submit_display_sequence(column_id, display_sequence, display_sequence_operation) {
        form_columns.action = "{{url('/')}}/plugin/forms/updateColumnSequence/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        form_columns.column_id.value = column_id;
        form_columns.display_sequence.value = display_sequence;
        form_columns.display_sequence_operation.value = display_sequence_operation;
        form_columns.submit();
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

        <!-- Add or Update Form Button -->
        <div class="form-group">
            <form action="" id="form_columns" name="form_columns" method="POST">
                {{ csrf_field() }}
                <input type="hidden" name="forms_id" value="{{$forms_id}}">
                <input type="hidden" name="return_frame_action" value="edit">
                <input type="hidden" name="column_id" value="">
                <input type="hidden" name="display_sequence" value="">
                <input type="hidden" name="display_sequence_operation" value="">

                {{-- メッセージエリア --}}
                <div class="alert alert-info mt-2">
                    <i class="fas fa-exclamation-circle"></i> {{ $message ? $message : 'ユーザが登録時の項目を設定します。' }}
                </div>

                {{-- ワーニングメッセージエリア --}}
                @if ($warning_message)
                    <div class="alert alert-warning mt-2">
                        <i class="fas fa-exclamation-circle"></i> {{$warning_message}}
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
                        <thead>
                            <tr>
                                <th class="text-center" nowrap>表示順</th>
                                <th class="text-center" style="min-width: 165px;" nowrap>項目名</th>
                                <th class="text-center" style="min-width: 165px;" nowrap>型</th>
                                <th class="text-center" nowrap>必須</th>
                                <th class="text-center" nowrap>詳細 <a href="https://connect-cms.jp/manual/user/form#frame-125" target="_brank"><span class="fas fa-question-circle" data-toggle="tooltip" title="オンラインマニュアルはこちら"></a></th>
                                <th class="text-center" nowrap>コピー</th>
                                <th class="text-center" nowrap>更新</th>
                                <th class="text-center" nowrap>削除</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- 更新用の行 --}}
                            @foreach($columns as $column)
                                @include('plugins.user.forms.default.forms_edit_row')
                            @endforeach
                            {{-- 新規登録用の行 --}}
                            <tr>
                                <th colspan="8">【項目の追加行】</th>
                            </tr>
                            @include('plugins.user.forms.default.forms_edit_row_add')
                        </tbody>
                    </table>
                </div>
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
