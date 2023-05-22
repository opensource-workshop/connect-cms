{{--
 * 検索画面：登録者・登録日時後ろテンプレート
 *
 * @author 牧野　可也子 <makino@opensource-workshop.jp>
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

@if ($searchs_results)
<div class="container pt-3">
    @foreach($searchs_results as $searchs_result)
        <div class="row mb-3 search-result-row">
            {{-- カテゴリ --}}
            @if(!empty($searchs_result->category))
            <div class="search-result-category text-nowrap col-md-auto col-auto order-md-1 order-1">
                <span class="badge cc_category_{{$searchs_result->classname}}">{{$searchs_result->category}}</span>
            </div>
            @endif

            {{-- タイトル --}}
            <div class="search-result-title text-truncate col-md col order-md-2 order-2">
            @if($link_pattern[$searchs_result->plugin_name] == 'show_page_frame_post')
                <a href="{{url('/')}}{{$link_base[$searchs_result->plugin_name]}}/{{$searchs_result->page_id}}/{{$searchs_result->frame_id}}/{{$searchs_result->post_id}}#frame-{{$searchs_result->frame_id}}">
            @elseif($link_pattern[$searchs_result->plugin_name] == 'show_page')
                <a href="{{url('/')}}{{$searchs_result->permanent_link}}">
            @else
                {{-- 上記以外は想定していない為、取り敢えず permanent_link をリンク先とする --}}
                <a href="{{url('/')}}{{$searchs_result->permanent_link}}">
            @endif
                @if(!empty(strip_tags($searchs_result->post_title)))
                    {{ strip_tags($searchs_result->post_title) }}
                @else
                    (無題)
                @endif
                </a>
            </div>

            {{-- 登録日 --}}
            @if($searchs_frame->view_posted_at && !empty($searchs_result->posted_at))
            <div class="search-result-postedat text-nowrap col-md-auto col-auto order-md-3 order-4 ml-auto">
                {{(new Carbon($searchs_result->posted_at))->format('Y/m/d')}}
            </div>
            @endif

            {{-- 登録者 --}}
            @if($searchs_frame->view_posted_name && !empty($searchs_result->posted_name))
            <div class="search-result-postedname text-truncate col-md-auto col-auto order-md-3 order-5 @if(!($searchs_frame->view_posted_at && !empty($searchs_result->posted_at))) ml-auto @endif)">
                {{$searchs_result->posted_name}}
            </div>
            @endif

            {{-- 本文 --}}
            @if(!empty(strip_tags($searchs_result->body)))
            <div class="search-result-body text-secondary col-12 order-md-5 order-3">
                {!! mb_strimwidth(strip_tags($searchs_result->body), 0, 160, '…') !!}
            </div>
            @endif

        </div>
    @endforeach
    
    {{-- ページング処理 --}}
    <div class="row">
        <div class="col">
            @include('plugins.common.user_paginate', ['posts' => $searchs_results, 'frame' => $frame, 'appends' => ['search_keyword' => old('search_keyword')], 'aria_label_name' => $searchs_frame->search_name])
        </div>
    </div>
</div>
@endif

@endsection
