{{--
 * リンクリスト画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category リンクリストプラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@if (isset($frame) && $frame->bucket_id)
    {{-- バケツあり --}}
@else
    {{-- バケツなし --}}
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用するリンクリストを選択するか、作成してください。</p>
        </div>
    </div>
@endif

{{-- 新規登録 --}}
@can('posts.create',[[null, 'linklists', $buckets]])
    @if (isset($frame) && $frame->bucket_id)
        <div class="row">
            <p class="text-right col-12">
                {{-- 新規登録ボタン --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/linklists/edit/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}'"><i class="far fa-edit"></i> 新規登録</button>
            </p>
        </div>
    @endif
@endcan

{{-- リンク表示 --}}
@if (isset($posts))
    @if (!$plugin_frame->type)
    <dl>
    @elseif ($plugin_frame->type == 1)
    <ul type="disc">
    @elseif ($plugin_frame->type == 2)
    <ul type="circle">
    @elseif ($plugin_frame->type == 3)
    <ul type="square">
    @elseif ($plugin_frame->type == 4)
    <ol type="1">
    @elseif ($plugin_frame->type == 5)
    <ol type="a">
    @elseif ($plugin_frame->type == 6)
    <ol type="A">
    @elseif ($plugin_frame->type == 7)
    <ol type="i">
    @elseif ($plugin_frame->type == 8)
    <ol type="I">
    @endif

    @foreach($posts as $post)
        @if (!$plugin_frame->type)
        <dd>
        @else
        <li>
        @endif

            @can('posts.update',[[null, 'linklists', $buckets]])
                <a href="{{url('/')}}/plugin/linklists/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}"><i class="far fa-edit"></a></i>
            @endcan
            @if (empty($post->url))
                {{$post->title}}
            @else
                @if ($post->target_blank_flag)
                    <a href="{{$post->url}}" target="_blank">{{$post->title}}</a>
                @else
                    <a href="{{$post->url}}">{{$post->title}}</a>
                @endif
            @endif
            @if (!empty($post->description))
                <div class="alert alert-secondary bg-light mt-2" role="alert">
                    {!!nl2br(e($post->description))!!}
                </div>
            @endif

        @if (!$plugin_frame->type)
        </dd>
        @else
        </li>
        @endif
    @endforeach

    @if (!$plugin_frame->type)
    </dl>
    @elseif ($plugin_frame->type == 1 || $plugin_frame->type == 2 || $plugin_frame->type == 3)
    </ul>
    @elseif ($plugin_frame->type == 4 || $plugin_frame->type == 5 || $plugin_frame->type == 6 || $plugin_frame->type == 7 || $plugin_frame->type == 8)
    </ol>
    @endif
@endif

@endsection
