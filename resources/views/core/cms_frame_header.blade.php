{{--
 * CMSフレームヘッダー
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
{{-- フレームヘッダー(表示) --}}

{{-- パネルヘッダーはフレームタイトルが空、認証していない場合はパネルヘッダーを使用しない --}}
@if (!Auth::check() && empty($frame->frame_title))
@elseif (Auth::check() && empty($frame->frame_title) && !( Auth::user()->can(Config::get('cc_role.ROLE_SYSTEM_MANAGER')) || Auth::user()->can(Config::get('cc_role.ROLE_SITE_MANAGER'))))
@else

{{-- Auth::user()->role --}}

    {{-- 認証していてフレームタイトルが空の場合は、パネルヘッダーの中央にアイコンを配置したいので、高さ指定する。 --}}
    @if (Auth::check() && empty($frame->frame_title))
        <div class="panel-heading" style="padding-top: 0px;padding-bottom: 0px;height: 20px;">
    @else
        <div class="panel-heading">
    @endif

    {{-- フレームタイトル --}}
    {{$frame->frame_title}}

    {{-- ログインしていて、システム管理者、サイト管理者権限があれば、編集機能を有効にする --}}
    @if (Auth::check() && ( Auth::user()->can(Config::get('cc_role.ROLE_SYSTEM_MANAGER')) || Auth::user()->can(Config::get('cc_role.ROLE_SITE_MANAGER'))))

        {{-- フレームを配置したページのみ、編集できるようにする。 --}}
        @if ($frame->page_id == $page->id)
        <div class="pull-right">
            {{-- 上移動。POSTのためのフォーム --}}
            <form action="/core/frame/sequenceUp/{{$page->id}}/{{ $frame->frame_id }}/{{ $frame->area_id }}" name="form_{{ $frame->frame_id }}_up" method="POST" class="visible-lg-inline visible-md-inli	ne visible-sm-inline visible-xs-inline">
                {{ csrf_field() }}
                <a href="javascript:form_{{ $frame->frame_id }}_up.submit();"><span class="glyphicon glyphicon-chevron-up bg-{{$frame->frame_design}}"></span></a> 
            </form>

            {{-- 下移動。POSTのためのフォーム --}}
            <form action="/core/frame/sequenceDown/{{$page->id}}/{{ $frame->frame_id }}/{{ $frame->area_id }}" name="form_{{ $frame->frame_id }}_down" method="POST" class="visible-lg-inline visible-md-inline visible-sm-inline visible-xs-inline">
                {{ csrf_field() }}
                <a href="javascript:form_{{ $frame->frame_id }}_down.submit();"><span class="glyphicon glyphicon-chevron-down bg-{{$frame->frame_design}}"></span></a> 
            </form>

            {{-- 変更画面へのリンク --}}
            <a href="{{url('/')}}/plugin/{{$plugin_instances[$frame->frame_id]->frame->plugin_name}}/{{$plugin_instances[$frame->frame_id]->getFirstFrameEditAction()}}/{{$page->id}}/{{$frame->frame_id}}#{{$frame->frame_id}}"><span class="glyphicon glyphicon-edit bg-{{$frame->frame_design}}"></span></a>

{{-- モーダル実装 --}}
            {{-- 変更画面へのリンク --}}
{{--
            <a href="#" data-href="{{URL::to('/')}}/core/frame/edit/{{$page->id}}/{{ $frame->frame_id }}" data-toggle="modal" data-target="#modalDetails"><span class="glyphicon glyphicon-edit bg-{{$frame->frame_design}}"></span></a>
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
</div>
@endif
