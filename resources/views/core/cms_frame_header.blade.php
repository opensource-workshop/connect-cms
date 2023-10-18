{{--
 * CMSフレームヘッダー
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
{{-- フレームヘッダー(表示) --}}

@php
// フレームヘッダ表示フラグ
$canFrameHaederDisplayed = false;

// フレームタイトルがあれば、フレームヘッダ表示する。
if ($frame->frame_title) {
    $canFrameHaederDisplayed = true;   // 表示
}

// フレームタイトルが無くても、権限あれば、フレームヘッダ表示する。
//   - Gate::check()は、Auth::user()->can()と同等メソッド。 @see https://readouble.com/laravel/6.x/ja/authorization.html#authorizing-actions-via-gates
if (Gate::check(['role_frame_header', 'frames.move', 'frames.edit'], [[null,null,null,$frame]])) {
    $canFrameHaederDisplayed = true;   // 表示
}
@endphp
{{--
フレームヘッダはフレームタイトルが空、認証していない場合はフレームヘッダを使用しない
　・ログインしてない & フレームタイトル空 = フレームヘッダ表示しない
　・ログインしてる & フレームタイトル空 & frames.move 権限ない = フレームヘッダ表示しない
　・それ以外表示
　　・ログインしてる & フレームタイトルあり = フレームヘッダ表示する。
　※ これ抜けてた（ログインしてる & フレームタイトルなし & 権限ある = フレームヘッダ表示する）

@if (!Auth::check() && empty($frame->frame_title))
@elseif (Auth::check() && empty($frame->frame_title) && !( Auth::user()->can('frames.move')))
@else
--}}
@if ($canFrameHaederDisplayed)
    @php
        $class_border = "";
        //
        if ($frame->frame_design == 'none') {
            $class_border = " border-0";
        }
    @endphp

    {{-- 認証していてフレームタイトルが空の場合は、パネルヘッダーの中央にアイコンを配置したいので、高さ指定する。 --}}
    @if (Auth::check() && empty($frame->frame_title) && app('request')->input('mode') == 'preview')
        @php $class_header_bg = "bg-transparent"; @endphp
        <h1 class="card-header {{$class_header_bg}} border-0" style="padding-top: 0px;padding-bottom: 0px;">
    @elseif (Auth::check() && empty($frame->frame_title))
        @php $class_header_bg = "bg-transparent"; @endphp
        <h1 class="card-header {{$class_header_bg}} border-0" style="padding-top: 0px;padding-bottom: 0px;">
    @else
        @php $class_header_bg = "bg-{$frame->frame_design}"; @endphp
        <h1 class="card-header {{$class_header_bg}} cc-{{$frame->frame_design}}-font-color">
    @endif

    {{-- フレームタイトル --}}
    {{$frame->frame_title}}

    {{-- 各ステータスラベルは、ログインしている＆プラグイン管理者＆プレビューモードではない状態の時のみ表示 --}}
    @if (Auth::check() &&
        Auth::user()->can('role_arrangement') &&
        app('request')->input('mode') != 'preview')

        @if ($frame->default_hidden)
            <small><span class="badge badge-warning">初期非表示</span></small>
        @endif

        @if ($frame->none_hidden)
            <small><span class="badge badge-warning">データがない場合は非表示</span></small>
        @endif

        @if ($frame->page_only == 1 && $page->id == $frame->page_id)
            <small><span class="badge badge-warning">このページのみ表示する。</span></small>
        @endif

        @if ($frame->page_only == 2 && $page->id == $frame->page_id)
            <small><span class="badge badge-warning">このページのみ表示しない。</span></small>
        @endif
    @endif

    {{-- 公開以外の場合にステータス表示 ※デフォルト状態の公開もステータス表示すると画面表示が煩雑になる為、意識的な設定（非公開、又は、限定公開）のみステータス表示を行う --}}
    @if (Auth::check() && $frame->content_open_type != ContentOpenType::always_open)
        <small>
            <span class="badge badge-warning">
                <a href="{{URL::to('/')}}/plugin/{{$frame->plugin_name}}/frame_setting/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}">
                    <i class="fas fa-cog"></i>
                </a>
                {{ ContentOpenType::getDescription($frame->content_open_type) }}
                @if ($frame->content_open_type == ContentOpenType::limited_open)
                    {{-- 期限付き公開の場合は日付も表示 --}}
                    {{ '（' . Carbon::parse($frame->content_open_date_from)->format('Y/n/j H:i:s') . ' - ' . Carbon::parse($frame->content_open_date_to)->format('Y/n/j H:i:s') . '）' }}
                @endif
            </span>
        </small>
    @endif
    {{-- ログインしていて、権限があれば、編集機能を有効にする --}}
    {{--
    @if (Auth::check() &&
        (Auth::user()->can('role_arrangement')) &&
         app('request')->input('mode') != 'preview')
    --}}
    @if (Gate::check(['role_frame_header', 'frames.move', 'frames.edit'], [[null,null,null,$frame]]) &&
        app('request')->input('mode') != 'preview')

        {{-- フレームを配置したページのみ、編集できるようにする。 --}}
{{--
        @if ($frame->page_id == $page->id)
--}}
        <div class="float-right">
            @php
                // [TODO] あれ？フレームごとにselectしていました。今後はどっか上流で plugin_name_full を持つ方向がよいかと。
                $plugins_name = Plugins::where('plugin_name', $frame->plugin_name)->first();
                if ($plugins_name) {
                    $plugin_name_full = $plugins_name->plugin_name_full;
                } else {
                    $plugin_name_full = $frame->plugin_name;
                }
            @endphp

            {{-- プラグイン名 --}}
            <span class="badge badge-secondary">
                {{--
                @if (Plugins::where('plugin_name', $frame->plugin_name)->first())
                    {{ Plugins::where('plugin_name', $frame->plugin_name)->first()->plugin_name_full }}
                @else
                    {{$frame->plugin_name}}
                @endif
                --}}
                {{$plugin_name_full}}
            </span>

            {{-- ページ内リンク --}}
            <a href="{{URL::to($page->permanent_link)}}#frame-{{ $frame->frame_id }}" title="ページ内リンク"><small><i class="fas fa-link {{$class_header_bg}} cc-font-color"></i></small></a>

            {{-- 上移動。POSTのためのフォーム --}}
            <form action="{{url('/')}}/core/frame/sequenceUp/{{$page->id}}/{{ $frame->frame_id }}/{{ $frame->area_id }}#frame-{{$frame->frame_id}}" name="form_{{ $frame->frame_id }}_up" method="POST" class="form-inline d-inline">
                {{ csrf_field() }}
                <a href="javascript:form_{{ $frame->frame_id }}_up.submit();" title="上移動" id="frame_up_{{ $frame->frame_id }}"><i class="fas fa-angle-up {{$class_header_bg}} align-bottom cc-font-color"></i></a>
            </form>

            {{-- 下移動。POSTのためのフォーム --}}
            <form action="{{url('/')}}/core/frame/sequenceDown/{{$page->id}}/{{ $frame->frame_id }}/{{ $frame->area_id }}#frame-{{$frame->frame_id}}" name="form_{{ $frame->frame_id }}_down" method="POST" class="form-inline d-inline">
                {{ csrf_field() }}
                <a href="javascript:form_{{ $frame->frame_id }}_down.submit();" title="下移動" id="frame_down_{{ $frame->frame_id }}"><i class="fas fa-angle-down {{$class_header_bg}} align-bottom cc-font-color"></i></a>
            </form>

            {{-- 変更画面へのリンク --}}
            <a href="{{url('/')}}/plugin/{{$plugin_instances[$frame->frame_id]->frame->plugin_name}}/{{$plugin_instances[$frame->frame_id]->getFirstFrameEditAction()}}/{{$page->id}}/{{$frame->frame_id}}#frame-{{$frame->frame_id}}" title="{{$plugin_name_full}}設定"><small><i class="fas fa-cog {{$class_header_bg}} cc-font-color"></i></small></a>

{{-- モーダル実装 --}}
            {{-- 変更画面へのリンク --}}
{{--
            <a href="#" data-href="{{URL::to('/')}}/core/frame/edit/{{$page->id}}/{{ $frame->frame_id }}" data-toggle="modal" data-target="#modalDetails"><span class="glyphicon glyphicon-edit {{$class_header_bg}}"></a>
--}}

            {{-- 削除。POSTのためのフォーム --}}
        </div>
{{--
        @else
        <div class="float-right">
            <i class="fas fa-angle-up {{$class_header_bg}} align-bottom text-secondary cc-font-color"></i>
            <i class="fas fa-angle-down {{$class_header_bg}} align-bottom text-secondary cc-font-color"></i>
            <small><i class="fas fa-cog {{$class_header_bg}} text-secondary cc-font-color"></i></small>
        </div>
        @endif
--}}
    @endif
    </h1>
@endif
