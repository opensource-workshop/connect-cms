{{--
 * 編集画面テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}

{{-- 機能選択タブ --}}
@include('plugins.user.contents.default.contents_edit_tab')

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
        branding: false
    });
</script>

{{-- 更新用フォーム --}}
<form action="/redirect/plugin/contents/update/{{$page->id}}/{{$frame_id}}/{{$contents->id}}" method="POST" class="">
    {{ csrf_field() }}
    <input type="hidden" name="action" value="edit">

    <textarea name="contents">{!! $contents->content_text !!}</textarea>

    <div class="form-group">
        <input type="hidden" name="bucket_id" value="{{$contents->bucket_id}}">
        <br />
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'">Cancel</button>
    </div>
</form>
