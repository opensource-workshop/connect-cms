{{--
 * 登録画面(input file)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
 --}}
@php
    // value 値の取得
    $value_obj = (empty($input_cols)) ? null : $input_cols->where('databases_inputs_id', $id)->where('databases_columns_id', $database_obj->id)->first();
    $value = '';
    $client_original_name = '';
    if (!empty($value_obj)) {
        $value = $value_obj->value;
        $client_original_name = $value_obj->client_original_name;
    }
@endphp
@if ($value)
    <a href="{{url('/')}}/file/{{$value}}" target="_blank">{{$client_original_name}}</a><br />
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" name="delete_upload_column_ids[{{$database_obj->id}}]" value="{{$database_obj->id}}" class="custom-control-input" id="delete_upload_column_ids[{{$database_obj->id}}]">
            <label class="custom-control-label" for="delete_upload_column_ids[{{$database_obj->id}}]">ファイルを削除する。</label>
        </div>
    </div>
@endif
<input name="databases_columns_value[{{$database_obj->id}}]" class="" type="{{$database_obj->column_type}}">
@if ($errors && $errors->has("databases_columns_value.$database_obj->id"))
    <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first("databases_columns_value.$database_obj->id")}}</div>
@endif
