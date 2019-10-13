{{--
 * 設定画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
<div class="modal fade" id="formsDetailModal{{$row_no}}" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">項目詳細設定（{{$row['column_name']}}）</h5>
                <button type="button" class="close" data-dismiss="modal"><span>×</span></button>
            </div>

            <form onsubmit="return false;" action="" id="form_column_detail{{$row_no}}" name="form_column_detail{{$row_no}}" method="POST">
                {{-- 選択肢の入力項目の行番号用変数として持っているinput --}}
                @if (isset($row['select']))
                    <input type="hidden" name="select_count{{$row_no}}" id="select_count{{$row_no}}" value="{{count($row['select'])}}" />
                @else
                    {{-- 選択肢が空の場合でも、最初の1行は入力用に表示するため、初期値は1 --}}
                    <input type="hidden" name="select_count{{$row_no}}" id="select_count{{$row_no}}" value="1" />
                @endif
                <div class="modal-body">

                    <table class="table table-hover mb-0">
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
                                    <i class="fas fa-arrow-up"></i>
                                </button>
                               <button type="button" class="btn btn-default btn-xs" disabled>
                                   <i class="fas fa-arrow-down"></i>
                               </button>
                           </td>
                           <td>
                                <input type="text" name="select_value" value="" class="form-control select_value">
                           </td>
                           <td nowrap>
                               <button class="btn btn-danger form-horizontal" onclick="">
                                   <i class="fas fa-trash-alt"></i> <span class="d-sm-none">削除</span>
                               </button>
                           </td>
                       </tr>

                       {{-- 選択肢がある場合 --}}
                       @if (isset($row['select']))
                       @foreach ($row['select'] as $select_no => $row_select)
                        <tr class="column_detail_row_{{$row_no}}_{{$select_no}}">
                            <td style="vertical-align: middle;" nowrap>
                                <button type="button" class="btn btn-default btn-xs" disabled>
                                    <i class="fas fa-arrow-up"></i>
                                </button>
                               <button type="button" class="btn btn-default btn-xs" disabled>
                                   <i class="fas fa-arrow-down"></i>
                               </button>
                           </td>
                           <td>
                                <input type="text" name="forms[{{$frame_id}}][{{$row_no}}][select][{{$select_no}}][value]" value="{{$row['select'][$select_no]['value']}}" class="form-control select_value">
                           </td>
                           <td nowrap>
                               <button class="btn btn-danger form-horizontal" onclick="javascript:remove_select_row('{{$row_no}}','{{$select_no}}');return false;">
                                   <i class="fas fa-trash-alt"></i> <span class="d-sm-none">削除</span>
                               </button>
                           </td>
                        </tr>
                       {{-- 選択肢が空の場合。入力用の最初の1行。行番号は1 --}}
                       @endforeach
                       @else
                        <tr class="column_detail_row_{{$row_no}}_1">
                            <td style="vertical-align: middle;" nowrap>
                                <button type="button" class="btn btn-default btn-xs" disabled>
                                    <i class="fas fa-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-default btn-xs" disabled>
                                    <i class="fas fa-arrow-down"></i>
                                </button>
                            </td>
                            <td>
                                 <input type="text" name="forms[{{$frame_id}}][{{$row_no}}][select][1][value]" value="" class="form-control select_value">
                            </td>
                            <td nowrap>
                                <button class="btn btn-danger" onclick="javascript:remove_select_row('{{$row_no}}','1');return false;">
                                    <i class="fas fa-trash-alt"></i> <span class="d-sm-none">削除</span>
                                </button>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot>
                       <tr class="column_detail_row_add">
                           <td style="vertical-align: middle;" nowrap></td>
                           <td class="text-center">
                               <button class="btn btn-primary" onclick="javascript:add_select_row('{{$row_no}}');return false;">
                                   <i class="fas fa-plus"></i> <span class="d-sm-none">選択肢追加</span>
                               </button>
                           </td>
                           <td>
                           </td>
                       </tr>
                    </tfoot>
                    </table>

                    <div class="text-center mt-1">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> キャンセル
                        </button>
                        <button type="button" class="btn btn-primary mr-3" onclick="javascript:submit_reload_column('{{$row_no}}');">
                            <i class="far fa-edit"></i> 変更
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
