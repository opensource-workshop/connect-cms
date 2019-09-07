{{--
 * 登録画面(input textarea)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
<textarea name="forms_columns_value[{{$form_obj->id}}]" class="form-control">{{old('forms_columns_value.'.$form_obj->id, $request->forms_columns_value[$form_obj->id])}}</textarea>
@if ($errors && $errors->has("forms_columns_value.$form_obj->id"))
    <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first("forms_columns_value.$form_obj->id")}}</div>
@endif
