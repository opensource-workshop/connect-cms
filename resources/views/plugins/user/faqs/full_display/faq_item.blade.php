{{--
 * FAQの1レコード部（Q&A全表示用）
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category FAQプラグイン
 * @param boolean $show_category カテゴリを表示するか
--}}
<article class="faq-item mb-4 border-bottom pb-3">
    {{-- 質問部分 --}}
    <div class="faq-question mb-3">
        <div class="d-flex align-items-start">
            <span class="mr-2 mt-1"><span class="h5"><span class="badge badge-primary">Q</span></span></span>
            <div class="flex-grow-1">
                <h5 class="mb-2 font-weight-bold">{{$post->getNobrPostTitle()}}</h5>
                
                {{-- カテゴリ --}}
                @if($post->category_view_flag && $show_category)
                    <span class="badge mb-1" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>
                @endif

                {{-- 状態表示 --}}
                @if ($post->status == 2)
                    @can('role_update_or_approval',[[$post, 'faqs', $buckets]])
                        <span class="badge badge-warning mb-1">承認待ち</span>
                    @endcan
                @endif
                @can('posts.update',[[$post, 'faqs', $buckets]])
                    @if ($post->status == 1)
                        <span class="badge badge-warning mb-1">一時保存</span>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    {{-- 回答部分 --}}
    <div class="faq-answer">
        <div class="d-flex align-items-start">
            <span class="mr-2 mt-1"><span class="h5"><span class="badge badge-secondary">A</span></span></span>
            <div class="flex-grow-1">
                {{-- 記事本文 --}}
                <div class="faq-answer-content">
                    {!! $post->post_text !!}
                </div>
            </div>
        </div>

        {{-- フッター情報 --}}
        <footer class="mt-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div class="faq-meta">
                    @if ($faq_frame->display_posted_at_flag)
                        {{-- 投稿日時 --}}
                        <small class="text-muted mr-3">公開日時：{{$post->posted_at->format('Y年n月j日 H時i分')}}</small>
                    @endif

                    @if (FrameConfig::getConfigValueAndOld($frame_configs, FaqFrameConfig::faq_display_created_name) == ShowType::show)
                        {{-- 投稿者 --}}
                        <small class="text-muted mr-3">[{{$post->created_name}}]</small>
                    @endif

                    {{-- 重要記事 --}}
                    @if ($post->important == 1)
                        <span class="badge badge-danger mr-1">重要</span>
                    @endif

                    {{-- タグ --}}
                    @isset($post->tags)
                        @foreach($post->tags as $tag)
                            <span class="badge badge-secondary mr-1">{{$tag}}</span>
                        @endforeach
                    @endisset
                </div>

                {{-- 操作ボタン --}}
                <div class="faq-actions">
                    @if ($post->status == 2)
                        @can('role_update_or_approval',[[$post, 'faqs', $buckets]])
                            <span class="badge badge-warning align-bottom mr-1">承認待ち</span>
                        @endcan
                        @can('posts.approval',[[$post, 'faqs', $buckets]])
                            <form action="{{url('/')}}/plugin/faqs/approval/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" method="post" name="form_approval" class="d-inline">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-primary btn-sm mr-1" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                                    <i class="fas fa-check"></i> <span class="d-none d-sm-inline">承認</span>
                                </button>
                            </form>
                        @endcan
                    @endif
                    @can('posts.update',[[$post, 'faqs', $buckets]])
                        @if ($post->status == 1)
                            <span class="badge badge-warning align-bottom mr-1">一時保存</span>
                        @endif
                        <a class="btn btn-success btn-sm mr-1" href="{{url('/')}}/plugin/faqs/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}">
                            <i class="far fa-edit"></i> <span class="d-none d-sm-inline">編集</span>
                        </a>
                    @endcan

                    {{-- 詳細画面 --}}
                    <a class="btn btn-success btn-sm" href="{{url('/')}}/plugin/faqs/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" title="{{$post->getNobrPostTitle()}}の詳細">
                        詳細 <i class="fas fa-angle-right"></i>
                    </a>
                </div>
            </div>
        </footer>
    </div>
</article>