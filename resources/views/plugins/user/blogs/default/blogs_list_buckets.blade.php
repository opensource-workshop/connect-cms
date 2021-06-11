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

<form action="{{url('/')}}/redirect/plugin/blogs/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/blogs/listBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">

    <div class="form-group">
        <table class="table table-hover {{$frame->getSettingTableClass()}}">
            <thead>
                <tr>
                    <th></th>
                    <th>ブログ名</th>
                    <th>件数</th>
                    <th>詳細</th>
                    <th>作成日</th>
                </tr>
            </thead>
            <tbody>
                @foreach($blogs as $blog)
                    <tr @if ($blog_frame->bucket_id == $blog->bucket_id) class="cc-active-tr"@endif>
                        <td class="d-table-cell">
                            <input type="radio" value="{{$blog->bucket_id}}" name="select_bucket"@if ($blog_frame->bucket_id == $blog->bucket_id) checked @endif>
                        </td>
                        <td><span class="{{$frame->getSettingCaptionClass()}}">ブログ名：</span>{{$blog->blog_name}}</td>
                        <td nowrap>
                            <span class="{{$frame->getSettingCaptionClass()}}">件数：</span>{{$blog->entry_count}}
                        </td>
                        <td>
                            <span class="{{$frame->getSettingCaptionClass()}}">詳細：</span>
                            <a class="btn btn-success btn-sm" href="{{url('/')}}/plugin/blogs/editBuckets/{{$page->id}}/{{$frame_id}}/{{$blog->id}}#frame-{{$frame_id}}">
                                <i class="far fa-edit"></i> 設定変更
                            </a>
                        </td>
                        <td><span class="{{$frame->getSettingCaptionClass()}}">作成日：</span>{{$blog->created_at}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="form-group text-center">
        {{ $blogs->fragment('frame-' . $frame_id)->links() }}
    </div>

    <div class="form-group text-center mt-3">
        <a class="btn btn-secondary mr-2" href="{{URL::to($page->permanent_link)}}">
            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
        </a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示ブログ変更</button>
    </div>
</form>
@endsection
