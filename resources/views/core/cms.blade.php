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
