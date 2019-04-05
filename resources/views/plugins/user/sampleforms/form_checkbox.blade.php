{{--
 * フォームのチェックボックス生成テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン・パーツ
 --}}
{{-- チェックボックスをon にする --}}
@php $column_checkbox_on = false; @endphp
@if(!empty($checkbox))
    @foreach(explode('|',$checkbox) as $checkbox_item)
        @if($checkbox_item == $check_value)
            @php $column_checkbox_on = true; @endphp
            @break
        @endif
    @endforeach
@endif
{{Form::checkbox($checkbox_name, $check_value, $column_checkbox_on)}}
