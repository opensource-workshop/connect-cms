{{--
 * 登録画面(input time)テンプレート。
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
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
     * 時間カレンダーボタン押下
     */
     $(function () {
        $('#{{ $database_obj->id }}').datetimepicker({
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
            databaseat: 'HH:mm',
            format: 'HH:mm',
            stepping: {{ $database_obj->minutes_increments }}
        });
    });
</script>
{{-- 時間 --}}
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
