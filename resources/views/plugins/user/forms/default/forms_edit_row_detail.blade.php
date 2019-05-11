{{--
 * 設定画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
<div class="modal fade" id="formsDetailModal{{$row_no}}" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">

            <form onsubmit="return false;" action="" id="form_column_detail{{$row_no}}" name="form_column_detail{{$row_no}}" method="POST">
                {{-- 選択肢の入力項目の行番号用変数として持っているinput --}}
                @if (isset($row['select']))
                    <input type="hidden" name="select_count{{$row_no}}" id="select_count{{$row_no}}" value="{{count($row['select'])}}" />
                @else
                    {{-- 選択肢が空の場合でも、最初の1行は入力用に表示するため、初期値は1 --}}
                    <input type="hidden" name="select_count{{$row_no}}" id="select_count{{$row_no}}" value="1" />
                @endif
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
                    <h4 class="modal-title">項目詳細設定（{{$row['column_name']}}）</h4>
                </div>
                <div class="modal-body">
                    <div class="panel panel-default">
                        <div class="panel-body">

                            <table class="table table-hover" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>操作</th>
                                    <th>選択肢</th>
                                    <th>削除</th>
                                </tr>
                            </thead>
                            <tbody id="column_detail_tbody{{$row_no}}">

                                <tr class="column_detail_row_hidden{{$row_no}}" style="display:none;">
                                    <td style="vertical-align: middle;" nowrap>
                                        <button type="button" class="btn btn-default btn-xs" disabled>
                                            <span class="glyphicon glyphicon-arrow-up"></span>
                                        </button>
                                       <button type="button" class="btn btn-default btn-xs" disabled>
                                           <span class="glyphicon glyphicon-arrow-down"></span>
                                       </button>
                                   </td>
                                   <td>
                                        <input type="text" name="select_value" value="" class="form-control select_value">
                                   </td>
                                   <td>
                                       <button class="btn btn-danger form-horizontal" onclick="">
                                           <span class="glyphicon glyphicon-trash"></span> <span class="hidden-sm hidden-xs">削除</span>
                                       </button>
                                   </td>
                               </tr>

                               {{-- 選択肢がある場合 --}}
                               @if (isset($row['select']))
                               @foreach ($row['select'] as $select_no => $row_select)
                                <tr class="column_detail_row_{{$row_no}}_{{$select_no}}">
                                    <td style="vertical-align: middle;" nowrap>
                                        <button type="button" class="btn btn-default btn-xs" disabled>
                                            <span class="glyphicon glyphicon-arrow-up"></span>
                                        </button>
                                       <button type="button" class="btn btn-default btn-xs" disabled>
                                           <span class="glyphicon glyphicon-arrow-down"></span>
                                       </button>
                                   </td>
                                   <td>
                                        <input type="text" name="forms[{{$frame_id}}][{{$row_no}}][select][{{$select_no}}][value]" value="{{$row['select'][$select_no]['value']}}" class="form-control select_value">
                                   </td>
                                   <td>
                                       <button class="btn btn-danger form-horizontal" onclick="javascript:remove_select_row('{{$row_no}}','{{$select_no}}');return false;">
                                           <span class="glyphicon glyphicon-trash"></span> <span class="hidden-sm hidden-xs">削除</span>
                                       </button>
                                   </td>
                                </tr>
                               {{-- 選択肢が空の場合。入力用の最初の1行。行番号は1 --}}
                               @endforeach
                               @else
                                <tr class="column_detail_row_{{$row_no}}_1">
                                    <td style="vertical-align: middle;" nowrap>
                                        <button type="button" class="btn btn-default btn-xs" disabled>
                                            <span class="glyphicon glyphicon-arrow-up"></span>
                                        </button>
                                        <button type="button" class="btn btn-default btn-xs" disabled>
                                            <span class="glyphicon glyphicon-arrow-down"></span>
                                        </button>
                                    </td>
                                    <td>
                                         <input type="text" name="forms[{{$frame_id}}][{{$row_no}}][select][1][value]" value="" class="form-control select_value">
                                    </td>
                                    <td>
                                        <button class="btn btn-danger form-horizontal" onclick="javascript:remove_select_row('{{$row_no}}','1');return false;">
                                            <span class="glyphicon glyphicon-trash"></span> <span class="hidden-sm hidden-xs">削除</span>
                                        </button>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                            <tfoot>
                               <tr class="column_detail_row_add">
                                   <td style="vertical-align: middle;" nowrap></td>
                                   <td class="text-center">
                                       <button class="btn btn-primary form-horizontal" onclick="javascript:add_select_row('{{$row_no}}');return false;">
                                           <span class="glyphicon glyphicon-pencil"></span> <span class="hidden-sm hidden-xs">選択肢追加</span>
                                       </button>
                                   </td>
                                   <td>
                                   </td>
                               </tr>
                            </tfoot>
                            </table>

                            <div class="text-center" style="margin-top:10px;">
                                <button type="button" class="btn btn-primary form-horizontal" onclick="javascript:submit_reload_column('{{$row_no}}');">
                                    <span class="glyphicon glyphicon-edit"></span> 変更
                                </button>
                                <button type="button" class="btn btn-default" style="margin-left: 10px;" data-dismiss="modal">
                                    <span class="glyphicon glyphicon-remove"></span> キャンセル
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
