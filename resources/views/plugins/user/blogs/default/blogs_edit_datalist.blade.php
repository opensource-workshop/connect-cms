{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
 --}}

<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.blogs.frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

<form action="/plugin/blogs/change/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group">
        <table class="table table-hover" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th></th>
                <th>ブログ名</th>
                <th>詳細</th>
                <th>作成日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($blogs as $blog)
            <tr @if ($blog_frame->blogs_id == $blog->id) class="active"@endif>
                <td><input type="radio" value="{{$blog->bucket_id}}" name="select_bucket"@if ($blog_frame->bucket_id == $blog->bucket_id) checked @endif></input></td>
                <td>{{$blog->blog_name}}</td>
                <th><button class="btn btn-primary btn-sm" type="button" onclick="location.href='{{url('/')}}/plugin/blogs/editBlog/{{$page->id}}/{{$frame_id}}/{{$blog->id}}'"><span class="glyphicon glyphicon-edit"></span> ブログ設定変更</button></th>
                <td>{{$blog->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <div class="text-center">
        {{ $blogs->links() }}
    </div>

    <div class="form-group text-center">
        <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> 表示ブログ変更</button>
        <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'"><span class="glyphicon glyphicon-remove"></span> キャンセル</button>
    </div>
</form>
