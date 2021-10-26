{{--
 * データ表示用テンプレート。
--}}
@php
    $obj = $inputs_columns->where('column_id', $column->id)->first();

    // その他の型
    $value = $obj ? $obj->value : "";

    // 空の場合、なにか出力しないと「項目名<br>値」で出力してるテンプレートは高さがずれてしまうため対応
    if (is_null($value) || $value === '') {
        // change to laravel6.
        // $value = "&nbsp;";
        $value = "\n";
    }
@endphp

{{--
@if ($column->column_type == ReservationColumnType::wysiwyg)
    {!!$value!!}
@else
--}}

{{-- 改行だけして他はエスケープ --}}
{!!nl2br(e($value))!!}

{{--
@endif
--}}
