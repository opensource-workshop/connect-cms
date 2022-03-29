{{--
 * 項目の追加行
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
 --}}
<tr>
    {{-- 余白 --}}
    <td></td>
    {{-- 項目名 --}}
    <td>
        <input class="form-control @if ($errors && $errors->has('column_name')) border-danger @endif" type="text" name="column_name" value="{{ old('column_name') }}" placeholder="項目名">
    </td>
    {{-- 入力データ型 --}}
    <td>
        <select class="form-control" name="column_type" style="min-width: 100px;">
            <option value="" disabled>型を指定</option>
            @foreach (UserColumnType::getMembers() as $key=>$value)
                <option value="{{$key}}" @if ($key == old('column_type')) selected="selected" @endif>{{ $value }}</option>
            @endforeach
        </select>
    </td>
    {{-- 必須 --}}
    <td class="align-middle text-center">
        <input type="checkbox" name="required" value="1" @if (old('required') == Required::on) checked="checked" @endif data-toggle="tooltip" title="必須項目として指定します。">
    </td>
    {{-- ＋ボタン --}}
    <td class="text-center">
        <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_add_column(this);">
            <i class="fas fa-plus"></i> <span class="d-sm-none">追加</span>
        </button>
    </td>
    {{-- 表示上の区切り線が切れてしまう為、空のtdタグを設置 --}}
    <td></td>
    <td></td>
</tr>
