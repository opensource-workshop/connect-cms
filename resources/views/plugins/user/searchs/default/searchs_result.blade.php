{{--
 * 検索画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp> / 牧野　可也子 <makino@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 検索プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if(isset($searchs_frame))
    @include('plugins.user.searchs.default.searchs_form')
@else
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        検索設定を作成してください。
    </div>
@endif

@if($searchs_results)
<div class="searchs-result-body mt-3">
    @foreach($searchs_results as $searchs_result)
    <dl class="searchs-result-row border-bottom">
        <dt>
            {{-- 登録日 --}}
            @if($searchs_frame->view_posted_at)
                {{(new Carbon($searchs_result->posted_at))->format('Y/m/d')}}
            @endif
            {{-- カテゴリ --}}
            @if($searchs_result->category)
                <span class="badge cc_category_{{$searchs_result->classname}}">{{$searchs_result->category}}</span>
            @endif
        </dt>
        <dd class="searchs-result-posttitle">
            {{-- タイトル --}}
            @if($link_pattern[$searchs_result->plugin_name] == 'show_page_frame_post')
            <a href="{{url('/')}}{{$link_base[$searchs_result->plugin_name]}}/{{$searchs_result->page_id}}/{{$searchs_result->frame_id}}/{{$searchs_result->post_id}}#frame-{{$searchs_result->frame_id}}">
            @elseif($link_pattern[$searchs_result->plugin_name] == 'show_page')
            <a href="{{url('/')}}{{$searchs_result->permanent_link}}">
            @else
            {{-- 上記以外は想定していない為、取り敢えず permanent_link をリンク先とする --}}
            <a href="{{url('/')}}{{$searchs_result->permanent_link}}">
            @endif
            @if(!empty(strip_tags($searchs_result->post_title)))
                {{-- タイトル  本文と同じぐらいで取り敢えずカット。 --}}
                {!! mb_strimwidth(strip_tags($searchs_result->post_title), 0, 160, '…') !!}
            @else
                (無題)
            @endif
            </a>
            {{-- 登録者 --}}
            @if($searchs_frame->view_posted_name)
                - {{$searchs_result->posted_name}}
            @endif
        </dd>
        {{-- 本文 半角160文字（全角80文字）まで 世の検索エンジンがだいたいこれくらい --}}
        @if(!empty(strip_tags($searchs_result->body)))
        <dd class="search-result-postbody text-secondary">
            {!! mb_strimwidth(strip_tags($searchs_result->body), 0, 160, '…') !!}
        </dd>
        @endif
    </dl>
    @endforeach
@php
    $appends['search_keyword'] = old('search_keyword');
    // ページ配下の絞込み
    if (old('narrow_down_page_id')) {
        $appends['narrow_down_page_id'] = old('narrow_down_page_id');
    }

@endphp
    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $searchs_results, 'frame' => $frame, $appends])
</div>
@endif
@endsection
