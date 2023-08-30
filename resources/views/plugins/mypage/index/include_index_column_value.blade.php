{{--
 * マイページトップのデータ表示テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category マイページ
 --}}
@php
    $input_col = $input_cols->firstWhere('users_columns_id', $column->id);
    $value = $input_col->value ?? ''
@endphp
<tr class="input-cols">
    <th>{{$column->column_name}}</th>
    <td>{{$value}}</td>
</tr>
