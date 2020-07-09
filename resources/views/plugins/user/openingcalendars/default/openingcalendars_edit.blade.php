{{--
 * 開館カレンダー編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 開館カレンダープラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
<style type="text/css">
<!--
    #frame-card-{{ $frame->frame_id }} { width: 60vw; z-index: 999; background-color: white !important;}
-->
</style>

{{-- 全選択 --}}
<script type="text/javascript">
	function set_all_time(arg) {
        for(var i = 1;  i <= {{count($edit_days)}};  i++) {
            element_name = "openingcalendars[" + i + "]";
            elements = document.getElementsByName(element_name);
            elements[arg].checked = true ;
        }
	}
</script>

【編集年月】：{{$edit_ym}}

<form action="{{url('/')}}/plugin/openingcalendars/edit/{{$page->id}}/{{$frame_id}}" method="POST" class="" name="chenge_ym">
    {{csrf_field()}}
    <select name="edit_ym" onchange="javascript:submit(this.form);">
        @foreach($select_ym as $option_ym => $data_on)
        <option value="{{$option_ym}}"@if($data_on) style="background-color: #f0f0f0;" @endif @if($edit_ym == $option_ym) selected="selected"@endif>{{$option_ym}}</option>
        @endforeach()
    </select>
</form>
<br />

<form action="{{url('/')}}/plugin/openingcalendars/save/{{$page->id}}/{{$frame_id}}" method="POST" class="" name="chenge_plan">
    {{ csrf_field() }}
    <input type="hidden" name="target_ym" value="{{$edit_ym}}">

    <table style="background-color: #ffffff;">
        <tr>
            <th>日</th>
            <th>パターン</th>
        </tr>
        <tr class="border-bottom border-primary">
            <td class="px-1"><label class="my-1">全て</label></td>
                @foreach($patterns as $pattern)
            <td class="px-1">
{{$pattern->caption}}<br />
                <label><input type="radio" name="all_check" onclick="javascript:set_all_time({{$loop->index}});">{{$pattern->pattern}}</label>
            </td>
                @endforeach
        </tr>
        @foreach($edit_days as $key => $edit_day)
        <tr class="border-bottom">
            <td class="px-1" nowrap><label class="my-1">{{$key}}日({{$week_names[$key]}})</label></td>
                @foreach($patterns as $pattern)
            <td class="px-1" nowrap>
                <label class="my-1">
                    @if ($edit_day && $edit_day->openingcalendars_patterns_id == $pattern->id)
                    <input type="radio" value="{{$pattern->id}}" name="openingcalendars[{{$key}}]" checked="checked">{{$pattern->pattern}}
                    @else
                    <input type="radio" value="{{$pattern->id}}" name="openingcalendars[{{$key}}]">{{$pattern->pattern}}
                    @endif
                </label>
            </td>
                @endforeach
        </tr>
    @endforeach
    </table>

    <div class="form-group mt-3">
        <label class="control-label">月毎のコメント</label>
        <textarea name="comments" class="form-control" rows=2>{!!old('comments', $months->comments)!!}</textarea>
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 変更</span></button>
    </div>
</form>
@endsection
