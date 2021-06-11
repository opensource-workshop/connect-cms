{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.contents.contents_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
<form action="{{url('/')}}/redirect/plugin/contents/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}
    <table class="table table-hover table-responsive">
    <thead>
        <tr>
            <th nowrap>選択</th>
            <th nowrap>
                <a href="{{url('/')}}/plugin/contents/listBuckets/{{$page->id}}/{{$frame_id}}?sort=contents_updated_at|{{$order_link["contents_updated_at"][0]}}">更新日</a>
                @if ($request_order_str == "contents_updated_at|asc")
                    <i class="fas fa-sort-numeric-down"></i>
                @elseif ($request_order_str == "contents_updated_at|desc")
                    <i class="fas fa-sort-numeric-down-alt"></i>
                @endif
            </th>
            <th nowrap>
                <a href="{{url('/')}}/plugin/contents/listBuckets/{{$page->id}}/{{$frame_id}}?sort=page_name|{{$order_link["page_name"][0]}}">使用ページ</a>
                @if ($request_order_str == "page_name|asc")
                    <i class="fas fa-sort-alpha-down"></i>
                @elseif ($request_order_str == "page_name|desc")
                    <i class="fas fa-sort-alpha-down-alt"></i>
                @endif
            </th>
            <th nowrap>
                <a href="{{url('/')}}/plugin/contents/listBuckets/{{$page->id}}/{{$frame_id}}?sort=bucket_name|{{$order_link["bucket_name"][0]}}">データ名</a>
                @if ($request_order_str == "bucket_name|asc")
                    <i class="fas fa-sort-alpha-down"></i>
                @elseif ($request_order_str == "bucket_name|desc")
                    <i class="fas fa-sort-alpha-down-alt"></i>
                @endif
            </th>
            <th nowrap>
                <a href="{{url('/')}}/plugin/contents/listBuckets/{{$page->id}}/{{$frame_id}}?sort=frame_title|{{$order_link["frame_title"][0]}}">フレームタイトル</a>
                @if ($request_order_str == "frame_title|asc")
                    <i class="fas fa-sort-alpha-down"></i>
                @elseif ($request_order_str == "frame_title|desc")
                    <i class="fas fa-sort-alpha-down-alt"></i>
                @endif
            </th>
            <th nowrap>
                <a href="{{url('/')}}/plugin/contents/listBuckets/{{$page->id}}/{{$frame_id}}?sort=content_text|{{$order_link["content_text"][0]}}">内容</a>
                @if ($request_order_str == "content_text|asc")
                    <i class="fas fa-sort-alpha-down"></i>
                @elseif ($request_order_str == "content_text|desc")
                    <i class="fas fa-sort-alpha-down-alt"></i>
                @endif
            </th>
        </tr>
    </thead>
    <tbody>
    @foreach($buckets_list as $bucket)
        <tr @if ($bucket->frames_id == $frame_id) class="cc-active-tr"@endif>
            <td>
                <input type="radio" value="{{$bucket->id}}" name="select_bucket"@if ($bucket->frames_id == $frame_id) checked @endif>
            </td>
            <td>{{$bucket->contents_updated_at}}</td>
            <td>{{$bucket->page_name}}</td>
            <td>{{$bucket->bucket_name}}</td>
            <td>{{$bucket->frame_title}}</td>
            <td>{{str_limit(strip_tags($bucket->content_text),36,'...')}}</td>
        </tr>
    @endforeach
    </tbody>
    </table>

    <div class="m-2">
        {{ $buckets_list->appends(['sort' => $request_order_str])->fragment($frame_id)->links() }}
    </div>

    <div class="text-center">
        <button type="button" class="btn btn-secondary mr-2" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更確定</button>
    </div>
</form>
@endsection
