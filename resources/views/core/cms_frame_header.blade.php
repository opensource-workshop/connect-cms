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
@elseif (Auth::check() && empty($frame->frame_title) && !( Auth::user()->can('frames.move')))
@else
    @php
        $class_border = "";
        // 
        if ($frame->frame_design == 'none') {
            $class_border = " border-0";
        }
    @endphp

    {{-- 認証していてフレームタイトルが空の場合は、パネルヘッダーの中央にアイコンを配置したいので、高さ指定する。 --}}
    @if (Auth::check() && empty($frame->frame_title) && app('request')->input('mode') == 'preview')
        <h1 class="card-header bg-transparent border-0" style="padding-top: 0px;padding-bottom: 0px;">
    @elseif (Auth::check() && empty($frame->frame_title))
        <h1 class="card-header bg-transparent border-0" style="padding-top: 0px;padding-bottom: 0px;height: 24px;">
    @else
        <h1 class="card-header bg-{{$frame->frame_design}} cc-{{$frame->frame_design}}-font-color">
    @endif

    {{-- フレームタイトル --}}
    {{$frame->frame_title}}
    @if (Auth::check() && $frame->default_hidden)
        <small><span class="badge badge-warning">初期非表示</span></small>
    @endif

    @if (Auth::check() && $frame->none_hidden)
        <small><span class="badge badge-warning">データがない場合は非表示</span></small>
    @endif

    @if (Auth::check() && $frame->page_only == 2 && $page->id == $frame->page_id)
        <small><span class="badge badge-warning">このページのみ表示しない。</span></small>
    @endif

    {{-- ログインしていて、システム管理者、サイト管理者権限があれば、編集機能を有効にする --}}
    @if (Auth::check() &&
        (Auth::user()->can('role_arrangement')) &&
         app('request')->input('mode') != 'preview')

        {{-- フレームを配置したページのみ、編集できるようにする。 --}}
{{--
        @if ($frame->page_id == $page->id)
--}}
        <div class="float-right">

            {{-- プラグイン名 --}}
            <span class="badge badge-secondary">
                {{ Plugins::query()->where('plugin_name', $frame->plugin_name)->first()->plugin_name_full }}
            </span>

            {{-- 上移動。POSTのためのフォーム --}}
            <form action="{{url('/')}}/core/frame/sequenceUp/{{$page->id}}/{{ $frame->frame_id }}/{{ $frame->area_id }}" name="form_{{ $frame->frame_id }}_up" method="POST" class="form-inline d-inline">
                {{ csrf_field() }}
                <a href="javascript:form_{{ $frame->frame_id }}_up.submit();"><i class="fas fa-angle-up bg-{{$frame->frame_design}} align-bottom cc-font-color"></i></a> 
            </form>

            {{-- 下移動。POSTのためのフォーム --}}
            <form action="{{url('/')}}/core/frame/sequenceDown/{{$page->id}}/{{ $frame->frame_id }}/{{ $frame->area_id }}" name="form_{{ $frame->frame_id }}_down" method="POST" class="form-inline d-inline">
                {{ csrf_field() }}
                <a href="javascript:form_{{ $frame->frame_id }}_down.submit();"><i class="fas fa-angle-down bg-{{$frame->frame_design}} align-bottom cc-font-color"></i></a> 
            </form>

            {{-- 変更画面へのリンク --}}
            <a href="{{url('/')}}/plugin/{{$plugin_instances[$frame->frame_id]->frame->plugin_name}}/{{$plugin_instances[$frame->frame_id]->getFirstFrameEditAction()}}/{{$page->id}}/{{$frame->frame_id}}#frame-{{$frame->frame_id}}"><small><i class="fas fa-cog bg-{{$frame->frame_design}} cc-font-color"></i></small></a>

{{-- モーダル実装 --}}
            {{-- 変更画面へのリンク --}}
{{--
            <a href="#" data-href="{{URL::to('/')}}/core/frame/edit/{{$page->id}}/{{ $frame->frame_id }}" data-toggle="modal" data-target="#modalDetails"><span class="glyphicon glyphicon-edit bg-{{$frame->frame_design}}"></a>
--}}

            {{-- 削除。POSTのためのフォーム --}}
        </div>
{{--
        @else
        <div class="float-right">
            <i class="fas fa-angle-up bg-{{$frame->frame_design}} align-bottom text-secondary cc-font-color"></i>
            <i class="fas fa-angle-down bg-{{$frame->frame_design}} align-bottom text-secondary cc-font-color"></i>
            <i class="fas fa-cog bg-{{$frame->frame_design}} small text-secondary cc-font-color"></i>
        </div>
        @endif
--}}
    @endif
    </h1>
@endif
