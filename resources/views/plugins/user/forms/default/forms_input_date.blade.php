{{--
 * 登録画面(input date)テンプレート。
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
<script>
    /**
     * カレンダーボタン押下
     */
    $(function () {
        $('#{{ $form_obj->id }}').datetimepicker({
            @if (App::getLocale() == ConnectLocale::ja)
                dayViewHeaderFormat: 'YYYY年 M月',
            @endif
            locale: '{{ App::getLocale() }}',
            format: 'YYYY/MM/DD',
            timepicker:false
        });
    });
</script>
    {{-- 日付 --}}
    <div class="input-group date" id="{{ $form_obj->id }}" data-target-input="nearest">
        <input
            type="text"
            name="forms_columns_value[{{ $form_obj->id }}]"
            value="@if ($frame_id == $request->frame_id){{old('forms_columns_value.'.$form_obj->id, $request->forms_columns_value[$form_obj->id])}}@endif"
            class="form-control datetimepicker-input"
            data-target="#{{ $form_obj->id }}"
            placeholder="{{ $form_obj->place_holder }}"
            id="{{$label_id}}"
        >
        <div class="input-group-append" data-target="#{{ $form_obj->id }}" data-toggle="datetimepicker">
            <div class="input-group-text"><i class="far fa-clock"></i></div>
        </div>
    </div>
@if ($errors && $errors->has("forms_columns_value.$form_obj->id"))
    <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first("forms_columns_value.$form_obj->id")}}</div>
@endif
