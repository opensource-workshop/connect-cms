{{--
 * 確認画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
<script type="text/javascript">
    {{-- 保存のsubmit JavaScript --}}
    function submit_databases_store() {
        @if (isset($id))
            databases_store{{$frame_id}}.action = "{{URL::to('/')}}/redirect/plugin/databases/publicStore/{{$page->id}}/{{$frame_id}}/{{$id}}#frame-{{$frame_id}}";
        @else
            databases_store{{$frame_id}}.action = "{{URL::to('/')}}/redirect/plugin/databases/publicStore/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        @endif
        databases_store{{$frame_id}}.submit();
    }
    {{-- 保存のキャンセル JavaScript --}}
    function submit_databases_cancel() {
        @if($id)
            databases_store{{$frame_id}}.action = "{{url('/')}}/plugin/databases/detail/{{$page->id}}/{{$frame_id}}/{{$id}}#frame-{{$frame_id}}";
        @else
            databases_store{{$frame_id}}.action = "{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        @endif
        databases_store{{$frame_id}}.submit();
    }
</script>

<form action="" name="databases_store{{$frame_id}}" method="POST">
    {{ csrf_field() }}
    @foreach($databases_columns as $database_column)
    <div class="form-group container-fluid row">
        {{-- ラベル --}}
        <label class="col-sm-2 control-label text-nowrap">{{$database_column->column_name}}</label>
        {{-- 項目 --}}
        <div class="col-sm-10">

        @switch($database_column->column_type)

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
        @case("date")
            {{$request->databases_columns_value[$database_column->id]}}
            <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="{{$request->databases_columns_value[$database_column->id]}}">
            @break
        @case(DatabaseColumnType::time)
            {{$request->databases_columns_value[$database_column->id]}}
            <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="{{$request->databases_columns_value[$database_column->id]}}">
            @break
        @case(DatabaseColumnType::file)
            @php
                // value 値の取得
                if ($uploads && $uploads->where('columns_id', $database_column->id)) {
                    $value_obj = $uploads->where('columns_id', $database_column->id)->first();
                }
            @endphp
            @if(isset($value_obj)) {{-- ファイルがアップロードされた or もともとアップロードされていて変更がない時 --}}
                <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="{{$value_obj->id}}">
                <a href="{{url('/')}}/file/{{$value_obj->id}}" target="_blank">{{$value_obj->client_original_name}}</a>
            @else
                <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="">
            @endif
            @break
        @case(DatabaseColumnType::image)
            @php
                // value 値の取得
                if ($uploads && $uploads->where('columns_id', $database_column->id)) {
                    $value_obj = $uploads->where('columns_id', $database_column->id)->first();
                }
            @endphp
            @if(isset($value_obj)) {{-- ファイルがアップロードされた or もともとアップロードされていて変更がない時 --}}
                <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="{{$value_obj->id}}">
                <img src="{{url('/')}}/file/{{$value_obj->id}}" />
            @else
                <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="">
            @endif
            @break
        @case(DatabaseColumnType::video)
            @php
                // value 値の取得
                if ($uploads && $uploads->where('columns_id', $database_column->id)) {
                    $value_obj = $uploads->where('columns_id', $database_column->id)->first();
                }
            @endphp
            @if(isset($value_obj)) {{-- ファイルがアップロードされた or もともとアップロードされていて変更がない時 --}}
                <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="{{$value_obj->id}}">
                <a href="{{url('/')}}/file/{{$value_obj->id}}" target="_blank">{{$value_obj->client_original_name}}</a>
            @else
                <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="">
            @endif
            @break
        @case(DatabaseColumnType::wysiwyg)
            {!!$request->databases_columns_value[$database_column->id]!!}
            <input name="databases_columns_value[{{$database_column->id}}]" class="form-control" type="hidden" value="{{$request->databases_columns_value[$database_column->id]}}">
            @break
        @endswitch
        </div>
    </div>
    @endforeach

    {{-- 削除対象のファイルのid --}}
    @foreach($delete_upload_column_ids as $delete_upload_column_id)
        <input name="delete_upload_column_ids[{{$delete_upload_column_id}}]" type="hidden" value="{{$delete_upload_column_id}}">
    @endforeach

    {{-- ボタンエリア --}}
    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="javascript:submit_databases_cancel();"><i class="fas fa-times"></i> キャンセル</button>
        @if($id)
            <button type="submit" class="btn btn-primary" onclick="javascript:submit_databases_store();"><i class="fas fa-check"></i> 変更確定</button>
        @else
            <button type="submit" class="btn btn-primary" onclick="javascript:submit_databases_store();"><i class="fas fa-check"></i> 登録確定</button>
        @endif
    </div>
</form>
@endsection
