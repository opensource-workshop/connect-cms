{{--
 * 予約項目の追加行
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}
<tr>
    <td style="vertical-align: middle;" nowrap><br /></td>
    <td>
        {{-- 予約項目名 --}}
        <input class="form-control" type="text" name="column_name" value="{{ old('column_name') }}" placeholder="予約項目名">
    </td>
    <td>
        {{-- 型 --}}
        <select class="form-control" name="column_type" style="min-width: 100px;">
            <option value="" disabled>型を指定</option>
            @foreach (ReservationColumnType::getMembers() as $key=>$value)
                <option value="{{$key}}"
                    {{-- validation用 --}}
                    @if($key == old('column_type'))
                        selected="selected"
                    @endif
                >{{ $value }}</option>
            @endforeach
        </select>
    </td>
    <td style="vertical-align: middle;">
        {{-- 必須 --}}
        <input type="checkbox" name="required" value="1" data-toggle="tooltip" title="必須項目として指定します。">
    </td>
    <td class="text-center" style="vertical-align: middle;">
        {{-- ＋ボタン --}}
        <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_add_column(this);"><i class="fas fa-plus"></i> <span class="d-sm-none">追加</span></button>
    </td>
    {{-- 表示上の区切り線が切れてしまう為、空のtdタグを設置 --}}
    <td>
    </td>
    <td>
    </td>
    
</tr>
