{{--
 * 登録画面(input time)テンプレート。
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
<script>
    /**
     * 時間カレンダーボタン押下
     */
    $(function () {
        $('#{{ $form_obj->id }}').datetimepicker({
            tooltips: {
                close: '閉じる',
                pickHour: '時間を取得',
                incrementHour: '時間を増加',
                decrementHour: '時間を減少',
                pickMinute: '分を取得',
                incrementMinute: '分を増加',
                decrementMinute: '分を減少',
                pickSecond: '秒を取得',
                incrementSecond: '秒を増加',
                decrementSecond: '秒を減少',
                togglePeriod: '午前/午後切替',
                selectTime: '時間を選択'
            },
            format: 'HH:mm',
            stepping: {{ $form_obj->minutes_increments }}
        });
    });
</script>
    {{-- 時間 --}}
    <div class="input-group date" id="{{ $form_obj->id }}" data-target-input="nearest">
        <input 
            type="text" 
            name="forms_columns_value[{{ $form_obj->id }}]" 
            value="@if ($frame_id == $request->frame_id){{old('forms_columns_value.'.$form_obj->id, $request->forms_columns_value[$form_obj->id])}}@endif"
            class="form-control datetimepicker-input" 
            data-target="#{{ $form_obj->id }}"
        >
        <div class="input-group-append" data-target="#{{ $form_obj->id }}" data-toggle="datetimepicker">
            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
        </div>
    </div>
@if ($errors && $errors->has("forms_columns_value.$form_obj->id"))
    <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first("forms_columns_value.$form_obj->id")}}</div>
@endif
