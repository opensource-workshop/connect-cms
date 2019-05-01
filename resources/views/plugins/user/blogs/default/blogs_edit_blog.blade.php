{{--
 * ブログ編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
 --}}

<div class="panel-body">
    <ul class="nav nav-tabs">
        {{-- プラグイン側のフレームメニュー --}}
        @include('plugins.user.blogs.frame_edit_tab')

        {{-- コア側のフレームメニュー --}}
        @include('core.cms_frame_edit_tab')
    </ul>
</div>

<div class="container-fluid">

<div class="alert alert-info" role="info">
    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
    @if (empty($blog) || $create_flag)
        新しいブログ設定を登録します。
    @else
        ブログ設定を変更します。
    @endif
</div>

{{-- <form action="/plugin/blogs/saveBlogs/{{$page->id}}/{{$frame_id}}" method="POST" class=""> --}}
<form action="/redirect/plugin/blogs/saveBlogs/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}
    {{-- create_flag がtrue の場合、新規作成するためにblogs_id を空にする --}}
    @if ($create_flag)
        <input type="hidden" name="blogs_id" value="">
    @else
        <input type="hidden" name="blogs_id" value="{{$blog->id}}">
    @endif

    <div class="form-group">
        <label class="control-label">ブログ名 <span class="label label-danger">必須</span></label>
        <input type="text" name="blog_name" value="{{old('blog_name', $blog->blog_name)}}" class="form-control">
        <div class="text-danger"></div>
    </div>

    <div class="form-group">
        <label class="control-label">表示件数 <span class="label label-danger">必須</span></label>
        <input type="text" name="view_count" value="{{old('view_count', $blog->view_count)}}" class="form-control">
        <div class="text-danger"></div>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <button type="submit" class="btn btn-primary form-horizontal"><span class="glyphicon glyphicon-ok"></span> 
            @if (empty($blog) || $create_flag)
                登録
            @else
                変更
            @endif
        </button>
        <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'">キャンセル</button>
    </div>

</form>
</div>
