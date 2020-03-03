{{--
 * 登録画面(input mail)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
 --}}
<input name="databases_columns_value[{{$database_obj->id}}]" class="form-control" type="{{$database_obj->column_type}}" value="@if ($frame_id == $request->frame_id){{old('databases_columns_value.'.$database_obj->id, $request->databases_columns_value[$database_obj->id])}}@endif">
@if ($errors && $errors->has("databases_columns_value.$database_obj->id"))
    <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first("databases_columns_value.$database_obj->id")}}</div>
@endif
