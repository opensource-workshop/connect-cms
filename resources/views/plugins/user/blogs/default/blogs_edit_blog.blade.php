{{--
 * ブログ編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
 --}}

<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.blogs.blogs_frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

@if (!$blog->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
        設定画面から、使用するブログを選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info" style="margin-top: 10px;">
        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>

        @if ($message)
            {{$message}}
        @else
            @if (empty($blog) || $create_flag)
                新しいブログ設定を登録します。
            @else
                ブログ設定を変更します。
            @endif
        @endif
    </div>
@endif

@if (!$blog->id && !$create_flag)
@else
<form action="/plugin/blogs/saveBlogs/{{$page->id}}/{{$frame_id}}" method="POST" class="">
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
        @if ($errors && $errors->has('blog_name')) <div class="text-danger">{{$errors->first('blog_name')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">表示件数 <span class="label label-danger">必須</span></label>
        <input type="text" name="view_count" value="{{old('view_count', $blog->view_count)}}" class="form-control">
        @if ($errors && $errors->has('view_count')) <div class="text-danger">{{$errors->first('view_count')}}</div> @endif
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6">
                <button type="submit" class="btn btn-primary form-horizontal"><span class="glyphicon glyphicon-ok"></span> 
                @if (empty($blog) || $create_flag)
                    登録確定
                @else
                    変更確定
                @endif
                </button>
                <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                    <span class="glyphicon glyphicon-remove"></span> キャンセル
                </button>
            </div>

            {{-- 既存ブログの場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
            <div class="col-sm-3 pull-right text-right">
                <a data-toggle="collapse" href="#collapse{{$blog_frame->id}}">
                    <span class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> <span class="hidden-xs">削除</span></span>
                </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$blog_frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="panel panel-danger">
        <div class="panel-body">
            <span class="text-danger">ブログを削除します。<br>このブログに記載した記事も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/blogs/blogsDestroy/{{$page->id}}/{{$frame_id}}/{{$blog->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><span class="glyphicon glyphicon-ok"></span> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
