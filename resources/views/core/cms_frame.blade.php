{{--
 * CMSフレーム画面
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
@php
// 独自クラス名 ＆ フレーム＆アクションのクラス名生成
if ($frame->classname) {
    $frame_classname = $frame->classname;
}
else {
    $frame_classname = 'frame-' . $frame->frame_id;
}
if ($frame_id == $frame->frame_id) {
    $frame_classname .= ' ' . $frame_classname . '-' . $action;
}

$plugin_name = ' plugin-' . $frame->plugin_name . ' ';

$default_hidden = '';
if ($frame->default_hidden && (!Auth::check() || !Auth::user()->can('role_arrangement'))) {
    $default_hidden = ' d-none';
}
// フレーム非表示の判定
$hidden_flag = '';
if ($default_hidden == '' & isset($frame->hidden_flag) && $frame->hidden_flag == true && (!Auth::check() || !Auth::user()->can('role_arrangement'))) {
    $hidden_flag = ' d-none';
}

@endphp

{{-- 非ログイン、且つ、非表示条件（非公開、又は、限定公開）にマッチした場合はフレームを非表示にする --}}
@if (
        !Auth::check() &&
        (
            $frame->content_open_type == ContentOpenType::always_close ||
            (
                $frame->content_open_type == ContentOpenType::limited_open && 
                !Carbon::now()->between($frame->content_open_date_from, $frame->content_open_date_to)
            )
        )
    )
    @php
        $hidden_flag = ' d-none';
    @endphp
@endif

@if($frame->frame_col==0)
<div class="p-0 col-12 @if ($frame->area_id==2 && !$loop->last) @endif {{$frame_classname}}{{$plugin_name}}{{$default_hidden}}{{$hidden_flag}}{{" $frame->plugin_name-$frame->template"}}" id="frame-{{ $frame->frame_id }}">
@else
<div class="p-0 col-sm-{{$frame->frame_col}} @if ($frame->area_id==2 &&!$loop->last) @endif {{$frame_classname}}{{$plugin_name}}{{$hidden_flag}}{{" $frame->plugin_name-$frame->template"}}" id="frame-{{ $frame->frame_id }}">
@endif

@if ($frame->browser_width == '100%')
<div class="">
@else
<div class="container">
@endif

    <div class="card mb-3 @if ($frame->frame_design == 'none') border-0 frame-design-none @endif" id="frame-card-{{ $frame->frame_id }}">

        {{-- フレームヘッダー(表示) --}}
        @include('core.cms_frame_header')

        {{-- フレーム(各種編集) --}}
        @if (($action == 'frame_setting' || $action == 'frame_delete') && $frame_id == $frame->frame_id)

            {{-- フレーム(コア・編集) --}}
            @if ($action == 'frame_setting')
                @can("frames.edit")
                    @include('core.cms_frame_edit')
                @else
                    <div class="card-body">
                        @include('errors.403')
                    </div>
                @endcan

            {{-- フレーム(コア・削除) --}}
            @elseif ($action == 'frame_delete')
                @can("frames.delete")
                    @include('core.cms_frame_delete')
                @else
                    <div class="card-body">
                        @include('errors.403')
                    </div>
                @endcan

            {{-- フレーム(プラグイン) --}}
            @else
                {!!$plugin_instances[$frame->frame_id]->invoke($plugin_instances[$frame->frame_id], app('request'), Request::get('action'), $page->id, $frame->frame_id, null)!!}
            @endif

        {{-- 通常のコンテンツ表示 --}}
        @else

            {{-- フレームボディ --}}
{{--
            @if ($frame->frame_design == 'none')
            <div class="card-body" style="padding: 0; clear: both;">
            @else
            <div class="card-body">
            @endif
--}}
                {{-- プラグが設定されていれば、プラグ優先 --}}
                {{-- 実際には、プラグのデータを画面に渡すなどという動きをしたい --}}

{{--                @if ($frame->plug_name) --}}
                    {{-- プラグが選択されていたら --}}
{{--                    @include('plug_call', [$frame]) --}}

                {{-- ルーティングする新しい呼び方 --}}
{{--                @elseif (isset( $action ) && $action != '' && $frame_id == $frame->frame_id) --}}

                @if (Config::get('app.debug'))
                    @if (isset( $action ) && $action != '' && $frame_id == $frame->frame_id)
                        {!!$plugin_instances[$frame->frame_id]->invoke($plugin_instances[$frame->frame_id], app('request'), $action, $page->id, $frame->frame_id, $id)!!}
                    @else
                        {!!$plugin_instances[$frame->frame_id]->index(app('request'), $page->id, $frame->frame_id)!!}
                    @endif
                @else

                @if (isset( $action ) && $action != '' && $frame_id == $frame->frame_id)
                    {{-- アクションが指定されていてフレームID の指定があれば、プラグインのアクションを呼ぶ --}}
                    @php
                        // 例外、エラーを補足しつつ、画面の標準関数(index)を呼ぶ。
                        try {
                            echo $plugin_instances[$frame->frame_id]->invoke($plugin_instances[$frame->frame_id], app('request'), $action, $page->id, $frame->frame_id, $id);
                        } catch (\Throwable $e) {
                            $plugin_instances[$frame->frame_id]->putLog($e);
                    @endphp
                        @include('errors.500_inframe' ,['debug_message' => $e->getMessage()])
                    @php
                        }
                    @endphp
                @else
                    {{-- アクション名の指定などがない通常の表示パターン --}}
                    {{-- プラグインの表示アクションを呼び出し。プラグイン側でフレームボディ内の画面出力まで行う。 --}}
                    @php
                        // 例外、エラーを補足しつつ、画面の標準関数(index)を呼ぶ。
                        try {
                            echo $plugin_instances[$frame->frame_id]->index(app('request'), $page->id, $frame->frame_id);
                        } catch (\Throwable $e) {
                            $plugin_instances[$frame->frame_id]->putLog($e);
                    @endphp
                        @include('errors.500_inframe' ,['debug_message' => $e->getMessage()])
                    @php
                        }
                    @endphp
                @endif

                @endif
{{--
            </div>
--}}
        @endif
    </div>
</div>
</div>
