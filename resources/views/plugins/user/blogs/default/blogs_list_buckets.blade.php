{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.blogs.blogs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
<form action="{{url('/')}}/plugin/blogs/changeBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group">
        <table class="table table-hover {{$frame->getSettingTableClass()}}">
        <thead>
            <tr>
                <th></th>
                <th>ブログ名</th>
                <th>作成日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($blogs as $blog)
            <tr @if ($blog_frame->bucket_id == $blog->bucket_id) class="cc-active-tr"@endif>
                <td>
                    <input type="radio" value="{{$blog->bucket_id}}" name="select_bucket"@if ($blog_frame->bucket_id == $blog->bucket_id) checked @endif>
                    <span class="{{$frame->getSettingCaptionClass()}}">{{$blog->blog_name}}</span>
                </td>
                <td class="{{$frame->getNarrowDisplayNone()}}">{{$blog->blog_name}}</td>
                <td>{{$blog->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <div class="form-group text-center">
        {{ $blogs->links() }}
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示ブログ変更</button>
    </div>
</form>
@endsection
