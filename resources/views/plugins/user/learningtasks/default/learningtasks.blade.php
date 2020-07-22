{{--
 * 課題管理画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

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
@if (isset($learningtasks_posts))  {{-- 課題があるか --}}
    @foreach($categories_and_posts as $category_id => $categories_and_post)  {{-- カテゴリのループ --}}
    <div class="accordion @if (!$loop->first) mt-3 @endif" id="accordionLearningTask{{$frame_id}}_{{$category_id}}">
        <span class="badge" style="color:{{$categories[$category_id]->category_color}};background-color:{{$categories[$category_id]->category_background_color}};">{{$categories[$category_id]->category}}</span>

<table class="table table-bordered">
    <thead class="bg-light">
    <tr>
        <th scope="col" class="text-nowrap">科目名</th>
        <th scope="col" class="text-nowrap">レポート</th>
        <th scope="col" class="text-nowrap">試験日時</th>
        <th scope="col" class="text-nowrap">試験評価</th>
{{--
        <th scope="col" class="text-nowrap">単位数</th>
        <th scope="col" class="text-nowrap">免許状の種類</th>
--}}
    </tr>
    </thead>
    <tbody>
        @foreach($categories_and_post as $post)  {{-- 課題のループ --}}

            <tr>
                <th><a href="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}">{!!$post->getNobrPostTitle()!!}</a></th>{{-- タイトル --}}
                <td>
                @if ($loop->index == 0)
                    未提出
                @elseif ($loop->index == 2)
                    <span class="text-danger">評価：D</span>
                @else
                    評価：A
                @endif
                </td>
                <td class="text-nowrap">
                @if ($loop->index == 0)
                @elseif ($loop->index == 2)
                @else
                    2020年7月10日 10:00～11:30<br />2020年7月10日 10:00～11:30
                @endif
                </td>
                <td>A</td>
{{--
                <td>4単位</td>
                <td>小専免<br />中専免<br />高専免</td>
--}}
            </tr>
        @endforeach
    </tbody>
</table>
    </div>
    @endforeach
    {{-- ページング処理 --}}
    <div class="text-center">
        {{ $learningtasks_posts->links() }}
    </div>
@endif

@endsection
