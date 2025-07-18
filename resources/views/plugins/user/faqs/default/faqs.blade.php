{{--
 * FAQ画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
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
    
    {{-- 絞り込み機能 --}}
    @include('plugins.user.faqs.default.faqs_narrowing_down')

    {{-- 条件クリア --}}
    @include('plugins.user.faqs.clear_conditions')


    <div class="accordion" id="accordionFaq{{$frame_id}}">
    @foreach($faqs_posts as $post)
        {{-- FAQの要素呼び出し --}}
        @include('plugins.user.faqs.default.faq', ['post' => $post, 'hide_category' => false])
    @endforeach
    </div>

    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $faqs_posts, 'frame' => $frame, 'aria_label_name' => $faq_frame->faq_name, 'class' => 'mt-3'])

@endif
@endsection
