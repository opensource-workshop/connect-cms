{{--
 * 表示画面テンプレート。データのみ。HTMLは解釈する。
 *
 * フレームが作られた直後の状態では、$contents が存在せずにnull の場合があるので、チェックして切り替えている。
 * 
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@if ($contents)
    {!! $contents->content_text !!}
    @can('role_update_or_approval',[[$contents, 'contents', $buckets]])
    <div class="row">
        <div class="col-12 text-right mb-1">
            {{-- 変更画面へのリンク --}}
            @if ($frame->page_id == $page->id)

                @if ($contents->status == 2)
                    @can('role_update_or_approval',[[$contents, 'contents', $buckets]])
                        <span class="badge badge-warning align-bottom">承認待ち</span>
                    @endcan
                    @can('posts.approval',[[$contents, 'contents', $buckets]])
                        <form action="{{url('/')}}/redirect/plugin/contents/approval/{{$page->id}}/{{$frame_id}}/{{$contents->id}}" method="post" name="form_approval" class="d-inline">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                                <i class="fas fa-check"></i> <span class="hidden-xs">承認</span>
                            </button>
                        </form>
                    @endcan
                @endif

                @can('posts.update',[[$contents, 'contents', $buckets]])
                    <a href="{{url('/')}}/plugin/contents/edit/{{$page->id}}/{{$frame_id}}/{{$contents->id}}#{{$frame_id}}">
                        <span class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span class="hidden-xs">編集</span></span>
                    </a>
                @endcan
            @endif
        </div>
    </div>
    @endcan
@else
    @can('posts.update',[[$contents, 'contents', $buckets]])
    <p class="text-right">
        {{-- 追加画面へのリンク --}}
        <a href="{{url('/')}}/plugin/contents/edit/{{$page->id}}/{{$frame_id}}#{{$frame_id}}">
            <span class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span class="hidden-xs">編集</span></span>
        </a>
    </p>
    @endcan
@endif
