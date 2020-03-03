{{--
 * 確認画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
<script type="text/javascript">
    {{-- 保存のsubmit JavaScript --}}
    function submit_databases_store() {
        databases_store{{$frame_id}}.action = "{{URL::to('/')}}/plugin/databases/publicStore/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        databases_store{{$frame_id}}.submit();
    }
    {{-- 保存のキャンセル JavaScript --}}
    function submit_databases_cancel() {
        databases_store{{$frame_id}}.action = "{{URL::to('/')}}/plugin/databases/index/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        databases_store{{$frame_id}}.submit();
    }
</script>

<form action="" name="databases_store{{$frame_id}}" method="POST">
    {{ csrf_field() }}
    @foreach($databases_columns as $database_column)
    <div class="form-group container-fluid row">
        {{-- ラベル --}}
        <label class="col-sm-6 control-label text-nowrap">{{$database_column->column_name}}</label>
        {{-- 項目 --}}
        <div class="col-sm-6">

        @switch($database_column->column_type)

        @case(DatabaseColumnType::group)
            <div class="form-inline">
                @foreach($database_column->group as $group_row)
                    <label class="control-label" style="vertical-align: top; margin-right: 10px;@if (!$loop->first) margin-left: 30px;@endif">{{$group_row->column_name}}</label>
                    {{$request->databases_columns_value[$group_row->id]}}
                    <input name="databases_columns_value[{{$group_row->id}}]" class="form-control" type="hidden" value="{{$request->databases_columns_value[$group_row->id]}}" />
                @endforeach
            </div>
            @break
        @case(DatabaseColumnType::text)
            {{$request->databases_columns_value[$database_column->id]}}
            <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="{{$request->databases_columns_value[$database_column->id]}}">
            @break
        @case(DatabaseColumnType::textarea)
            {!!nl2br(e($request->databases_columns_value[$database_column->id]))!!}
            <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="{{$request->databases_columns_value[$database_column->id]}}">
            @break
        @case(DatabaseColumnType::radio)
            @if (array_key_exists($database_column->id, $request->databases_columns_value))
                <input name="databases_columns_value[{{$database_column->id}}]" type="hidden" value="{{$request->databases_columns_value[$database_column->id]}}">{{$request->databases_columns_value[$database_column->id]}}
            @else
                <input name="databases_columns_value[{{$database_column->id}}]" type="hidden">
            @endif
            @break
        @case(DatabaseColumnType::checkbox)
            @if (array_key_exists($database_column->id, $request->databases_columns_value))
                @foreach($request->databases_columns_value[$database_column->id] as $checkbox_item)
                    <input name="databases_columns_value[{{$database_column->id}}][]" type="hidden" value="{{$checkbox_item}}">{{$checkbox_item}}@if (!$loop->last), @endif
                @endforeach
            @else
                <input name="databases_columns_value[{{$database_column->id}}][]" type="hidden">
            @endif
            @break
        @case(DatabaseColumnType::select)
            @if (array_key_exists($database_column->id, $request->databases_columns_value))
                <input name="databases_columns_value[{{$database_column->id}}]" type="hidden" value="{{$request->databases_columns_value[$database_column->id]}}">{{$request->databases_columns_value[$database_column->id]}}
            @else
                <input name="databases_columns_value[{{$database_column->id}}]" type="hidden">
            @endif
            @break
        @case(DatabaseColumnType::mail)
            {{$request->databases_columns_value[$database_column->id]}}
            <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="{{$request->databases_columns_value[$database_column->id]}}">
            @break
        @case(DatabaseColumnType::date)
            {{$request->databases_columns_value[$database_column->id]}}
            <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="{{$request->databases_columns_value[$database_column->id]}}">
            @break
        @case(DatabaseColumnType::time)
            {{$request->databases_columns_value[$database_column->id]}}
            <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="{{$request->databases_columns_value[$database_column->id]}}">
            @break
        @endswitch
        </div>
    </div>
    @endforeach
    {{-- ボタンエリア --}}
    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="javascript:submit_databases_cancel();"><i class="fas fa-times"></i> キャンセル</button>
        <button type="submit" class="btn btn-primary" onclick="javascript:submit_databases_store();"><i class="fas fa-check"></i> 送信</button>
    </div>
</form>
@endsection
