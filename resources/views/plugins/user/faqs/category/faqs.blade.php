{{--
 * FAQ画面テンプレート（カテゴリー別表示）
 *
 * @author 石垣 佑樹 <ishigaki@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.co.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category FAQプラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- RSS --}}
@if (isset($faq_frame->rss) && $faq_frame->rss == 1)
<div class="row">
    <p class="text-left col-6">
        <a href="{{url('/')}}/redirect/plugin/faqs/rss/{{$page->id}}/{{$frame_id}}/"><span class="badge badge-info">RSS2.0</span></a>
    </p>
</div>
@endif

@if (isset($frame) && $frame->bucket_id)
    {{-- 新規登録 --}}
    @can('posts.create',[[null, 'faqs', $buckets]])
        <div class="row">
            <p class="text-right col-12">
                {{-- 新規登録ボタン --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/faqs/create/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}'"><i class="far fa-edit"></i> 新規登録</button>
            </p>
        </div>
    @endcan
@else
    {{-- 新規登録 --}}
    @can('frames.edit',[[null, null, null, $frame]])
        <div class="card border-danger">
            <div class="card-body">
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用するFAQを選択するか、作成してください。</p>
            </div>
        </div>
    @endcan
@endif

{{-- FAQ表示 --}}
@if (isset($faqs_posts))
    {{-- 検索フォーム --}}
    @include('plugins.user.faqs.search_form')
    
    {{-- 条件クリア --}}
    @include('plugins.user.faqs.clear_conditions')
    
    {{-- 件数表示 --}}
    @include('plugins.user.faqs.count_display')
    
    @php
        $sorted_posts = $faqs_posts;
        if ($faq_frame->sequence_conditions != 3) {
            // カテゴリー順になってなければカテゴリーごとに並び替え
            $sort = [
                ['category_display_sequence', 'asc'],
                ['categories_id', 'asc']
            ];
            if ($faq_frame->sequence_conditions == 0) {
                // 最新順
                $sort[] = ['posted_at', 'desc'];;
            } elseif ($faq_frame->sequence_conditions == 1) {
                // 投稿順
                $sort[] = ['posted_at', 'asc'];
            } elseif ($faq_frame->sequence_conditions == 2) {
                // 指定順
                $sort[] = ['display_sequence', 'asc'];
            }
            $sorted_posts = $faqs_posts->sortBy($sort);
        }

    @endphp
    @foreach($sorted_posts as $post)
        @if ($loop->first || $sorted_posts[$loop->index - 1]->categories_id !== $post->categories_id)
            {{-- カテゴリ名 --}}
            <h2 class="faq-category-title mt-1" id="{{$post->category}}"><span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span></h1>
            <div class="accordion faq-category" id="accordionFaq{{$frame_id}}">
        @endif
        {{-- FAQの要素呼び出し --}}
        @include('plugins.user.faqs.default.faq', ['post' => $post, 'hide_category' => true])
        {{-- カテゴリ毎のdiv閉じ --}}
        @if ($loop->last || $sorted_posts[$loop->index + 1]->categories_id !== $post->categories_id)
            </div>
        @endif
    @endforeach

    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $faqs_posts, 'frame' => $frame, 'aria_label_name' => $faq_frame->faq_name, 'class' => 'mt-3'])

@endif
@endsection
