{{--
 * 新規登録画面テンプレート
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

{{-- WYSIWYG 呼び出し --}}
<script type="text/javascript" src="/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
    tinymce.init({
        selector : 'textarea',
        plugins  : 'jbimages link autolink preview textcolor code table',
        toolbar  : 'bold italic underline strikethrough subscript superscript | forecolor backcolor | table | blockquote | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link jbimages | preview | code',
        menubar  : '',
        relative_urls : false,
        height: 200,
        branding: false,
        protect: [
            /\<\/?(script)\>/g
        ],
images_upload_url: '/debug/postAcceptor.php',
file_picker_types: 'file image media',
automatic_uploads: false,
  file_picker_callback: function(callback, value, meta) {
    // Provide file and text for the link dialog
    if (meta.filetype == 'file') {
      callback('mypage.html', {text: 'My text'});
    }

    // Provide image and alt text for the image dialog
    if (meta.filetype == 'image') {
      callback('myimage.jpg', {alt: 'My alt text'});
    }

    // Provide alternative source and posted for the media dialog
    if (meta.filetype == 'media') {
      callback('movie.mp4', {source2: 'alt.ogg', poster: 'image.jpg'});
    }
  },

    });
</script>

{{-- 新規登録用フォーム --}}
<div class="text-center">
    <form action="/redirect/plugin/contents/store/{{$page->id}}/{{$frame_id}}" method="POST" class="">
        {{ csrf_field() }}
        <input type="hidden" name="action" value="edit">

        <textarea name="contents"></textarea>

        <div class="form-group">
            <input type="hidden" name="bucket_id" value="">
            <br />
            <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> 登録確定</button>
            <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'"><span class="glyphicon glyphicon-remove"></span> キャンセル</button>
        </div>
    </form>
</div>
