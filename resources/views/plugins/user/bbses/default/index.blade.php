{{--
 * 掲示板画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@if (isset($frame) && $frame->bucket_id)
    {{-- バケツあり --}}
@else
    {{-- バケツなし --}}
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用する掲示板を選択するか、作成してください。</p>
        </div>
    </div>
@endif

{{-- 新規登録 --}}
@can('posts.create',[[null, 'bbses', $buckets]])
    @if (isset($frame) && $frame->bucket_id)
        <div class="row">
            <p class="text-right col-12">
                {{-- 新規登録ボタン --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/bbses/edit/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}'"><i class="far fa-edit"></i> 新規登録</button>
            </p>
        </div>
    @endif
@endcan

{{-- リンク表示 --}}
@if (isset($posts))
    @foreach($posts as $post)
        <div class="card mb-3">
            <div class="card-header"><a href="{{url('/')}}/plugin/bbses/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">{{$post->title}}</a>@if ($post->status == 1) <span class="badge badge-warning align-bottom">一時保存</span>@elseif ($post->status == 2) <span class="badge badge-warning align-bottom">承認待ち</span>@endif<span class="float-right">{{$post->updated_at->format('Y-m-d')}} [{{$post->created_name}}]</span></div>
            <div class="card-body">
                {!!$post->body!!}
                @if ($children_posts->where("thread_root_id", $post->id)->isNotEmpty())
                    @foreach ($children_posts->where("thread_root_id", $post->id) as $children_post)
                        <div class="card mt-3">
                            <div class="card-header"><a href="{{url('/')}}/plugin/bbses/show/{{$page->id}}/{{$frame_id}}/{{$children_post->id}}#frame-{{$frame_id}}">{{$children_post->title}}</a>@if ($children_post->status == 1) <span class="badge badge-warning align-bottom">一時保存</span>@elseif ($children_post->status == 2) <span class="badge badge-warning align-bottom">承認待ち</span>@endif<span class="float-right">{{$children_post->updated_at->format('Y-m-d')}} [{{$children_post->created_name}}]</span></div>
                            <div class="card-body">
                                {!!$children_post->body!!}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach
@endif

@endsection
