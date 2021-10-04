{{--
 * 掲示板記事　関連記事画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上　雅人 <inoue@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}
{{-- スレッドの投稿一覧 --}}
@if ($thread_root_post)

    {{-- 詳細でのスレッド記事の展開方法の判定 --}}
    @if ($plugin_frame->thread_format == 2)
        {{-- 詳細でのスレッド記事の展開方法：すべて閉じる --}}
        <div class="card mb-3">
            {{-- 詳細でのスレッド記事の展開方法：すべて閉じるの場合は、card のヘッダに根記事のタイトルを表示 --}}
            <div class="card-header">
                {{$thread_root_post->title}}
                @if ($thread_root_post->status == 1) <span class="badge badge-warning align-bottom">一時保存</span>
                @elseif ($thread_root_post->status == 2) <span class="badge badge-warning align-bottom">承認待ち</span>
                @endif
                <span class="float-right">
                    @include('plugins.user.bbses.default.post_created_at_and_name', ['post' => $thread_root_post])
                </span>
            </div>
            {{-- 詳細でのスレッド記事の展開方法：すべて閉じるの場合は、card のボディに根記事を含めた記事のタイトル一覧を表示 --}}
            <div class="card-body">
                {{-- 根記事（スレッドの記事は古い順なので、根記事は最初） --}}
                @include('plugins.user.bbses.default.post_title_div', ['view_post' => $thread_root_post, 'current_post' => $post, 'list_class' => ''])
                {{-- スレッド記事 --}}
                @foreach ($children_posts->where("thread_root_id", $thread_root_post->id) as $children_post)
                    @include('plugins.user.bbses.default.post_title_div', ['view_post' => $children_post, 'current_post' => $post, 'list_class' => ''])
                @endforeach
            </div>
        </div>
    @else
        {{-- 詳細でのスレッド記事の展開方法：すべて展開 or 詳細表示している記事のみ展開 --}}
        <span class="badge badge-primary mb-1">スレッドの記事一覧</span>
        <div class="card mb-3">
            {{-- 詳細でのスレッド記事の展開方法の判定 --}}
            @if ($plugin_frame->thread_format != 0)
                {{-- 詳細でのスレッド記事の展開方法が詳細表示している記事のみ展開の場合 --}}
                <div class="card-header">
                    @include('plugins.user.bbses.default.post_title', ['view_post' => $thread_root_post, 'current_post' => $post, 'list_class' => ''])
                </div>
                <div class="card-body">
                    {{-- 根記事（スレッドの記事は古い順なので、根記事は最初） --}}
                    {{-- 返信の場合は、親のpost を展開、詳細表示の場合は、自分のpost を展開 --}}
                    @if ((isset($reply_flag) && $reply_flag && $thread_root_post->id == $parent_post->id) ||
                         ($thread_root_post->id == $post->id))
                        <div class="card mb-2">
                            <div class="card-header">
                                @include('plugins.user.bbses.default.post_title_div', ['view_post' => $thread_root_post, 'current_post' => $post, 'list_class' => ''])
                            </div>
                            <div class="card-body">
                                {!!$thread_root_post->body!!}
                            </div>
                        </div>
                    @else
                        @include('plugins.user.bbses.default.post_title_div', ['view_post' => $thread_root_post, 'current_post' => $post, 'list_class' => ''])
                    @endif

                    {{-- スレッド記事 --}}
                    @foreach ($children_posts->where("thread_root_id", $thread_root_post->id) as $children_post)

                        {{-- 返信の場合は、親のpost を展開、詳細表示の場合は、自分のpost を展開 --}}
                        @if ((isset($reply_flag) && $reply_flag && $children_post->id == $parent_post->id) ||
                             ($children_post->id == $post->id))
                            <div class="card mb-2">
                                <div class="card-header">
                                    @include('plugins.user.bbses.default.post_title_div', ['view_post' => $children_post, 'current_post' => $post, 'list_class' => ''])
                                </div>
                                <div class="card-body">
                                    {!!$children_post->body!!}
                                </div>
                            </div>
                        @else
                            @include('plugins.user.bbses.default.post_title_div', ['view_post' => $children_post, 'current_post' => $post, 'list_class' => ''])
                        @endif
                    @endforeach
                </div>
            @else
                <div class="card-header">
                    @include('plugins.user.bbses.default.post_title_div', ['view_post' => $thread_root_post, 'current_post' => $post, 'list_class' => ''])
                </div>
                <div class="card-body">
                    {!!$thread_root_post->body!!}

                    @if ($post && $post->id == $thread_root_post->id)
                        {{-- 自記事のため いいねボタン 表示しない --}}
                    @else
                        {{-- いいねボタン --}}
                        @include('plugins.common.like', [
                            'use_like' => $bbs->use_like,
                            'like_button_name' => $bbs->like_button_name,
                            'contents_id' => $thread_root_post->id,
                            'like_id' => $thread_root_post->like_id,
                            'like_count' => $thread_root_post->like_count,
                            'like_users_id' => $thread_root_post->like_users_id,
                        ])
                    @endif

                    @foreach ($children_posts as $children_post)
                        <div class="card mt-3">
                            <div class="card-header">
                                @include('plugins.user.bbses.default.post_title_div', ['view_post' => $children_post, 'current_post' => $post, 'list_class' => ''])
                            </div>
                            <div class="card-body">
                                {!!$children_post->body!!}

                                @if ($post && $post->id == $children_post->id)
                                    {{-- 自記事のため いいねボタン 表示しない --}}
                                @else
                                    {{-- いいねボタン --}}
                                    @include('plugins.common.like', [
                                        'use_like' => $bbs->use_like,
                                        'like_button_name' => $bbs->like_button_name,
                                        'contents_id' => $children_post->id,
                                        'like_id' => $children_post->like_id,
                                        'like_count' => $children_post->like_count,
                                        'like_users_id' => $children_post->like_users_id,
                                    ])
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
@endif
