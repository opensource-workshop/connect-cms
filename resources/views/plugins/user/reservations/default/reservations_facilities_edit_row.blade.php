{{--
 * 施設の更新行
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}
<tr>
    {{-- 表示順操作 --}}
    <td style="vertical-align: middle;" nowrap>
        {{-- 上移動 --}}
        <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->first) disabled @endif onclick="javascript:submit_display_sequence({{ $facility->id }}, {{ $facility->display_sequence }}, 'up')">
            <i class="fas fa-arrow-up"></i>
        </button>

        {{-- 下移動 --}}
        <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->last) disabled @endif onclick="javascript:submit_display_sequence({{ $facility->id }}, {{ $facility->display_sequence }}, 'down')">
            <i class="fas fa-arrow-down"></i>
        </button>
    </td>
    {{-- 施設名 --}}
    <td>
        <input class="form-control" type="text" name="facility_name_{{ $facility->id }}" value="{{$facility->facility_name}}">
    </td>
    {{-- 非表示フラグ --}}
    <td class="table-text p-1 w-auto" nowrap>
        <div class="custom-control custom-checkbox ml-3 mt-1">
            <input name="hide_flag_{{ $facility->id }}" id="hide_flag_{{ $facility->id }}" value="1" type="checkbox" class="custom-control-input" @if (isset($facility->hide_flag)) checked="checked" @endif>
            <label class="custom-control-label" for="hide_flag_{{ $facility->id }}"></label>
        </div>
    </td>
    {{-- 更新ボタン --}}
    <td style="vertical-align: middle;">
        <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_update_facility({{ $facility->id }});"><i class="fas fa-save"></i> <span class="d-sm-none">更新</span></button>
    </td>
</tr>
