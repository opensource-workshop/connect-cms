{{--
 * 確認画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
<script type="text/javascript">
    {{-- 保存のsubmit JavaScript --}}
    function submit_forms_store() {
        forms_store{{$frame_id}}.action = "{{URL::to('/')}}/plugin/forms/publicStore/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        forms_store{{$frame_id}}.submit();
    }
    {{-- 保存のキャンセル JavaScript --}}
    function submit_forms_cancel() {
        forms_store{{$frame_id}}.action = "{{URL::to('/')}}/plugin/forms/index/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        forms_store{{$frame_id}}.submit();
    }
</script>

<form action="" name="forms_store{{$frame_id}}" method="POST">
    {{ csrf_field() }}
    @foreach($forms_columns as $form_column)
    <div class="form-group container-fluid row">
        <label class="col-sm-4 control-label">{{$form_column->column_name}}</label>
        <div class="col-sm-8">

        @switch($form_column->column_type)

            @case("group")
                <div class="form-inline">
                    @foreach($form_column->group as $group_row)
                        <label class="control-label" style="vertical-align: top; margin-right: 10px;@if (!$loop->first) margin-left: 30px;@endif">{{$group_row->column_name}}</label>
                        {{$request->forms_columns_value[$group_row->id]}}
                        <input name="forms_columns_value[{{$group_row->id}}]" class="form-control" type="hidden" value="{{$request->forms_columns_value[$group_row->id]}}" />
                    @endforeach
                </div>
            @break
        @case("text")
            {{$request->forms_columns_value[$form_column->id]}}
            <input name="forms_columns_value[{{$form_column->id}}]" class="form-control" type="hidden" value="{{$request->forms_columns_value[$form_column->id]}}">
            @break
        @case("textarea")
            {!!nl2br(e($request->forms_columns_value[$form_column->id]))!!}
            <input name="forms_columns_value[{{$form_column->id}}]" class="form-control" type="hidden" value="{{$request->forms_columns_value[$form_column->id]}}">
            @break
        @case("radio")
            @if (array_key_exists($form_column->id, $request->forms_columns_value))
                <input name="forms_columns_value[{{$form_column->id}}]" type="hidden" value="{{$request->forms_columns_value[$form_column->id]}}">{{$request->forms_columns_value[$form_column->id]}}
            @else
                <input name="forms_columns_value[{{$form_column->id}}]" type="hidden">
            @endif
            @break
        @case("checkbox")
            @if (array_key_exists($form_column->id, $request->forms_columns_value))
                @foreach($request->forms_columns_value[$form_column->id] as $checkbox_item)
                    <input name="forms_columns_value[{{$form_column->id}}][]" type="hidden" value="{{$checkbox_item}}">{{$checkbox_item}}@if (!$loop->last), @endif
                @endforeach
            @else
                <input name="forms_columns_value[{{$form_column->id}}][]" type="hidden">
            @endif
            @break
        @case("select")
            @if (array_key_exists($form_column->id, $request->forms_columns_value))
                <input name="forms_columns_value[{{$form_column->id}}]" type="hidden" value="{{$request->forms_columns_value[$form_column->id]}}">{{$request->forms_columns_value[$form_column->id]}}
            @else
                <input name="forms_columns_value[{{$form_column->id}}]" type="hidden">
            @endif
            @break
        @case("mail")
            {{$request->forms_columns_value[$form_column->id]}}
            <input name="forms_columns_value[{{$form_column->id}}]" class="form-control" type="hidden" value="{{$request->forms_columns_value[$form_column->id]}}">
            @break
        @endswitch
        </div>
    </div>
    @endforeach
    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="javascript:submit_forms_cancel();"><i class="fas fa-times"></i> キャンセル</button>
        <button type="submit" class="btn btn-primary" onclick="javascript:submit_forms_store();"><i class="fas fa-check"></i> 送信</button>
    </div>
</form>
