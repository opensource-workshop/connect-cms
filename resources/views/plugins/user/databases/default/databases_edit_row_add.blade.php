{{--
 * 項目の追加行テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
<tr id="column_add_tr">
    {{-- 余白 --}}
    <td class="p-1">
    </td>

    {{-- 項目名 --}}
    <td class="p-1">
        <input class="form-control @if ($errors && $errors->has('column_name')) border-danger @endif" type="text" name="column_name" value="{{ old('column_name') }}">
    </td>

    {{-- 型 --}}
    <td class="p-1">
        <select class="form-control" name="column_type">
            <option value="" disabled>型を指定</option>
            @foreach (DatabaseColumnType::getMembers() as $key=>$value)
                <option value="{{$key}}" @if($key == old('column_type')) selected="selected" @endif>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </td>

    {{-- 必須 --}}
    <td class="align-middle text-center p-1">
        <input type="checkbox" name="required" value="1" data-toggle="tooltip" title="必須項目として指定します。" @if (old("required") == Required::on) checked="checked" @endif>
    </td>

    {{-- 余白 --}}
    <td colspan="3" class="p-1">
    </td>

    {{-- ＋ボタン --}}
    <td class="text-center p-1">
        <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_add_column();">
            <i class="fas fa-plus"></i>
        </button>
    </td>

    {{-- 余白 --}}
    <td class="p-1">
    </td>
</tr>
