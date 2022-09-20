{{--
 * FAQの1レコード部
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 石垣 佑樹 <ishigaki@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category FAQプラグイン
 * @param boolean $hide_category カテゴリを隠すか
--}}
<article class="card">
    <header class="faq-list-title" id="headingFaq{{$post->id}}">

        <div class="d-flex flex-row">
            <button class="btn btn-link p-0 text-left" type="button" data-toggle="collapse" data-target="#collapseFaq{{$post->id}}" aria-expanded="true" aria-controls="collapseFaq{{$post->id}}" id="button_collapse_faq{{$post->id}}">
                {{-- タイトル --}}
                <span class="pr-2"><span class="h5"><span class="badge badge-primary">Q</span></span></span>{{$post->getNobrPostTitle()}}

                {{-- カテゴリ --}}
                @if($post->category_view_flag && !$hide_category)
                    <span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>
                @endif

                @if ($post->status == 2)
                    @can('role_update_or_approval',[[$post, 'faqs', $buckets]])
                        <span class="badge badge-warning">承認待ち</span>
                    @endcan
                @endif
                @can('posts.update',[[$post, 'faqs', $buckets]])
                    @if ($post->status == 1)
                        <span class="badge badge-warning">一時保存</span>
                    @endif
                @endcan
            </button>
        </div>

    </header>

    {{-- 記事本文 --}}
    <div id="collapseFaq{{$post->id}}" class="collapse" aria-labelledby="headingFaq{{$post->id}}" data-parent="#accordionFaq{{$frame_id}}">
        <div class="card-body faq-list-body">

            <div class="d-flex flex-row">
                <div class="pr-2"><span class="h5"><span class="badge badge-secondary">A</span></span></div>
                <div>
                    {{-- 記事本文 --}}
                    {!! $post->post_text !!}
                </div>
            </div>

            <footer>
                <div class="pt-1">
                    @if ($faq_frame->display_posted_at_flag)
                        {{-- 投稿日時 --}}
                        公開日時：{{$post->posted_at->format('Y年n月j日 H時i分')}}
                    @endif

                    @if (FrameConfig::getConfigValueAndOld($frame_configs, FaqFrameConfig::faq_display_created_name) == ShowType::show)
                        {{-- 投稿者 --}}
                        [{{$post->created_name}}]
                    @endif

                    {{-- 重要記事 --}}
                    @if ($post->important == 1)
                        <span class="badge badge-danger">重要</span>
                    @endif

                    {{-- カテゴリ --}}
                    @if ($post->category_view_flag && !$hide_category)
                        <span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>
                    @endif

                    {{-- タグ --}}
                    @isset($post->tags)
                        @foreach($post->tags as $tag)
                            <span class="badge badge-secondary">{{$tag}}</span>
                        @endforeach
                    @endisset
                </div>

                {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
                <div class="row pt-2">
                    <div class="col text-right mb-1">
                        @if ($post->status == 2)
                            @can('role_update_or_approval',[[$post, 'faqs', $buckets]])
                                <span class="badge badge-warning align-bottom">承認待ち</span>
                            @endcan
                            @can('posts.approval',[[$post, 'faqs', $buckets]])
                                <form action="{{url('/')}}/plugin/faqs/approval/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" method="post" name="form_approval" class="d-inline">
                                    {{ csrf_field() }}
                                    <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                                        <i class="fas fa-check"></i> <span class="d-none d-sm-inline">承認</span>
                                    </button>
                                </form>
                            @endcan
                        @endif
                        @can('posts.update',[[$post, 'faqs', $buckets]])
                            @if ($post->status == 1)
                                <span class="badge badge-warning align-bottom">一時保存</span>
                            @endif
                            <a class="btn btn-success btn-sm" href="{{url('/')}}/plugin/faqs/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}">
                                <i class="far fa-edit"></i> <span class="d-none d-sm-inline">編集</span>
                            </a>
                        @endcan

                        {{-- 詳細画面 --}}
                        <a class="btn btn-success btn-sm ml-2" href="{{url('/')}}/plugin/faqs/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" title="{{$post->getNobrPostTitle()}}の詳細">
                            詳細 <i class="fas fa-angle-right"></i>
                        </a>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</article>
