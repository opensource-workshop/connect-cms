{{--
 * 項目の追加行テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
    <tr id="column_add_tr">
    {{-- 余白 --}}
    <td>
    </td>
    {{-- 項目名 --}}
    <td>
        <input class="form-control @if ($errors && $errors->has('column_name')) border-danger @endif" type="text" name="column_name" value="{{ old('column_name') }}">
    </td>
    {{-- 型 --}}
    <td>
        <select class="form-control" name="column_type">
            <option value="" disabled>型を指定</option>
            @foreach (FormColumnType::getMembers() as $key=>$value)
                <option value="{{$key}}"
                    {{-- validation用 --}}
                    @if($key == old('column_type'))
                        selected="selected"
                    @endif
                >{{ $value }}</option>
            @endforeach
        </select>
    </td>
    {{-- 必須 --}}
    <td class="align-middle text-center">
        <input type="checkbox" name="required" value="1" data-toggle="tooltip" title="必須項目として指定します。" @if (old("required") == Required::on) checked="checked" @endif>
    </td>
    {{-- 余白 --}}
    <td>
    </td>
    {{-- 余白 --}}
    <td>
    </td>
    {{-- ＋ボタン --}}
    <td class="text-center">
        <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_add_column();"><i class="fas fa-plus"></i> <span class="d-sm-none">追加</span></button>
    </td>
    {{-- 余白 --}}
    <td>
    </td>
</tr>