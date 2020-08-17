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
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/linklists/edit/{{$page->id}}/{{$frame_id}}'"><i class="far fa-edit"></i> 新規登録</button>
            </p>
        </div>
    @endif
@endcan

{{-- FAQ表示 --}}
@if (isset($posts))
    <ul>
    @foreach($posts as $post)
        @if (empty($post->url))
        <li>
            @can('posts.update',[[null, 'linklists', $buckets]])
                <span class="badge badge-primary"><i class="far fa-edit"></i></span>
            @endcan
            {{$post->title}}
        </li>
        @else
        <li>
            @can('posts.update',[[null, 'linklists', $buckets]])
                <span class="badge badge-primary"><i class="far fa-edit"></i></span>
            @endcan
            <a href="{{$post->url}}" target="_blank">{{$post->title}}</a>
        </li>
        @endif
    @endforeach
    </ul>
@endif

@endsection
