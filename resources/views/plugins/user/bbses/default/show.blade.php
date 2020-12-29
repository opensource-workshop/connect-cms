{{--
 * 掲示板記事詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<article>
    <header>
        {{-- タイトル --}}
        <h2>{{$post->title}}</h2>

        {{-- 投稿日時 --}}
        <b>{{$post->created_at->format('Y年n月j日 H時i分')}}</b>
    </header>

    {{-- 記事本文 --}}
    {!! $post->body !!}

    {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
    <footer class="row">
        <div class="col-12 text-right mb-1">
        @can('posts.update',[[$post, $frame->plugin_name, $buckets]])
            @if ($post->temporary_flag == 1)
                <span class="badge badge-warning align-bottom">一時保存</span>
            @endif
            <a href="{{url('/')}}/plugin/bbses/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}">
                <span class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span class="hidden-xs">編集</span></span>
            </a>
        @endcan
        </div>
    </footer>
</article>

{{-- 一覧へ戻る --}}
<nav class="row" aria-label="ページ移動">
    <div class="col-12 text-center mt-3">
        <a href="{{url('/')}}{{$page->getLinkUrl()}}#frame-{{$frame->id}}">
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">{{__('messages.to_list')}}</span></span>
        </a>
    </div>
</nav>
@endsection
