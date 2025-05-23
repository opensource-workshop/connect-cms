{{--
 * 登録画面(input textarea)テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
@php
    $value = $request->forms_columns_value[$form_obj->id] ?? null;
@endphp
<textarea rows="4" name="forms_columns_value[{{$form_obj->id}}]" class="form-control" placeholder="{{ $form_obj->place_holder }}" id="{{$label_id}}">@if ($frame_id == $request->frame_id){{old('forms_columns_value.'.$form_obj->id, $value)}}@endif</textarea>
@include('plugins.common.errors_inline', ['name' => "forms_columns_value.$form_obj->id"])
