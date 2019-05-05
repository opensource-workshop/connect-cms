{{--
 * ブログ記事登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}

{{-- WYSIWYG 呼び出し --}}
<script type="text/javascript" src="/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
    tinymce.init({
        selector : 'textarea',
        plugins  : 'jbimages link autolink preview textcolor code',
        toolbar  : 'bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link jbimages | preview | code',
        menubar  : false,
        relative_urls : false,
        height: 200,
        branding: false,
        protect: [
            /\<\/?(script)\>/g
        ],
    });
</script>

{{-- 投稿用フォーム --}}
@if (empty($blogs_posts->id))
    <form action="/plugin/blogs/save/{{$page->id}}/{{$frame_id}}" method="POST" class="">
@else
    <form action="/plugin/blogs/save/{{$page->id}}/{{$frame_id}}/{{$blogs_posts->id}}" method="POST" class="">
@endif
    {{ csrf_field() }}
    <input type="hidden" name="blogs_id" value="{{$blog_frame->blogs_id}}">

    <div class="form-group">
        <label class="control-label">タイトル <span class="label label-danger">必須</span></label>
        <input type="text" name="post_title" value="{{old('post_title', $blogs_posts->post_title)}}" class="form-control">
        @if ($errors && $errors->has('post_title')) <div class="text-danger">{{$errors->first('post_title')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">投稿日時 <span class="label label-danger">必須</span></label>
        <input type="text" name="posted_at" value="{{old('posted_at', $blogs_posts->posted_at)}}" class="form-control">
        @if ($errors && $errors->has('posted_at')) <div class="text-danger">{{$errors->first('posted_at')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">本文 <span class="label label-danger">必須</span></label>
        <textarea name="post_text">{!!old('post_text', $blogs_posts->post_text)!!}</textarea>
        @if ($errors && $errors->has('post_text')) <div class="text-danger">{{$errors->first('post_text')}}</div> @endif
    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6">
                <div class="text-center">
                    <input type="hidden" name="bucket_id" value="">
                    @if (empty($blogs_posts->id))
                        <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> 登録確定</button>
                    @else
                        <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> 変更確定</button>
                    @endif
                    <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'"><span class="glyphicon glyphicon-remove"></span> キャンセル</button>
                </div>
            </div>
            <div class="col-sm-3 pull-right text-right">
                @if (!empty($blogs_posts->id))
                    <a data-toggle="collapse" href="#collapse{{$blogs_posts->id}}">
                        <span class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> <span class="hidden-xs">削除</span></span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</form>

<div id="collapse{{$blogs_posts->id}}" class="collapse" style="margin-top: 8px;">
    <div class="panel panel-danger">
        <div class="panel-body">
            <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/plugin/blogs/destroy/{{$page->id}}/{{$frame_id}}/{{$blogs_posts->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><span class="glyphicon glyphicon-ok"></span> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
