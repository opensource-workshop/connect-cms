{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}

{{-- 機能選択タブ --}}
<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.contents.contents_frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

<form action="/redirect/plugin/contents/change/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}
    <table class="table table-hover" style="margin-bottom: 0;">
    <thead>
        <tr>
            <th></th>
            {{-- <th>選択</th> --}}
            <th>
                <a href="{{url('/')}}/plugin/contents/datalist/{{$page->id}}/{{$frame_id}}?sort=contents_updated_at|{{$order_link["contents_updated_at"][0]}}">更新日</a>
                @if ($request_order_str == "contents_updated_at|asc")
                    <i class="fas fa-sort-numeric-down"></i>
                @elseif ($request_order_str == "contents_updated_at|desc")
                    <i class="fas fa-sort-numeric-down-alt"></i>
                @endif
            </th>
            <th>
                <a href="{{url('/')}}/plugin/contents/datalist/{{$page->id}}/{{$frame_id}}?sort=page_name|{{$order_link["page_name"][0]}}">使用ページ</a>
                @if ($request_order_str == "page_name|asc")
                    <i class="fas fa-sort-alpha-down"></i>
                @elseif ($request_order_str == "page_name|desc")
                    <i class="fas fa-sort-alpha-down-alt"></i>
                @endif
            </th>
            <th>
                <a href="{{url('/')}}/plugin/contents/datalist/{{$page->id}}/{{$frame_id}}?sort=bucket_name|{{$order_link["bucket_name"][0]}}">データ名</a>
                @if ($request_order_str == "bucket_name|asc")
                    <i class="fas fa-sort-alpha-down"></i>
                @elseif ($request_order_str == "bucket_name|desc")
                    <i class="fas fa-sort-alpha-down-alt"></i>
                @endif
            </th>
            <th>
                <a href="{{url('/')}}/plugin/contents/datalist/{{$page->id}}/{{$frame_id}}?sort=frame_title|{{$order_link["frame_title"][0]}}">フレームタイトル</a>
                @if ($request_order_str == "frame_title|asc")
                    <i class="fas fa-sort-alpha-down"></i>
                @elseif ($request_order_str == "frame_title|desc")
                    <i class="fas fa-sort-alpha-down-alt"></i>
                @endif
            </th>
            <th>
                <a href="{{url('/')}}/plugin/contents/datalist/{{$page->id}}/{{$frame_id}}?sort=content_text|{{$order_link["content_text"][0]}}">内容</a>
                @if ($request_order_str == "content_text|asc")
                    <i class="fas fa-sort-alpha-down"></i>
                @elseif ($request_order_str == "content_text|desc")
                    <i class="fas fa-sort-alpha-down-alt"></i>
                @endif
            </th>
        </tr>
    </thead>
    <tbody>
    @foreach($buckets as $bucket)
        <tr @if ($bucket->frames_id == $frame_id) class="active"@endif>
            <td><input type="radio" value="{{$bucket->id}}" name="select_bucket"@if ($bucket->frames_id == $frame_id) checked @endif></input></td>
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
        {{ $buckets->appends(['sort' => $request_order_str])->fragment($frame_id)->links() }}
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-check"></i> 変更確定</button>
        <button type="button" class="btn btn-secondary" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i> キャンセル</button>
    </div>
</form>
