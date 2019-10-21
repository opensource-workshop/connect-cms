{{--
 * ブログ画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
 --}}

{{-- 新規登録 --}}
@can('posts.create',[[null, 'blogs', 'preview_off']])
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

        {{-- 投稿日時 --}}
        <b>{{$post->posted_at->format('Y年n月j日')}}</b>
        {{-- タイトル --}}
        <h2>{{$post->post_title}}</h2>
            @if ($loop->last)
                <article>
            @else
                <article class="cc_article">
            @endif
            {{-- 記事本文 --}}
            {!! $post->post_text !!}

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
    @endforeach

    {{-- ページング処理 --}}
    <div class="text-center">
        {{ $blogs_posts->links() }}
    </div>
@endif

