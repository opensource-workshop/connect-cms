{{--
 * 登録画面(input date)テンプレート。
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@php
    // value 値の取得
    $value_obj = (empty($input_cols)) ? null : $input_cols->where('databases_inputs_id', $id)->where('databases_columns_id', $database_obj->id)->first();
    $value = '';
    if (!empty($value_obj)) {
        $value = $value_obj->value;
    }
@endphp
<script>
    /**
     * カレンダーボタン押下
     */
    $(function () {
        $('#{{ $database_obj->id }}').datetimepicker({
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
<div class="input-group date" id="{{ $database_obj->id }}" data-target-input="nearest">
    <input
        type="text"
        name="databases_columns_value[{{ $database_obj->id }}]"
        value="{{old('databases_columns_value.'.$database_obj->id, $value)}}"
        class="form-control datetimepicker-input @if ($errors && $errors->has("databases_columns_value.$database_obj->id")) border-danger @endif"
        data-target="#{{ $database_obj->id }}"
    >
    <div class="input-group-append" data-target="#{{ $database_obj->id }}" data-toggle="datetimepicker">
        <div class="input-group-text @if ($errors && $errors->has("databases_columns_value.$database_obj->id")) border-danger @endif"><i class="far fa-clock"></i></div>
    </div>
</div>
@include('plugins.common.errors_inline', ['name' => "databases_columns_value.$database_obj->id"])
