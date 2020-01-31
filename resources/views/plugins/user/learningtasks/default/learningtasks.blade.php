{{--
 * 課題管理画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- RSS --}}
@if (isset($learningtasks_frame->rss) && $learningtasks_frame->rss == 1)
<div class="row">
    <p class="text-left col-6">
        <a href="{{url('/')}}/redirect/plugin/learningtasks/rss/{{$page->id}}/{{$frame_id}}/"><span class="badge badge-info">RSS2.0</span></a>
    </p>
</div>
@endif

{{-- 新規登録 --}}
@can('posts.create',[[null, 'learningtasks', $buckets]])
    @if (isset($frame) && $frame->bucket_id)
        <div class="row">
            <p class="text-right col-12">
                {{-- 新規登録ボタン --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/learningtasks/create/{{$page->id}}/{{$frame_id}}'"><i class="far fa-edit"></i> 新規登録</button>
            </p>
        </div>
    @else
        <div class="card border-danger">
            <div class="card-body">
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用する課題管理を選択するか、作成してください。</p>
            </div>
        </div>
    @endif
@endcan

{{-- 課題管理表示 --}}
@if (isset($learningtasks_posts))
    @foreach($categories_and_posts as $category_id => $categories_and_post)
    <div class="accordion @if (!$loop->first) mt-3 @endif" id="accordionLearningTask{{$frame_id}}_{{$category_id}}">
        <span class="badge" style="color:{{$categories[$category_id]->category_color}};background-color:{{$categories[$category_id]->category_background_color}};">{{$categories[$category_id]->category}}</span>
    @foreach($categories_and_post as $post)
        <div class="card">
            <button class="btn btn-link p-0 text-left" type="button" data-toggle="collapse" data-target="#collapseLearningTask{{$post->id}}" aria-expanded="true" aria-controls="collapseLearningTask{{$post->id}}">
                <div class="card-header learningtasks-list-title row" id="headingLearningTask{{$post->id}}">
                    @auth
                    <div class="col-sm-1">
                    @if($post->user_task_status)
                        <span class="badge badge-pill badge-primary">修了</span>
                    @endif
                    </div>
                    @endauth

                    @auth
                    <div class="col-sm-11">
                    @else
                    <div class="col-sm-12">
                    @endauth
                    {{-- タイトル --}}
                    {!!$post->getNobrPostTitle()!!}
                    </div>
               </div>
            </button>

            <div id="collapseLearningTask{{$post->id}}" class="collapse" aria-labelledby="headingLearningTask{{$post->id}}" data-parent="#accordionLearningTask{{$frame_id}}_{{$category_id}}">
                <div class="card-body">

                {{-- 記事本文 --}}
                <article class="">
                    {{-- 記事本文 --}}
                    {!!$post->post_text!!}

                    {{-- 課題ファイル --}}
                    @if ($post->task_files)
                        @foreach($post->task_files as $task_file)
                        <p>
                            <a href="{{url('/')}}/file/{{$task_file->task_file_uploads_id}}" target="_blank" rel="noopener">{{$task_file->client_original_name}}</a>
                            <span class="ml-2"><span><i class="fas fa-arrow-circle-down"></i> {{$task_file->download_count}}
                        </p>
                        @endforeach
                    @endif

                    {{-- 重要記事 --}}
                    @if($post->important == 1)
                        <span class="badge badge-danger">重要</span>
                    @endif

                    {{-- タグ --}}
                    @isset($post->tags)
                        @foreach($post->tags as $tag)
                            <span class="badge badge-secondary">{{$tag}}</span>
                        @endforeach
                    @endisset
                </article>

                {{-- 修了チェック --}}
                @auth
                <div class="card p-3 m-3">
                    <form action="{{url('/')}}/plugin/learningtasks/changeStatus/{{$page->id}}/{{$frame_id}}/{{$post->contents_id}}" method="post" name="form_status" class="d-inline">
                        {{ csrf_field() }}
                        @if ($post->user_task_status == 0)
                        <p>修了したら下の「修了」ボタンをクリックしてください。</p>

                        <input type="hidden" name="task_status" value="1">
                        <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('修了しましたか？');">
                            <i class="fas fa-check"></i> 修了
                        </button>
                        @else
                        <p>修了を取り消す場合は下の「修了取り消し」ボタンをクリックしてください。</p>

                        <input type="hidden" name="task_status" value="0">
                        <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('修了を取り消しますか？');">
                            <i class="fas fa-check"></i> 修了取り消し
                        </button>
                        @endif
                    </form>
                </div>
                @endauth

                {{-- 投稿日時 --}}
                公開日時：{{$post->posted_at->format('Y年n月j日 H時i分')}}

                {{-- 詳細画面 --}}
                <a href="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}"><i class="fas fa-expand-alt"></i></a>

                {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
                <div class="row">
                    <div class="col-12 text-right mb-1">
                    @if ($post->status == 2)
                        @can('role_update_or_approval',[[$post, 'learningtasks', $buckets]])
                            <span class="badge badge-warning align-bottom">承認待ち</span>
                        @endcan
                        @can('posts.approval',[[$post, 'learningtasks', $buckets]])
                            <form action="{{url('/')}}/plugin/learningtasks/approval/{{$page->id}}/{{$frame_id}}/{{$post->id}}" method="post" name="form_approval" class="d-inline">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                                    <i class="fas fa-check"></i> <span class="hidden-xs">承認</span>
                                </button>
                            </form>
                        @endcan
                    @endif
                    @can('posts.update',[[$post, 'learningtasks', $buckets]])
                        @if ($post->status == 1)
                            <span class="badge badge-warning align-bottom">一時保存</span>
                        @endif
                        <a href="{{url('/')}}/plugin/learningtasks/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}">
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
    @endforeach
    {{-- ページング処理 --}}
    <div class="text-center">
        {{ $learningtasks_posts->links() }}
    </div>
@endif
@endsection
