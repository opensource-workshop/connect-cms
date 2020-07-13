{{--
 * 検索画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 検索プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if(isset($searchs_frame))
    @include('plugins.user.searchs.default.searchs_form')
@else
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        検索設定を作成してください。
    </div>
@endif

@if ($searchs_results)
<div class="mt-3">
    <dl>
    @foreach($searchs_results as $searchs_result)
        @if ($searchs_frame->view_posted_at)
        <dt>
            @if ($searchs_frame->view_posted_at)
                {{(new Carbon($searchs_result->posted_at))->format('Y/m/d')}}
                @if($searchs_result->category)
                    <span class="badge cc_category_{{$searchs_result->classname}}">{{$searchs_result->category}}</span>
                @endif
            @endif
        </dt>
        @endif
        <dd>
            @if ($link_pattern[$searchs_result->plugin_name] == 'show_page_frame_post')
            <a href="{{url('/')}}{{$link_base[$searchs_result->plugin_name]}}/{{$searchs_result->page_id}}/{{$searchs_result->frame_id}}/{{$searchs_result->post_id}}#frame-{{$searchs_result->frame_id}}">
                {{$searchs_result->post_title}}
            </a>
            @elseif ($link_pattern[$searchs_result->plugin_name] == 'show_page')
            <a href="{{url('/')}}{{$searchs_result->permanent_link}}">
                {{$searchs_result->post_title}}
            </a>
            @endif
            @if ($searchs_frame->view_posted_name)
                - {{$searchs_result->posted_name}}
            @endif
        </dd>
    @endforeach
    </dl>

    {{-- ページング処理 --}}
    <div class="text-center">
        {{ $searchs_results->appends(['search_keyword' => old('search_keyword')])->links() }}
    </div>
</div>
@endif
@endsection
