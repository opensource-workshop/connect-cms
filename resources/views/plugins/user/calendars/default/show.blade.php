{{--
 * カレンダー記事詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category カレンダープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- post が firstOrNew で取得しているので、id があるかで空を判断 --}}
@if (empty($post) || empty($post->id))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        指定された記事は存在しません。
    </div>
@else

    {{-- 編集、返信ボタンのアクション --}}
    <script type="text/javascript">
        function edit_action() {
            form_carendars_posts{{$frame_id}}.action = "{{url('/')}}/plugin/calendars/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}";
            form_carendars_posts{{$frame_id}}.submit();
        }
        function approval_action() {
            if (!confirm('承認します。\nよろしいですか？')) {
                return false;
            }
            form_carendars_posts{{$frame_id}}.action = "{{url('/')}}/redirect/plugin/calendars/approval/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}";
            form_carendars_posts{{$frame_id}}.redirect_path.value = "{{url('/')}}/plugin/calendars/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}";
            form_carendars_posts{{$frame_id}}.submit();
        }
    </script>

    <dl class="row">
        <dt class="col-sm-2 py-1">タイトル</dt>
        <dd class="col-sm-10 py-1">{{$post->title}}</dd>

        <dt class="col-sm-2 py-1">全日予定</dt>
        <dd class="col-sm-10 py-1">{{$post->getAlldayFlagString()}}</dd>

        <dt class="col-sm-2 py-1">開始日時</dt>
        <dd class="col-sm-10 py-1">{{$post->getStartDateTime()}}</dd>

        <dt class="col-sm-2 py-1">終了日時</dt>
        <dd class="col-sm-10 py-1">{{$post->getEndDateTime()}}</dd>

        <dt class="col-sm-2 py-1">本文</dt>
        <dd class="col-sm-10 py-1">{!!$post->body!!}</dd>
    </dl>

    <div class="row">
        <div class="col-12 text-right">
            <form method="POST" class="" name="form_carendars_posts{{$frame_id}}">
                {{csrf_field()}}
                <input type="hidden" name="redirect_path" value="">

                {!!$post->getStatusBadge(true)!!}

                {{-- 承認ボタンの表示：自分が承認できる権限の場合 --}}
                @can('posts.approval',[[$post, $frame->plugin_name, $buckets]])
                    @if ($post->status == 2)
                    <button type="button" class="btn btn-sm btn-primary" onclick="javascript:approval_action();">
                        <i class="far fa-edit"></i> <span class="hidden-xs">承認</span>
                    </button>
                    @endif
                @endcan

                {{-- 自分が更新できる権限の場合 --}}
                @can('posts.update',[[$post, $frame->plugin_name, $buckets]])
                    <button type="button" class="btn btn-sm btn-success" onclick="javascript:edit_action();">
                        <i class="far fa-edit"></i> <span class="hidden-xs">編集</span>
                    </button>
                @endcan
            </form>
        </div>
    </div>

    {{-- 一覧へ戻る --}}
    <nav class="row" aria-label="ページ移動">
        <div class="col-12 text-center my-3">
            <a href="{{url('/')}}{{$page->getLinkUrl()}}#frame-{{$frame->id}}">
                <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">{{__('messages.to_list')}}</span></span>
            </a>
        </div>
    </nav>

{{-- / post がある想定の処理 --}}
@endif

@endsection
