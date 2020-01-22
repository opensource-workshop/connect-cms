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
            {{-- 項目追加のsubmit JavaScript --}}
            function submit_setting_column() {
                form_columns.action = "/plugin/forms/addColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                form_columns.submit();
            }

            {{-- 項目削除のsubmit JavaScript --}}
            function submit_destroy_column(row_no) {
                form_columns.action = "/plugin/forms/deleteColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                form_columns.destroy_no.value = row_no;
                form_columns.submit();
            }

            {{-- ページの上移動用フォームのsubmit JavaScript --}}
            function submit_sequence_up( id ) {
                form_columns.action = "/plugin/forms/upColumnSequence/{{$page->id}}/{{$frame_id}}/" + id + "#frame-{{$frame_id}}";
                form_columns.submit();
            }

            {{-- ページの下移動用フォームのsubmit JavaScript --}}
            function submit_sequence_down( id ) {
                form_columns.action = "/plugin/forms/downColumnSequence/{{$page->id}}/{{$frame_id}}/" + id + "#frame-{{$frame_id}}";
                form_columns.submit();
            }

            {{-- 項目の再設定フォームのsubmit JavaScript --}}
            function submit_reload_column(row_no) {

                {{-- POPUP画面の選択肢をメインのフォームに取り込んでsubmitする --}}
                $('#column_detail_tbody' + row_no + ' input[name^=forms]').each(function(i, elem) {
                    //console.log(elem);
                    var clone_elem = $(elem).clone();
                    clone_elem.css('display', 'none');
                    $('#form_columns').append(clone_elem);
                });
                //console.log($('#form_columns'));

                form_columns.action = "/plugin/forms/reloadColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                form_columns.submit();
            }

            {{-- 選択肢の追加 --}}
            function add_select_row(row_no) {

                {{-- 選択肢の行番号用変数のカウントアップ --}}
                var select_count = $("#select_count"+row_no).val();
                var new_count = parseInt(select_count,10)+1;
                $("#select_count"+row_no).val(new_count);

                {{-- 選択肢の行番号取得（tr の数） --}}
                // var size = $('#column_detail_tbody' + row_no + ' tr').length;

                {{-- 選択肢の行をクローンして、クラス名を付け直す --}}
                var clone_tr = $(".column_detail_row_hidden"+row_no).clone();
                clone_tr.removeClass("column_detail_row_hidden"+row_no).addClass("column_detail_row_"+row_no+"_"+new_count);
                clone_tr.find('.select_value').attr('name', 'forms[{{$frame_id}}][' + row_no + '][select][' + new_count + '][value]');
                clone_tr.css('display', 'table-row');
                clone_tr.appendTo($("#column_detail_tbody"+row_no));
            }

            {{-- 選択肢の削除 --}}
            function remove_select_row(row_no,select_no) {
                $(".column_detail_row_" + row_no + '_' + select_no).remove();
            }
        </script>

        {{-- キャンセル用のフォーム。キャンセル時はセッションをクリアするため、トークン付きでPOST でsubmit したい。 --}}
        <form action="/redirect/plugin/forms/cancel/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="forms_cancel" method="POST" class="visible-lg-inline visible-md-inline visible-sm-inline visible-xs-inline">
            {{ csrf_field() }}
        </form>

        <!-- Add or Update Form Button -->
        <div class="form-group">
            <form action="/plugin/forms/saveColumn/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" id="form_columns" name="form_columns" method="POST">
                {{ csrf_field() }}
                <input type="hidden" name="forms_id" value="{{$forms_id}}">
                <input type="hidden" name="destroy_no" value="">
                <input type="hidden" name="return_frame_action" value="edit">

                <div class="table-responsive">

                    {{-- 項目の一覧 --}}
                    <table class="table table-hover">
                    <thead>
                        <tr>
                            <th nowrap>操作</th>
                            <th nowrap>項目名</th>
                            <th nowrap>型</th>
                            <th nowrap>必須</th>
                            <th nowrap>まとめ数</th>
                            <th nowrap>削除</th>
                            <th nowrap></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- 更新用の行 --}}
                        @foreach($rows as $row)
                            @include('plugins.user.forms.default.forms_edit_row',['row_no' => $loop->iteration, 'delete_flag' => $row['delete_flag']])
                        @endforeach
                        {{-- 新規登録用の行 --}}
                        <tr>
                            <th colspan="4">【項目の追加行】</th>
                        </tr>
                        @include('plugins.user.forms.default.forms_edit_row_add',['row_no' => 0, 'delete_flag' => 0])
                    </tbody>
                    </table>
                </div>
                {{-- ボタンエリア --}}
                <div class="text-center mt-3 mt-md-0">
                    {{-- キャンセルボタン --}}
                    <button type="button" class="btn btn-secondary mr-2" onclick="javascript:forms_cancel.submit();"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span></button>
                    {{-- フォーム保存ボタン --}}
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> フォーム保存
                    </button>
                </div>
            </form>
        </div>

            {{-- POPUP用、各行の詳細画面 --}}
            @foreach($rows as $row)
                @include('plugins.user.forms.default.forms_edit_row_detail',['row_no' => $loop->iteration])
            @endforeach

        @endif
    @endauth
@endsection
