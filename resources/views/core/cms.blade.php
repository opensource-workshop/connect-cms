{{--
 * CMSメイン画面
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
{{-- 大元のレイアウトの継承とページコンテンツは大元のレイアウトに埋め込むために @section で定義する --}}
@extends('layouts.app')
@section('content')
<div class="container-fluid p-0">


{{-- *********************************************************** --}}

{{-- <a href="#" data-href="/test_dir/load.html" data-toggle="modal" data-target="#modalDetails">リンク</a> --}}
{{-- <a href="#" data-href="{{URL::to('/')}}/test/1" data-toggle="modal" data-target="#modalDetails">リンク</a> --}}

<!-- Modal -->
<div class="modal fade" id="modalDetails" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        </div>
    </div>
</div>

<script>
$(function () {
    //任意のリンクをモーダル内に読み込む
    $("#modalDetails").on("show.bs.modal", function(e) {
        var link = $(e.relatedTarget); //クリックしたセルのオブジェクトデータ
        $(this).find(".modal-content").load(link.attr("data-href"));
    });
});

// フラッシュメッセージのfadeout
$(function(){
    $('.connect-flash').fadeOut(10000);
});
</script>

{{-- *********************************************************** --}}
    {{-- フラッシュメッセージ表示(fadeout あり) --}}
    @if (session('flash_message_for_add_plugin'))
        <div class="connect-flash alert alert-success text-center">
            {{ session('flash_message_for_add_plugin') }}
        </div>
    @endif

    {{-- フラッシュメッセージ表示 --}}
    @if (session('flash_message_for_header'))
        <div class="alert {{ session('flash_message_for_header_class') ?? 'alert-success' }} text-center">
            {{ session('flash_message_for_header') }}
        </div>
    @endif

    {{-- ヘッダーエリア --}}
    @if ($layouts_info[0]['exists'])
        {{-- @if (isset($configs_array['browser_width_header']) && $configs_array['browser_width_header']->value == '100%') --}}
        @if (Configs::getConfigsValue($cc_configs, 'browser_width_header') == '100%')
    <header id="ccHeaderArea" class="ccHeaderArea row p-0 mx-auto">
        @else
    <header id="ccHeaderArea" class="ccHeaderArea row container p-0 mx-auto">
        @endif
        {{-- ヘッダーフレームのループ --}}
        @isset($layouts_info[0]['frames'])
            @foreach($layouts_info[0]['frames'] as $frame)
                @if ($frame->isVisible($page, Auth::user()))
                    @include('core.cms_frame')
                @else
                    {{-- このページのみ表示しない --}}
                @endif
            @endforeach
        @endif
    </header>
    @endif

    {{-- 中央エリア --}}
    @php
        // センターエリア任意クラスを抽出（カンマ設定時はランダムで１つ設定）
        // $center_area_optional_class = null;
        // if(isset($configs_array['center_area_optional_class'])){
        //     $classes = explode(',', $configs_array['center_area_optional_class']->value);
        //     $center_area_optional_class = $classes[array_rand($classes)];
        // }
        $center_area_optional_class = Configs::getConfigsRandValue($cc_configs, 'center_area_optional_class');
    @endphp
        {{-- @if (isset($configs_array['browser_width_center']) && $configs_array['browser_width_center']->value == '100%') --}}
        @if (Configs::getConfigsValue($cc_configs, 'browser_width_center') == '100%')
    <div id="ccCenterArea" class="ccCenterArea row mx-auto p-0 d-flex align-items-start {{ $center_area_optional_class }}">
        @else
    <div id="ccCenterArea" class="ccCenterArea row container mx-auto p-0 d-flex align-items-start {{ $center_area_optional_class }}">
        @endif
        {{-- 左エリア --}}
        @if ($layouts_info[1]['exists'])
        <aside id="ccLeftArea" class="ccLeftArea p-0 {{$layouts_info[1]['col']}} order-2 order-lg-1">
            {{-- サービス取得 --}}
            {{-- Todo：実際には、ページ内で使用されているプラグインを動的に定義する必要がある --}}
            @isset($layouts_info[1]['frames'])
                @foreach($layouts_info[1]['frames'] as $frame)
                    @if ($frame->isVisible($page, Auth::user()))
                        @include('core.cms_frame')
                    @else
                        {{-- このページのみ表示しない --}}
                    @endif
                @endforeach
            @endif
        </aside>
        @endif

        {{-- メインエリア --}}
        <main id="ccMainArea" class="ccMainArea row mx-0 p-0 {{$layouts_info[2]['col']}} order-1 order-lg-2" role="main">
            {{-- ページ内のフレームのループ --}}
            @foreach($frames as $frame)
                @if ($frame->area_id == 2)
                    @include('core.cms_frame')
                @endif
            @endforeach
        </main>

        {{-- 右エリア --}}
        @if ($layouts_info[3]['exists'])
        <aside id="ccRightArea" class="ccRightArea p-0 {{$layouts_info[3]['col']}} order-3 order-lg-3">
            {{-- ページ内のフレームのループ --}}
            @isset($layouts_info[3]['frames'])
                @foreach($layouts_info[3]['frames'] as $frame)
                    @if ($frame->isVisible($page, Auth::user()))
                        @include('core.cms_frame')
                    @else
                        {{-- このページのみ表示しない --}}
                    @endif
                @endforeach
            @endif
        </aside>
        @endif

    </div>{{-- /row --}}

    {{-- フッターエリア --}}
    @php
        // フッターエリア任意クラスを抽出（カンマ設定時はランダムで１つ設定）
        // $footer_area_optional_class = null;
        // if(isset($configs_array['footer_area_optional_class'])){
        //     $classes = explode(',', $configs_array['footer_area_optional_class']->value);
        //     $footer_area_optional_class = $classes[array_rand($classes)];
        // }
        $footer_area_optional_class = Configs::getConfigsRandValue($cc_configs, 'footer_area_optional_class');
    @endphp
    @if ($layouts_info[4]['exists'])
        {{-- @if (isset($configs_array['browser_width_footer']) && $configs_array['browser_width_footer']->value == '100%') --}}
        @if (Configs::getConfigsValue($cc_configs, 'browser_width_footer') == '100%')
    <footer id="ccFooterArea" class="ccFooterArea row p-0 mx-auto {{ $footer_area_optional_class }}">
        @else
    <footer id="ccFooterArea" class="ccFooterArea row container p-0 mx-auto {{ $footer_area_optional_class }}">
        @endif
        {{-- フッターフレームのループ --}}
        @isset($layouts_info[4]['frames'])
            @foreach($layouts_info[4]['frames'] as $frame)
                @if ($frame->isVisible($page, Auth::user()))
                    @include('core.cms_frame')
                @else
                    {{-- このページのみ表示しない --}}
                @endif
            @endforeach
        @endif
{{--
        <div class="container">
            <div class="card">
                <div class="card-body">
                    <p class="text-center" style="margin: 0;">
                        Powered by Connect-CMS
                    </p>
                </div>
            </div>
        </div>
--}}
    </footer>
    @endif

</div>{{-- /container --}}

@endsection
