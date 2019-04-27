{{--
 * CMSメイン画面
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $current_page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 --}}
{{-- 大元のレイアウトの継承とページコンテンツは大元のレイアウトに埋め込むために @section で定義する --}}
@extends('layouts.app')
@section('content')
<div class="container">


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
    <div class="row">
        {{-- ヘッダーフレームのループ --}}
        @isset($layouts_info[0]['frames'])
            @foreach($layouts_info[0]['frames'] as $frame)
                @include('core.cms_frame')
            @endforeach
        @endif
    </div>
    @endif

    <div class="row">
        {{-- メインエリア --}}
        <div class="{{$layouts_info[2]['col']}}" style="padding-left: 0; padding-right: 0;">
            {{-- ページ内のフレームのループ --}}
            @foreach($frames as $frame)
                @if ($frame->area_id == 2)
                    @include('core.cms_frame')
                @endif
            @endforeach
        </div>

        {{-- 左エリア --}}
        @if ($layouts_info[1]['exists'])
        <div class="{{$layouts_info[1]['col']}}" style="padding-left: 0; padding-right: 0;">
            {{-- サービス取得 --}}
            {{-- Todo：実際には、ページ内で使用されているプラグインを動的に定義する必要がある --}}
            @isset($layouts_info[1]['frames'])
                @foreach($layouts_info[1]['frames'] as $frame)
                    @include('core.cms_frame')
                @endforeach
            @endif
        </div>
        @endif

        {{-- 右エリア --}}
        @if ($layouts_info[3]['exists'])
        <div class="{{$layouts_info[3]['col']}}" style="padding-left: 0; padding-right: 0;">
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
    <div class="row">
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
