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
        <div class="container">
            <div class="panel panel-default">
                <div class="panel-body">
                    <p class="text-center" style="margin: 0;">
                        ヘッダー
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">

{{--
        <div class="col-sm-12">
            <div class="row container-fluid">
--}}
                {{-- プラグイン追加フォーム --}}
{{--
                @include('core.cms_add_plugin')
            </div>
        </div>
--}}

        {{-- メインエリア --}}
        <div class="{{$layouts_info[2]['col']}}">
            <div class="row">

                {{-- ページ内のフレームのループ --}}
                @foreach($frames as $frame)
                    @include('core.cms_frame')
                @endforeach
            </div>
        </div>

        {{-- 左エリア --}}
        @if ($layouts_info[1]['exists'])
        <div class="{{$layouts_info[1]['col']}}">
            <div class="row container-fluid">
                {{-- サービス取得 --}}
                {{-- Todo：実際には、ページ内で使用されているプラグインを動的に定義する必要がある --}}
                @inject('menu', 'App\Plugins\User\Menu\Menu')
                {!! $menu->viewInit() !!}
            </div>
        </div>
        @endif

        {{-- 右エリア --}}
        @if ($layouts_info[3]['exists'])
        <div class="col-sm-3">
            <div class="row container-fluid">

            <div class="panel panel-default">
                <div class="panel-body">
                    <p class="text-center" style="margin: 0;">
                        右
                    </p>
                </div>
            </div>

            </div>
        </div>
        @endif

    </div>{{-- /row --}}

    {{-- フッターエリア --}}
    @if ($layouts_info[4]['exists'])
    <div class="row">
        <div class="container">
            <div class="panel panel-default">
                <div class="panel-body">
                    <p class="text-center" style="margin: 0;">
                        Powered by Connect-CMS
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>{{-- /container --}}

@endsection
