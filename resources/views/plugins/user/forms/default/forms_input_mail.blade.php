{{--
 * 登録画面(input mail)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
@php
    $value = $request->forms_columns_value[$form_obj->id] ?? null;
    $value_confirmation = $request->forms_columns_value_confirmation[$form_obj->id] ?? null;
@endphp
<input
    name="forms_columns_value[{{$form_obj->id}}]"
    class="form-control"
    type="text"
    value="@if ($frame_id == $request->frame_id){{old('forms_columns_value.'.$form_obj->id, $value)}}@endif"
    placeholder="{{ $form_obj->place_holder }}"
    id="{{$label_id}}"
>
{{-- 確認用の項目 --}}
<input
    name="forms_columns_value_confirmation[{{$form_obj->id}}]"
    class="form-control"
    type="text"
    value="@if ($frame_id == $request->frame_id){{old('forms_columns_value_confirmation.'.$form_obj->id, $value_confirmation)}}@endif"
    placeholder="{{ __('messages.enter_same_email') }}"
    title="{{ __('messages.enter_same_email') }}"
>
@include('plugins.common.errors_inline', ['name' => "forms_columns_value.$form_obj->id"])
