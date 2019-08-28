{{--
 * CMSフレーム画面
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
@if($frame->frame_col==0)
<div class="col-sm-12 @if ($frame->area_id==2 && !$loop->last) pr-0 @endif" id="{{ $frame->frame_id }}">
@else
<div class="col-sm-{{$frame->frame_col}} @if ($frame->area_id==2 &&!$loop->last) pr-0 @endif" id="{{ $frame->frame_id }}">
@endif

    @if ($frame->frame_design == 'none')
    <div class="card mb-3" style="-webkit-box-shadow: none; box-shadow: none; background-color: transparent;">
    @else
    <div class="card mb-3">
    @endif

        {{-- フレームヘッダー(表示) --}}
        @include('core.cms_frame_header')

        {{-- フレーム(各種編集) --}}
{{--        @if (!empty(app('request')->input('frame_action')) && app('request')->input('frame_id') == $frame->frame_id) --}}
{{--        @if ((app('request')->input('action') == 'frame_setting' || app('request')->input('action') == 'frame_delete') && app('request')->input('frame_id') == $frame->frame_id) --}}
        @if (($action == 'frame_setting' || $action == 'frame_delete') && $frame_id == $frame->frame_id)

            {{-- フレーム(コア・編集) --}}
{{--            @if (app('request')->input('frame_action') == 'frame_setting') --}}
{{--            @if (app('request')->input('action') == 'frame_setting') --}}
            @if ($action == 'frame_setting')
                @include('core.cms_frame_edit')

            {{-- フレーム(コア・削除) --}}
{{--            @elseif (app('request')->input('frame_action') == 'frame_delete') --}}
{{--            @elseif (app('request')->input('action') == 'frame_delete') --}}
            @elseif ($action == 'frame_delete')
                @include('core.cms_frame_delete')

            {{-- フレーム(プラグイン) --}}
            @else
{{--                {!!$plugin_instances[$frame->frame_id]->invoke($plugin_instances[$frame->frame_id], app('request'), app('request')->input('frame_action'), $page->id, $frame->frame_id, null)!!} --}}
                {!!$plugin_instances[$frame->frame_id]->invoke($plugin_instances[$frame->frame_id], app('request'), Request::get('action'), $page->id, $frame->frame_id, null)!!}

            @endif

        {{-- 通常のコンテンツ表示 --}}
        @else

            {{-- フレームボディ --}}
            @if ($frame->frame_design == 'none')
            <div class="card-body" style="padding: 0; clear: both;">
            @else
            <div class="card-body">
            @endif

                {{-- プラグが設定されていれば、プラグ優先 --}}
                {{-- 実際には、プラグのデータを画面に渡すなどという動きをしたい --}}

                @if ($frame->plug_name)
                    {{-- プラグが選択されていたら --}}
                    @include('plug_call', [$frame])

                {{-- ルーティングする新しい呼び方 --}}
                @elseif (isset( $action ) && $action != '' && $frame_id == $frame->frame_id)
                    {{-- アクションが指定されていてフレームID の指定があれば、プラグインのアクションを呼ぶ --}}
                    {!! $plugin_instances[$frame->frame_id]->invoke($plugin_instances[$frame->frame_id], app('request'), $action, $page->id, $frame->frame_id, $id) !!}

                {{-- パラメータ指定する古い呼び方 --}}
                @elseif (app('request')->input('action') != '' && app('request')->input('frame_id') == $frame->frame_id)
                    {{-- アクションが指定されていてフレームID の指定があれば、プラグインのアクションを呼ぶ --}}
                    {!! $plugin_instances[$frame->frame_id]->invoke($plugin_instances[$frame->frame_id], app('request'), Request::get('action'), $page->id, $frame->frame_id, Request::get('id')) !!}

                @else
                    {{-- アクション名の指定などがない通常の表示パターン --}}
                    {{-- プラグインの表示アクションを呼び出し。プラグイン側でフレームボディ内の画面出力まで行う。 --}}
                    {!! $plugin_instances[$frame->frame_id]->index(app('request'), $page->id, $frame->frame_id) !!}

                @endif
            </div>
        @endif
    </div>
</div>
