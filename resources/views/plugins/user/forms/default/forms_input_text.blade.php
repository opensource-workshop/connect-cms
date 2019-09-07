{{--
 * 登録画面(input text)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
<input name="forms_columns_value[{{$form_obj->id}}]" class="form-control" type="{{$form_obj->column_type}}" value="@if ($frame_id == $request->frame_id){{old('forms_columns_value.'.$form_obj->id, $request->forms_columns_value[$form_obj->id])}}@endif">
@if ($errors && $errors->has("forms_columns_value.$form_obj->id"))
    <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first("forms_columns_value.$form_obj->id")}}</div>
@endif
