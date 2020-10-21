{{--
 * ブログ画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
{{-- 新規登録 --}}
@can('posts.create',[[null, $frame->plugin_name, $buckets]])
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
<div>
    <dl>
    @foreach($blogs_posts as $post)
        {{-- 投稿日時 --}}
        <dt>
            {{$post->posted_at->format('Y/m/d')}}
            @if($post->category)<span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>@endif
        </dt>

        <dd>
            {{-- タイトル --}}
            <a href="{{url('/')}}/plugin/blogs/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}"><span class="title">{{$post->post_title}}</span></a>
            {{-- 重要記事設定マーク ※ログイン時のみ表示 --}}
            @if($post->important == 1 && Auth::user() && Auth::user()->can('posts.update',[[$post, 'blogs', 'preview_off']]))
                <span class="badge badge-pill badge-danger">重要記事に設定</span>
            @endif
        </dd>
    @endforeach

    {{-- ページング処理 --}}
    <nav class="text-center">
        {{ $blogs_posts->fragment('frame-' . $frame_id)->links() }}
    </nav>
</div>
@endif
@endsection
