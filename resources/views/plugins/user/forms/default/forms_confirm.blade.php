{{--
 * 確認画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
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
        {{-- ラベル --}}
        <label class="col-sm-2 control-label text-nowrap">{{$form_column->column_name}}</label>
        {{-- 項目 --}}
        <div class="col-sm-10">

        @switch($form_column->column_type)

        @case(FormColumnType::group)
            <div class="form-inline">
                @foreach($form_column->group as $group_row)
                    <label class="control-label" style="vertical-align: top; margin-right: 10px;@if (!$loop->first) margin-left: 30px;@endif">{{$group_row->column_name}}</label>
                    {{$request->forms_columns_value[$group_row->id]}}
                    <input name="forms_columns_value[{{$group_row->id}}]" class="form-control" type="hidden" value="{{$request->forms_columns_value[$group_row->id]}}" />
                @endforeach
            </div>
            @break
        @case(FormColumnType::text)
            {{$request->forms_columns_value[$form_column->id]}}
            <input name="forms_columns_value[{{$form_column->id}}]" class="form-control" type="hidden" value="{{$request->forms_columns_value[$form_column->id]}}">
            @break
        @case(FormColumnType::textarea)
            {!!nl2br(e($request->forms_columns_value[$form_column->id]))!!}
            <input name="forms_columns_value[{{$form_column->id}}]" class="form-control" type="hidden" value="{{$request->forms_columns_value[$form_column->id]}}">
            @break
        @case(FormColumnType::radio)
            @if (array_key_exists($form_column->id, $request->forms_columns_value))
                <input name="forms_columns_value[{{$form_column->id}}]" type="hidden" value="{{$request->forms_columns_value[$form_column->id]}}">{{$request->forms_columns_value[$form_column->id]}}
            @else
                <input name="forms_columns_value[{{$form_column->id}}]" type="hidden">
            @endif
            @break
        @case(FormColumnType::checkbox)
            @if (array_key_exists($form_column->id, $request->forms_columns_value))
                @foreach($request->forms_columns_value[$form_column->id] as $checkbox_item)
                    <input name="forms_columns_value[{{$form_column->id}}][]" type="hidden" value="{{$checkbox_item}}">{{$checkbox_item}}@if (!$loop->last), @endif
                @endforeach
            @else
                <input name="forms_columns_value[{{$form_column->id}}][]" type="hidden">
            @endif
            @break
        @case(FormColumnType::select)
            @if (array_key_exists($form_column->id, $request->forms_columns_value))
                <input name="forms_columns_value[{{$form_column->id}}]" type="hidden" value="{{$request->forms_columns_value[$form_column->id]}}">{{$request->forms_columns_value[$form_column->id]}}
            @else
                <input name="forms_columns_value[{{$form_column->id}}]" type="hidden">
            @endif
            @break
        @case(FormColumnType::mail)
            {{$request->forms_columns_value[$form_column->id]}}
            <input name="forms_columns_value[{{$form_column->id}}]" class="form-control" type="hidden" value="{{$request->forms_columns_value[$form_column->id]}}">
            @break
        @case(FormColumnType::date)
            {{$request->forms_columns_value[$form_column->id]}}
            <input name="forms_columns_value[{{$form_column->id}}]" class="form-control" type="hidden" value="{{$request->forms_columns_value[$form_column->id]}}">
            @break
        @case(FormColumnType::time)
            {{$request->forms_columns_value[$form_column->id]}}
            <input name="forms_columns_value[{{$form_column->id}}]" class="form-control" type="hidden" value="{{$request->forms_columns_value[$form_column->id]}}">
            @break
        @case(FormColumnType::time_from_to)
            {{$request->forms_columns_value[$form_column->id]}}
            <input name="forms_columns_value[{{$form_column->id}}]" class="form-control" type="hidden" value="{{$request->forms_columns_value[$form_column->id]}}">
            @break
        @endswitch
        </div>
    </div>
    @endforeach
    {{-- ボタンエリア --}}
    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="submit_forms_cancel();"><i class="fas fa-times"></i> {{__('messages.cancel')}}</button>
        @if ($form->use_temporary_regist_mail_flag)
            <button type="submit" class="btn btn-info" onclick="submit_forms_store();"><i class="fas fa-check"></i> {{__('messages.temporary_regist')}}</button>
        @else
            <button type="submit" class="btn btn-primary" onclick="submit_forms_store();"><i class="fas fa-check"></i> {{__('messages.main_regist')}}</button>
        @endif
    </div>
</form>
@endsection
