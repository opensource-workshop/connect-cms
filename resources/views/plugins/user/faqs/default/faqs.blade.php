{{--
 * FAQ画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
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

{{-- 新規登録 --}}
@can('posts.create',[[null, 'faqs', $buckets]])
    @if (isset($frame) && $frame->bucket_id)
        <div class="row">
            <p class="text-right col-12">
                {{-- 新規登録ボタン --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/faqs/create/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}'"><i class="far fa-edit"></i> 新規登録</button>
            </p>
        </div>
    @else
        <div class="card border-danger">
            <div class="card-body">
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用するFAQを選択するか、作成してください。</p>
            </div>
        </div>
    @endif
@endcan

{{-- FAQ表示 --}}
@if (isset($faqs_posts))
    <div class="accordion" id="accordionFaq{{$frame_id}}">
    @foreach($faqs_posts as $post)
        <div class="card">
            <button class="btn btn-link p-0 text-left" type="button" data-toggle="collapse" data-target="#collapseFaq{{$post->id}}" aria-expanded="true" aria-controls="collapseFaq{{$post->id}}">
                <div class="faq-list-title" id="headingFaq{{$post->id}}">

                    <div class="d-flex flex-row">
                        <div class="pr-2"><span class="h5"><span class="badge badge-primary">Q</span></span></div>
                        <div>
                            {{-- タイトル --}}
                            {!!$post->getNobrPostTitle()!!}

                            {{-- カテゴリ --}}
                            @if($post->category)
                                <span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>
                            @endif
                        </div>
                    </div>

                </div>
            </button>

            <div id="collapseFaq{{$post->id}}" class="collapse" aria-labelledby="headingFaq{{$post->id}}" data-parent="#accordionFaq{{$frame_id}}">
                <div class="card-body">

                {{-- 記事本文 --}}
                <article class="cc_article">

                    <div class="d-flex flex-row">
                        <div class="pr-2"><span class="h5"><span class="badge badge-secondary">A</span></span></div>
                        <div>
                            {{-- 記事本文 --}}
                            {!! $post->post_text !!}

                            {{-- 重要記事 --}}
                            @if($post->important == 1)
                                <span class="badge badge-danger">重要</span>
                            @endif

                            {{-- カテゴリ --}}
                            @if($post->category)
                                <span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>
                            @endif

                            {{-- タグ --}}
                            @isset($post->tags)
                                @foreach($post->tags as $tag)
                                    <span class="badge badge-secondary">{{$tag}}</span>
                                @endforeach
                            @endisset
                        </div>
                    </div>

                </article>

                {{-- 投稿日時 --}}
                公開日時：{{$post->posted_at->format('Y年n月j日 H時i分')}}

                {{-- 詳細画面 --}}
                <a href="{{url('/')}}/plugin/faqs/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}"><i class="fas fa-expand-alt"></i></a>

                {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
                <div class="row">
                    <div class="col-12 text-right mb-1">
                    @if ($post->status == 2)
                        @can('role_update_or_approval',[[$post, 'faqs', $buckets]])
                            <span class="badge badge-warning align-bottom">承認待ち</span>
                        @endcan
                        @can('posts.approval',[[$post, 'faqs', $buckets]])
                            <form action="{{url('/')}}/plugin/faqs/approval/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" method="post" name="form_approval" class="d-inline">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                                    <i class="fas fa-check"></i> <span class="hidden-xs">承認</span>
                                </button>
                            </form>
                        @endcan
                    @endif
                    @can('posts.update',[[$post, 'faqs', $buckets]])
                        @if ($post->status == 1)
                            <span class="badge badge-warning align-bottom">一時保存</span>
                        @endif
                        <a href="{{url('/')}}/plugin/faqs/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}">
                            <span class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span class="hidden-xs">編集</span></span>
                        </a>
                    @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    </div>

    {{-- ページング処理 --}}
    <div class="text-center">
        {{ $faqs_posts->links() }}
    </div>
@endif
@endsection
