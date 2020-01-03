{{--
 * タブ表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category タブ・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contsnts_$frame->id")
<ul>
@foreach($frames as $frame_record)
    <li id="tab_{{$frame_record->id}}" class="tab_{{$frame_record->id}}@if (isset($tabs) && $tabs->default_frame_id == $frame_record->id) current @endif"><a href="#" onclick="return false;">{{$frame_record->frame_title}}</a></li>
@endforeach
</ul>

<script>
$(document).ready(function(){
    $(function(){
    @foreach($frames as $frame_record)
        $('.tab_{{$frame_record->id}}').on('click', function() {
        @foreach($frames2 as $frame2)
          @if($frame_record->id == $frame2->id)
            $('#frame-{{$frame2->id}}').removeClass('d-none');
            $('#frame-{{$frame2->id}}').addClass('d-block');
            $('.tab_{{$frame2->id}}').addClass('current');
          @else
            $('#frame-{{$frame2->id}}').removeClass('d-block');
            $('#frame-{{$frame2->id}}').addClass('d-none');
            $('.tab_{{$frame2->id}}').removeClass('current');
          @endif
        @endforeach
        });
    @endforeach
    });
});
</script>
@endsection
