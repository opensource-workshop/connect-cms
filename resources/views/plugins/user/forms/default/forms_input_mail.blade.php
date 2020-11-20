{{--
 * 登録画面(input mail)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}

<input 
    name="forms_columns_value[{{$form_obj->id}}]" 
    class="form-control" 
    type="email" 
    value="@if ($frame_id == $request->frame_id){{old('forms_columns_value.'.$form_obj->id, $request->forms_columns_value[$form_obj->id])}}@endif"
    placeholder="{{ $form_obj->place_holder }}"
    id="{{$label_id}}"
>
{{-- 確認用の項目 --}}
<input 
    name="forms_columns_value_confirmation[{{$form_obj->id}}]" 
    class="form-control" 
    type="email" 
    value="@if ($frame_id == $request->frame_id){{old('forms_columns_value_confirmation.'.$form_obj->id, $request->forms_columns_value_confirmation[$form_obj->id])}}@endif"
    placeholder="{{ __('messages.enter_same_email') }}"
    title="{{ __('messages.enter_same_email') }}"
>
@if ($errors && $errors->has("forms_columns_value.$form_obj->id"))
    <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first("forms_columns_value.$form_obj->id")}}</div>
@endif
