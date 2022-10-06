{{--
 * フォトアルバム画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@php
    // ダウンロード処理が有効かの判断をして変数に保持。この後の画面判断で使う。
    if ((Auth::user() &&
         Auth::user()->can('posts.create', [[null, $frame->plugin_name, $buckets]])
    ) || (
        FrameConfig::getConfigValue($frame_configs, PhotoalbumFrameConfig::download)
    )) {
        $download_check = true;
    } else {
        $download_check = false;
    }
@endphp

{{-- ヘッダ --}}
@include('plugins.user.photoalbums.default.index_head')

{{-- フォルダ --}}
@include('plugins.user.photoalbums.default.index_folder')

{{-- 画像・動画 --}}
@include('plugins.user.photoalbums.default.index_image')

{{-- フッタ --}}
@include('plugins.user.photoalbums.default.index_foot')

@endsection
