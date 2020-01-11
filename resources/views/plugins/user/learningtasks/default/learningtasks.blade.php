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
    <div class="accordion" id="accordionLearningTask{{$frame_id}}">
    @foreach($learningtasks_posts as $post)
        <div class="card">
            <button class="btn btn-link p-0 text-left" type="button" data-toggle="collapse" data-target="#collapseLearningTask{{$post->id}}" aria-expanded="true" aria-controls="collapseLearningTask{{$post->id}}">
                <div class="card-header learningtasks-list-title" id="headingLearningTask{{$post->id}}">
                    {{-- タイトル --}}
                    {!!$post->getNobrPostTitle()!!}

                    {{-- カテゴリ --}}
                    @if($post->category)
                        <span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>
                    @endif

                    @if($post->user_status)
                        <span class="badge badge-pill badge-primary">修了</span>
                    @endif
               </div>
            </button>

            <div id="collapseLearningTask{{$post->id}}" class="collapse" aria-labelledby="headingLearningTask{{$post->id}}" data-parent="#accordionLearningTask{{$frame_id}}">
                <div class="card-body">

                {{-- 記事本文 --}}
                <article class="cc_article">
                    {{-- 記事本文 --}}
                    {!!$post->post_text!!}

                    {{-- 課題ファイル --}}
                    @if ($post->task_files)
                    <p>
                        @foreach($post->task_files as $task_file)
                        <a href="{{url('/')}}/file/{{$task_file->task_file_uploads_id}}" target="_blank" rel="noopener">{{$task_file->client_original_name}}</a>
                        @endforeach
                    </p>
                    @endif


                    {{-- 重要記事 --}}
                    @if($post->important == 1)
                        <span class="badge badge-danger">重要</span>
                    @endif

                    {{-- カテゴリ --}}
                    @if($post->category)
                        <span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>
                    @endif

                    {{-- タグ --}}
                    @isset($post->tags)
                        @foreach($post->tags as $tag)
                            <span class="badge badge-secondary">{{$tag}}</span>
                        @endforeach
                    @endisset
                </article>

                {{-- 修了チェック --}}
                <article class="cc_article">
                    @auth
                        <form action="{{url('/')}}/plugin/learningtasks/changeStatus/{{$page->id}}/{{$frame_id}}/{{$post->id}}" method="post" name="form_status" class="d-inline">
                            {{ csrf_field() }}

    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" name="task_status" value="1" class="custom-control-input" id="task_status" @if(old('task_status')) checked=checked @endif>
{{--
            <input type="checkbox" name="task_status" value="1" class="custom-control-input" id="task_status" @if(old('task_status', $learningtasks_users_status->task_status)) checked=checked @endif>
--}}
            <label class="custom-control-label" for="important">修了したらチェックして「修了」ボタンをクリックしてください。</label>
        </div>
    </div>

                            <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('終了しましたか？');">
                                <i class="fas fa-check"></i> 終了
                            </button>
                        </form>
                    @endauth
                </article>


                {{-- 投稿日時 --}}
                公開日時：{{$post->posted_at->format('Y年n月j日 H時i分')}}

                {{-- 詳細画面 --}}
                <a href="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}"><i class="fas fa-external-link-square-alt"></i></a>

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

    {{-- ページング処理 --}}
    <div class="text-center">
        {{ $learningtasks_posts->links() }}
    </div>
@endif
@endsection
