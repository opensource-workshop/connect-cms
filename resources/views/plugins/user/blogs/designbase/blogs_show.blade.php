{{--
 * ブログ記事詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<article>

    <header>
        {{-- 投稿日時 --}}
        <p>
        {{$post->posted_at->format('Y/m/d')}}
        </p>

        {{-- カテゴリ --}}
        @if($post->category)<span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>@endif
        {{-- タイトル --}}
        <h2>
            {{$post->post_title}}
            {{-- 重要記事設定マーク ※ログイン時のみ表示 --}}
            @if($post->important == 1 && Auth::user() && Auth::user()->can('posts.update',[[$post, 'blogs', 'preview_off']]))
                <small><span class="badge badge-pill badge-danger">重要記事に設定</span></small>
            @endif
        </h2>
    </header>

    {{-- 記事本文 --}}
    {!! $post->post_text !!}

    {{-- 続きを読む --}}
    @if ($post->read_more_flag)
        {{-- 続きを読む & タグありなら、続きを読むとタグの間に余白追加 --}}
        <div id="post_text2_button_{{$frame->id}}_{{$post->id}}" @isset($post_tags) class="mb-2" @endisset>
            <button type="button" class="btn btn-light btn-sm border" onclick="$('#post_text2_{{$frame->id}}_{{$post->id}}').show(); $('#post_text2_button_{{$frame->id}}_{{$post->id}}').hide();">
                <i class="fas fa-angle-down"></i> {{$post->read_more_button}}
            </button>
        </div>
        <div id="post_text2_{{$frame->id}}_{{$post->id}}" style="display: none;" @isset($post_tags) class="mb-2" @endisset>
            {!! $post->post_text2 !!}
            <button type="button" class="btn btn-light btn-sm border" onclick="$('#post_text2_button_{{$frame->id}}_{{$post->id}}').show(); $('#post_text2_{{$frame->id}}_{{$post->id}}').hide();">
                <i class="fas fa-angle-up"></i> {{$post->close_more_button}}
            </button>
        </div>
    @endif

    {{-- タグ --}}
    @isset($post_tags)
        @foreach($post_tags as $tags)
            <span class="badge badge-secondary">{{$tags->tags}}</span>
        @endforeach
    @endisset

    {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
    <footer class="row">
        <div class="col-12 text-right mb-1">
        @if ($post->status == 2)
            @can('role_update_or_approval',[[$post, $frame->plugin_name, $buckets]])
                <span class="badge badge-warning align-bottom">承認待ち</span>
            @endcan
            @can('posts.approval',[[$post, $frame->plugin_name, $buckets]])
                <form action="{{url('/')}}/plugin/blogs/approval/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" method="post" name="form_approval" class="d-inline">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                        <i class="fas fa-check"></i> <span class="hidden-xs">承認</span>
                    </button>
                </form>
            @endcan
        @endif
        @can('posts.update',[[$post, $frame->plugin_name, $buckets]])
            @if ($post->status == 1)
                <span class="badge badge-warning align-bottom">一時保存</span>
            @endif
            <a href="{{url('/')}}/plugin/blogs/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}">
                <span class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span class="hidden-xs">編集</span></span>
            </a>
        @endcan
        </div>
    </footer>
</article>

{{-- 一覧へ戻る --}}
<nav class="row">
    <div class="col-12 text-center mt-3">
        @if (isset($before_post))
        <a href="{{url('/')}}/plugin/blogs/show/{{$page->id}}/{{$frame_id}}/{{$before_post->id}}#frame-{{$frame->id}}" class="mr-1">
            <span class="btn btn-info"><i class="fas fa-chevron-left"></i> <span class="hidden-xs">{{__('messages.previous')}}</span></span>
        </a>
        @endif
        <a href="{{url('/')}}{{$page->getLinkUrl()}}#frame-{{$frame->id}}">
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">{{__('messages.to_list')}}</span></span>
        </a>
        @if (isset($after_post))
        <a href="{{url('/')}}/plugin/blogs/show/{{$page->id}}/{{$frame_id}}/{{$after_post->id}}#frame-{{$frame->id}}" class="mr-1">
            <span class="btn btn-info"><i class="fas fa-chevron-right"></i> <span class="hidden-xs">{{__('messages.next')}}</span></span>
        </a>
        @endif
    </div>
</nav>
@endsection
