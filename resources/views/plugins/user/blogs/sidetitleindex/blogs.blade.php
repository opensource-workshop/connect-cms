{{--
 * ブログ画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
 --}}


{{-- ブログ表示 --}}
@if (isset($blogs_posts))
    <div class="sidetitleindex">
    @foreach($blogs_posts as $post)
        @if ($loop->index == 3)
          @break
        @endif

        <div>
        {{-- 投稿日時 --}}
        <span class="date">{{$post->posted_at->format('Y年n月j日')}}</span>
        {{-- タイトル --}}
        <a href="{{url('/')}}/plugin/blogs/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}"><span class="title">{{$post->post_title}}</span></a>
        {{-- カテゴリ --}}
        @if($post->category)<span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>@endif
        </div>
    @endforeach
    </div>

    {{-- ページング処理 --}}
    {{--
    <div class="text-center">
        {{ $blogs_posts->links() }}
    </div>
     --}}
@endif

