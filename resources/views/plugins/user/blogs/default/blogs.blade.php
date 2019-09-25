{{--
 * ブログ画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
 --}}

{{-- 新規登録 --}}
{{-- @if (Auth::check() && (Auth::user()->can('posts.create'))) --}}

@can('posts.create','blogs')
    @if (isset($frame) && $frame->bucket_id)
        <p class="text-right">
            {{-- 新規登録ボタン --}}
            <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/blogs/create/{{$page->id}}/{{$frame_id}}'"><i class="far fa-edit"></i> 新規登録</button>
        </p>
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
        <h2>{{$post->post_title}}</h2>
        {{-- 投稿日時 --}}
        <b>{{$post->posted_at->format('Y年n月j日 H時i分')}}</b>
            @if ($loop->last)
                <article>
            @else
                <article class="cc_article">
            @endif
            {{-- 記事本文 --}}
            {!! $post->post_text !!}

            {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
            @can('posts.update',[[$post, 'blogs']])
                <p class="text-right">
                    <a href="{{url('/')}}/plugin/blogs/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}">
                        <span class="btn btn-primary btn-sm"><i class="far fa-edit"></i> <span class="hidden-xs">編集</span></span>
                    </a>
                </p>
            @endcan
        </article>
    @endforeach

    {{-- ページング処理 --}}
    <div class="text-center">
        {{ $blogs_posts->links() }}
    </div>
@endif

