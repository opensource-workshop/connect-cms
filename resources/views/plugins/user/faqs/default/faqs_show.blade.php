{{--
 * FAQ記事詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category FAQプラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<article>

    {{-- タイトル --}}
    <header class="d-flex flex-row">
        <div class="pr-2"><span class="h2"><span class="badge badge-primary">Q</span></span></div>
        <h2>{{$post->post_title}}</h2>
    </header>

    {{-- 記事本文 --}}
    <div class="d-flex flex-row">
        <div class="pr-2"><span class="h2"><span class="badge badge-secondary">A</span></span></div>
        <div>
            {{-- 記事本文 --}}
            {!! $post->post_text !!}
        </div>
    </div>

    <footer>
        <div class="pt-2">
            {{-- 投稿日時 --}}
            {{$post->posted_at->format('Y年n月j日 H時i分')}}

            {{-- 重要記事 --}}
            @if($post->important == 1)<span class="badge badge-danger">重要</span>@endif

            {{-- カテゴリ --}}
            @if($post->category)<span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>@endif

            {{-- タグ --}}
            @isset($post_tags)
                @foreach($post_tags as $tags)
                    <span class="badge badge-secondary">{{$tags->tags}}</span>
                @endforeach
            @endisset
        </div>

        {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
        <div class="row pt-1">
            <div class="col text-right mb-1">
            @if ($post->status == 2)
                @can('preview',[[null, 'faqs', 'preview_off']])
                    <span class="badge badge-warning align-bottom">承認待ち</span>
                @endcan
                @can('posts.approval',[[$post, 'faqs', 'preview_off']])
                    <form action="{{url('/')}}/plugin/faqs/approval/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" method="post" name="form_approval" class="d-inline">
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                            <i class="fas fa-check"></i> <span class="hidden-xs">承認</span>
                        </button>
                    </form>
                @endcan
            @endif
            @can('posts.update',[[$post, 'faqs', 'preview_off']])
                @if ($post->status == 1)
                    @can('preview',[[$post, 'faqs', 'preview_off']])
                        <span class="badge badge-warning align-bottom">一時保存</span>
                    @endcan
                @endif
                <a href="{{url('/')}}/plugin/faqs/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}">
                    <span class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span class="hidden-xs">編集</span></span>
                </a>
            @endcan
            </div>
        </div>
    </footer>

</article>

{{-- 一覧へ戻る --}}
<nav class="row" aria-label="{{$faq_frame->faq_name}}のページ移動">
    <div class="col-12 text-center mt-3">
        @if (isset($before_post))
        <a href="{{url('/')}}/plugin/faqs/show/{{$page->id}}/{{$frame_id}}/{{$before_post->id}}#frame-{{$frame->id}}" class="mr-1">
            <span class="btn btn-info"><i class="fas fa-chevron-left"></i> <span class="hidden-xs">前へ</span></span>
        </a>
        @endif
        <a href="{{url('/')}}{{$page->getLinkUrl()}}#frame-{{$frame->id}}">
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">一覧へ</span></span>
        </a>
        @if (isset($after_post))
        <a href="{{url('/')}}/plugin/faqs/show/{{$page->id}}/{{$frame_id}}/{{$after_post->id}}#frame-{{$frame->id}}" class="mr-1">
            <span class="btn btn-info"><i class="fas fa-chevron-right"></i> <span class="hidden-xs">次へ</span></span>
        </a>
        @endif
    </div>
</nav>
@endsection
