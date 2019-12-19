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
<div class="modal fade" id="modalDetails" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
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
</script>

{{-- *********************************************************** --}}

    {{-- ヘッダーエリア --}}
    @if ($layouts_info[0]['exists'])
        @if (isset($configs_array['browser_width_header']) && $configs_array['browser_width_header']->value == '100%')
    <div id="ccHeaderArea" class="ccHeaderArea row p-0 mx-auto">
        @else
    <div id="ccHeaderArea" class="ccHeaderArea row container p-0 mx-auto">
        @endif
        {{-- ヘッダーフレームのループ --}}
        @isset($layouts_info[0]['frames'])
            @foreach($layouts_info[0]['frames'] as $frame)
                @include('core.cms_frame')
            @endforeach
        @endif
    </div>
    @endif

    {{-- 中央エリア --}}
        @if (isset($configs_array['browser_width_center']) && $configs_array['browser_width_center']->value == '100%')
    <div id="ccCenterArea" class="ccCenterArea row mx-auto p-0 d-flex align-items-start">
        @else
    <div id="ccCenterArea" class="ccCenterArea row container mx-auto p-0 d-flex align-items-start">
        @endif
        {{-- 左エリア --}}
        @if ($layouts_info[1]['exists'])
{{--        <div class="{{$layouts_info[1]['col']}}" style="padding-left: 0; padding-right: 0;"> --}}
        <div id="ccLeftArea" class="ccLeftArea p-0 {{$layouts_info[1]['col']}} order-2 order-lg-1">
            {{-- サービス取得 --}}
            {{-- Todo：実際には、ページ内で使用されているプラグインを動的に定義する必要がある --}}
            @isset($layouts_info[1]['frames'])
                @foreach($layouts_info[1]['frames'] as $frame)
                    @include('core.cms_frame')
                @endforeach
            @endif
        </div>
        @endif

        {{-- メインエリア --}}
{{--        <div class="{{$layouts_info[2]['col']}}" style="padding-left: 0; padding-right: 0;"> --}}
{{--        <div class="row px-lg-0 {{$layouts_info[2]['col']}} order-1 order-lg-2"> --}}
        <div id="ccMainArea" class="ccMainArea row mx-0 p-0 {{$layouts_info[2]['col']}} order-1 order-lg-2">
            {{-- ページ内のフレームのループ --}}
            @foreach($frames as $frame)
                @if ($frame->area_id == 2)
                    @include('core.cms_frame')
                @endif
            @endforeach
        </div>

        {{-- 右エリア --}}
        @if ($layouts_info[3]['exists'])
{{--        <div class="{{$layouts_info[3]['col']}}" style="padding-left: 0; padding-right: 0;"> --}}
        <div id="ccRightArea" class="ccRightArea p-0 {{$layouts_info[3]['col']}} order-3 order-lg-3">
            {{-- ページ内のフレームのループ --}}
            @isset($layouts_info[3]['frames'])
                @foreach($layouts_info[3]['frames'] as $frame)
                    @include('core.cms_frame')
                @endforeach
            @endif
        </div>
        @endif

    </div>{{-- /row --}}

    {{-- フッターエリア --}}
    @if ($layouts_info[4]['exists'])
        @if (isset($configs_array['browser_width_footer']) && $configs_array['browser_width_footer']->value == '100%')
    <div id="ccFooterArea" class="ccFooterArea row p-0 mx-auto">
        @else
    <div id="ccFooterArea" class="ccFooterArea row container p-0 mx-auto">
        @endif
        {{-- ヘッダーフレームのループ --}}
        @isset($layouts_info[4]['frames'])
            @foreach($layouts_info[4]['frames'] as $frame)
                @include('core.cms_frame')
            @endforeach
        @endif
{{--
        <div class="container">
            <div class="panel panel-default">
                <div class="panel-body">
                    <p class="text-center" style="margin: 0;">
                        Powered by Connect-CMS
                    </p>
                </div>
            </div>
        </div>
--}}
    </div>
    @endif

</div>{{-- /container --}}

@endsection
