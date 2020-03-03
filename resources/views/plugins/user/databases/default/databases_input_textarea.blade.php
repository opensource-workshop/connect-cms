{{--
 * 登録画面(input textarea)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
 --}}
<textarea name="databases_columns_value[{{$database_obj->id}}]" class="form-control">{{old('databases_columns_value.'.$database_obj->id, $request->databases_columns_value[$database_obj->id])}}</textarea>
@if ($errors && $errors->has("databases_columns_value.$database_obj->id"))
    <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first("databases_columns_value.$database_obj->id")}}</div>
@endif
