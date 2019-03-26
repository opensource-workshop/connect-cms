{{--
 * CMSフレームヘッダー
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $current_page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
{{-- フレームヘッダー(表示) --}}
<div class="panel-heading">
    {{$frame->frame_title}}
    @auth
    @if (request()->core_action != 'frame_setting') {{-- フレーム内容を設定中は誤操作防止のためフレーム移動系の処理は隠す --}}
    <div class="pull-right">
        {{-- 上移動。POSTのためのフォーム --}}
        <form action="/core/frame/sequenceUp/{{$current_page->id}}/{{ $frame->frame_id }}" name="form_{{ $frame->frame_id }}_up" method="POST" class="visible-lg-inline visible-md-inline visible-sm-inline visible-xs-inline">
            {{ csrf_field() }}
            <a href="javascript:form_{{ $frame->frame_id }}_up.submit();"><span class="glyphicon glyphicon-chevron-up bg-{{$frame->frame_design}}"></a> 
        </form>

        {{-- 下移動。POSTのためのフォーム --}}
        <form action="/core/frame/sequenceDown/{{$current_page->id}}/{{ $frame->frame_id }}" name="form_{{ $frame->frame_id }}_down" method="POST" class="visible-lg-inline visible-md-inline visible-sm-inline visible-xs-inline">
            {{ csrf_field() }}
            <a href="javascript:form_{{ $frame->frame_id }}_down.submit();"><span class="glyphicon glyphicon-chevron-down bg-{{$frame->frame_design}}"></a> 
        </form>

        {{-- 変更画面へのリンク --}}
        <a href="{{$current_page->permanent_link}}?core_action=frame_setting&frame_id={!!$frame->frame_id!!}#{!!$frame->frame_id!!}"><span class="glyphicon glyphicon-edit bg-{{$frame->frame_design}}"></a>

        {{-- 削除。POSTのためのフォーム --}}
{{-- // 削除は編集画面の削除タブに移した。誤操作防止のため。
        <form action="/core/frame/destroy/{{$current_page->id}}/{{$frame->frame_id}}" name="form_{{ $frame->frame_id }}_del" method="POST" class="visible-lg-inline visible-md-inline visible-sm-inline visible-xs-inline">
            {{ csrf_field() }}
            <a href="javascript:if (confirm('フレームを削除します。\nよろしいですか？') == true){ form_{{$frame->frame_id}}_del.submit();}"><span class="glyphicon glyphicon-trash bg-{{$frame->frame_design}}"></a> 
        </form>
--}}
    </div>
    @endif
    @endauth
</div>
