{{--
 * 確認画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
<form action="{{URL::to($page->permanent_link)}}?action=store&frame_id={{$frame_id}}" name="form_add_column{{$frame_id}}" method="POST">
    {{ csrf_field() }}
    @foreach($forms_columns as $form_column)
    <div class="form-group container-fluid row">
        <label class="col-sm-2 control-label">{{$form_column->column_name}}</label>
        <div class="col-sm-10">

        @switch($form_column->column_type)

            @case("group")

<div class="form-inline">
        @foreach($form_column->group as $group_row)
<label class="control-label" style="vertical-align: top;">[{{$group_row->column_name}}]</label>
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
            <input name="forms_columns_value[{{$form_column->id}}]" type="hidden"> あああ
            @break
        @case("checkbox")
            <input name="forms_columns_value[{{$form_column->id}}]" type="hidden"> いいい
            @break
        @endswitch
        </div>
    </div>
    @endforeach
    <div class="form-group">
        <div class="col-sm-2 col-sm-push-2">
            <button class="btn btn-default">送信</button>
        </div>
    </div>
</form>
