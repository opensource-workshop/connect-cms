{{--
 * ブログ記事詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
{{-- 投稿日時 --}}
<p>
{{$post->posted_at->format('Y/m/d')}}
</p>

{{-- 重要 --}}
@if($post->important == 1)<span class="badge badge-danger">重要</span>@endif
{{-- カテゴリ --}}
@if($post->category)<span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>@endif
{{-- タイトル --}}
<h2>{{$post->post_title}}</h2>

<article>

    {{-- 記事本文 --}}
    {!! $post->post_text !!}

    {{-- タグ --}}
    @isset($post_tags)
        @foreach($post_tags as $tags)
            <span class="badge badge-secondary">{{$tags->tags}}</span>
        @endforeach
    @endisset

    {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
    <div class="row">
        <div class="col-12 text-right mb-1">
        @if ($post->status == 2)
            @can('preview',[[null, 'blogs', 'preview_off']])
                <span class="badge badge-warning align-bottom">承認待ち</span>
            @endcan
            @can('posts.approval',[[$post, 'blogs', 'preview_off']])
                <form action="{{url('/')}}/plugin/blogs/approval/{{$page->id}}/{{$frame_id}}/{{$post->id}}" method="post" name="form_approval" class="d-inline">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                        <i class="fas fa-check"></i> <span class="hidden-xs">承認</span>
                    </button>
                </form>
            @endcan
        @endif
        @can('posts.update',[[$post, 'blogs', 'preview_off']])
            @if ($post->status == 1)
                @can('preview',[[$post, 'blogs', 'preview_off']])
                    <span class="badge badge-warning align-bottom">一時保存</span>
                @endcan
            @endif
            <a href="{{url('/')}}/plugin/blogs/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}">
                <span class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span class="hidden-xs">編集</span></span>
            </a>
        @endcan
        </div>
    </div>
</article>

{{-- 一覧へ戻る --}}
<div class="row">
    <div class="col-12 text-center mt-3">
        @if (isset($before_post))
        <a href="{{url('/')}}/plugin/blogs/show/{{$page->id}}/{{$frame_id}}/{{$before_post->contents_id}}" class="mr-1">
            <span class="btn btn-info"><i class="fas fa-chevron-left"></i> <span class="hidden-xs">前へ</span></span>
        </a>
        @endif
        <a href="{{url('/')}}{{$page->getLinkUrl()}}">
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">一覧へ</span></span>
        </a>
        @if (isset($after_post))
        <a href="{{url('/')}}/plugin/blogs/show/{{$page->id}}/{{$frame_id}}/{{$after_post->contents_id}}" class="mr-1">
            <span class="btn btn-info"><i class="fas fa-chevron-right"></i> <span class="hidden-xs">次へ</span></span>
        </a>
        @endif
    </div>
</div>
@endsection
