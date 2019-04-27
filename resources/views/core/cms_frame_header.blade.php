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

{{-- パネルヘッダーはフレームタイトルが空、認証していない場合はパネルヘッダーを使用しない --}}
@if (!Auth::check() && empty($frame->frame_title))
@else

    {{-- 認証していてフレームタイトルが空の場合は、パネルヘッダーの中央にアイコンを配置したいので、高さ指定する。 --}}
    @if (Auth::check() && empty($frame->frame_title))
        <div class="panel-heading" style="padding-top: 0px;padding-bottom: 0px;height: 20px;">
    @else
        <div class="panel-heading">
    @endif

    {{-- フレームタイトル --}}
    {{$frame->frame_title}}

    @auth
    @if (request()->core_action != 'frame_setting') {{-- フレーム内容を設定中は誤操作防止のためフレーム移動系の処理は隠す --}}

        {{-- フレームを配置したページのみ、編集できるようにする。 --}}
        @if ($frame->page_id == $current_page->id)
        <div class="pull-right">
            {{-- 上移動。POSTのためのフォーム --}}
            <form action="/core/frame/sequenceUp/{{$current_page->id}}/{{ $frame->frame_id }}/{{ $frame->area_id }}" name="form_{{ $frame->frame_id }}_up" method="POST" class="visible-lg-inline visible-md-inli	ne visible-sm-inline visible-xs-inline">
                {{ csrf_field() }}
                <a href="javascript:form_{{ $frame->frame_id }}_up.submit();"><span class="glyphicon glyphicon-chevron-up bg-{{$frame->frame_design}}"></span></a> 
            </form>

            {{-- 下移動。POSTのためのフォーム --}}
            <form action="/core/frame/sequenceDown/{{$current_page->id}}/{{ $frame->frame_id }}/{{ $frame->area_id }}" name="form_{{ $frame->frame_id }}_down" method="POST" class="visible-lg-inline visible-md-inline visible-sm-inline visible-xs-inline">
                {{ csrf_field() }}
                <a href="javascript:form_{{ $frame->frame_id }}_down.submit();"><span class="glyphicon glyphicon-chevron-down bg-{{$frame->frame_design}}"></span></a> 
            </form>

            {{-- 変更画面へのリンク --}}
            <a href="{{$current_page->permanent_link}}?core_action=frame_setting&frame_id={!!$frame->frame_id!!}#{!!$frame->frame_id!!}"><span class="glyphicon glyphicon-edit bg-{{$frame->frame_design}}"></span></a>

{{-- モーダル実装 --}}
            {{-- 変更画面へのリンク --}}
{{--
            <a href="#" data-href="{{URL::to('/')}}/core/frame/edit/{{$current_page->id}}/{{ $frame->frame_id }}" data-toggle="modal" data-target="#modalDetails"><span class="glyphicon glyphicon-edit bg-{{$frame->frame_design}}"></span></a>
--}}

            {{-- 削除。POSTのためのフォーム --}}
        </div>
        @else
        <div class="pull-right">
            <span class="glyphicon glyphicon-chevron-up bg-{{$frame->frame_design}}" style="color:#ccc;"></span> 
            <span class="glyphicon glyphicon-chevron-down bg-{{$frame->frame_design}}" style="color:#ccc;"></span> 
            <span class="glyphicon glyphicon-edit bg-{{$frame->frame_design}}" style="color:#ccc;"></span> 
        </div>
        @endif

    @endif
    @endauth
</div>
@endif
