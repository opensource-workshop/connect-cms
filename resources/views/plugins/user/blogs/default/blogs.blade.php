{{--
 * ブログ画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
{{-- 新規登録 --}}
@can('posts.create',[[null, 'blogs', $buckets]])
    @if (isset($buckets) && isset($frame) && $frame->bucket_id)
        <div class="row">
            <p class="text-left col-6">
                @if (isset($blog_frame->rss) && $blog_frame->rss == 1)
                <a href="{{url('/')}}/redirect/plugin/blogs/rss/{{$page->id}}/{{$frame_id}}/"><span class="badge badge-info">RSS2.0</span></a>
                @endif
            </p>
            <p class="text-right col-6">
                {{-- 新規登録ボタン --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/blogs/create/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}'"><i class="far fa-edit"></i> 新規登録</button>
            </p>
        </div>
    @else
        <div class="card border-danger">
            <div class="card-body">
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用するブログを選択するか、作成してください。</p>
            </div>
        </div>
    @endif
@endcan

{{-- ブログ表示 --}}
@if (isset($blogs_posts))
    @foreach($blogs_posts as $post)

        {{-- タイトル --}}
        <h2><a href="{{url('/')}}/plugin/blogs/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}">{{$post->post_title}}</a></h2>

        {{-- 投稿日時 --}}
        <b>{{$post->posted_at->format('Y年n月j日 H時i分')}}</b>

        {{-- カテゴリ --}}
        @if($post->category)<span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>@endif
        {{-- 重要記事設定マーク ※ログイン時のみ表示 --}}
        @if($post->important == 1 && Auth::user() && Auth::user()->can('posts.update',[[$post, 'blogs', 'preview_off']]))
            <span class="badge badge-pill badge-danger">重要記事に設定</span>
        @endif

        @if ($loop->last)
        <article>
        @else
        <article class="cc_article">
        @endif
            <div class="clearfix">

                {{-- 記事本文 --}}
                {!! $post->post_text !!}

                {{-- 続きを読む --}}
                @if ($post->read_more_flag)
                    {{-- 続きを読む & タグありなら、続きを読むとタグの間に余白追加 --}}
                    <div id="post_text2_button_{{$frame->id}}_{{$post->id}}" @isset($post->tags) class="mb-2" @endisset>
                        <button type="button" class="btn btn-light btn-sm border" onclick="$('#post_text2_{{$frame->id}}_{{$post->id}}').show(); $('#post_text2_button_{{$frame->id}}_{{$post->id}}').hide();">
                            <i class="fas fa-angle-down"></i> {{$post->read_more_button}}
                        </button>
                    </div>
                    <div id="post_text2_{{$frame->id}}_{{$post->id}}" style="display: none;" @isset($post->tags) class="mb-2" @endisset>
                        {!! $post->post_text2 !!}
                        <button type="button" class="btn btn-light btn-sm border" onclick="$('#post_text2_button_{{$frame->id}}_{{$post->id}}').show(); $('#post_text2_{{$frame->id}}_{{$post->id}}').hide();">
                            <i class="fas fa-angle-up"></i> {{$post->close_more_button}}
                        </button>
                    </div>
                @endif

                {{-- タグ --}}
                @isset($post->tags)
                    @foreach($post->tags as $tag)
                        <span class="badge badge-secondary">{{$tag}}</span>
                    @endforeach
                @endisset

            </div>
            {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
            <div class="row">
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
            </div>
        </article>
    @endforeach

    {{-- ページング処理 --}}
    <div class="text-center">
        {{ $blogs_posts->links() }}
    </div>
@endif
@endsection
