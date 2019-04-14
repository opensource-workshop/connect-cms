{{--
 * CMSフレーム画面
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $current_page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
<div class="@if($frame->frame_col==0) col-sm-12 @else col-sm-{{$frame->frame_col}} @endif" id="{{ $frame->frame_id }}">
    <div class="panel panel-{{$frame->frame_design}}">

        {{-- フレームヘッダー(表示) --}}
        @include('core.cms_frame_header')

        {{-- フレーム(編集) --}}
        @if (app('request')->input('core_action') == 'frame_setting' && app('request')->input('frame_id') == $frame->frame_id)
            @include('core.cms_frame_edit')
        @endif

        {{-- フレーム(削除) --}}
        @if (app('request')->input('core_action') == 'frame_delete' && app('request')->input('frame_id') == $frame->frame_id)
            @include('core.cms_frame_delete')
        @endif

        {{-- フレームボディ --}}
        <div class="panel-body">

            {{-- プラグが設定されていれば、プラグ優先 --}}
            {{-- 実際には、プラグのデータを画面に渡すなどという動きをしたい --}}

            @if ($frame->plug_name)
                {{-- プラグが選択されていたら --}}
                @include('plug_call', [$frame])

            {{-- ルーティングする新しい呼び方 --}}
            @elseif (isset( $action ) && $action != '' && $frame_id == $frame->frame_id)
                {{-- アクションが指定されていてフレームID の指定があれば、プラグインのアクションを呼ぶ --}}
                {!! $plugin_instances[$frame->plugin_name]->invoke($plugin_instances[$frame->plugin_name], app('request'), $action, $current_page->id, $frame->frame_id, $id) !!}

            {{-- パラメータ指定する古い呼び方 --}}
            @elseif (app('request')->input('action') != '' && app('request')->input('frame_id') == $frame->frame_id)
                {{-- アクションが指定されていてフレームID の指定があれば、プラグインのアクションを呼ぶ --}}
                {!! $plugin_instances[$frame->plugin_name]->invoke($plugin_instances[$frame->plugin_name], app('request'), Request::get('action'), $current_page->id, $frame->frame_id, Request::get('id')) !!}

{{-- アクション名固定の時のロジック --}}
             @elseif (app('request')->input('action') == 'edit' && app('request')->input('frame_id') == $frame->frame_id) --}}
                    {{-- プラグインの編集アクションを呼び出し。プラグイン側でフレームボディ内の画面出力まで行う。 --}}
                    {{-- $plugin_instances[$frame->plugin_name]->edit(app('request'), $frame->frame_id, $current_page->id, app('request')->input('id'))
{{--                {!! $plugin_instances[$frame->plugin_name]->invoke($plugin_instances[$frame->plugin_name], app('request'), Request::get('action'), $current_page->id, $frame->frame_id, Request::get('id')) !!} --}}


            @else
                {{-- アクション名の指定などがない通常の表示パターン --}}
                {{-- プラグインの表示アクションを呼び出し。プラグイン側でフレームボディ内の画面出力まで行う。 --}}
                {!! $plugin_instances[$frame->plugin_name]->index(app('request'), $current_page->id, $frame->frame_id) !!}

            @endif
        </div>
    </div>
</div>
