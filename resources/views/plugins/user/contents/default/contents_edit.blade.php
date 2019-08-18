{{--
 * 編集画面テンプレート
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
        language : 'ja',
        plugins  : 'file media image jbimages link autolink preview textcolor code table lists',
        toolbar  : 'plugin insert media image bold italic underline strikethrough subscript superscript | formatselect | forecolor backcolor | table | numlist bullist | blockquote | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link jbimages | preview | code ',
        block_formats: "スタイル=p;見出し1=h1;見出し2=h2;見出し3=h3;見出し4=h4;見出し5=h5;見出し6=h6",
        menubar  : '',
        relative_urls : false,
        height: 200,
        branding: false,
        protect: [
            /\<\/?(script)\>/g
        ],
file_picker_types: 'file image media',
media_live_embeds: true,
image_caption: true,
image_title: true,


//	images_upload_url: 'postAcceptor.php',
//	images_upload_url: '/debug/test.php',
//	images_upload_base_path: '/some/basepath',
//	images_upload_base_path: '/debug',
//	images_upload_credentials: true,

  images_upload_handler: function (blobInfo, success, failure) {
	var xhr, formData;

	xhr = new XMLHttpRequest();
	xhr.withCredentials = false;
	xhr.open('POST', '/debug/postAcceptor.php');

	xhr.onload = function() {
	  var json;

	  if (xhr.status < 200 || xhr.status >= 300) {
		failure('HTTP Error: ' + xhr.status);
		return;
	  }

	  json = JSON.parse(xhr.responseText);

	  if (!json || typeof json.location != 'string') {
		failure('Invalid JSON: ' + xhr.responseText);
		return;
	  }

	  success(json.location);
	};

	formData = new FormData();

if( typeof(blobInfo.blob().name) !== undefined )
    fileName = blobInfo.blob().name;
else
    fileName = blobInfo.filename();

	formData.append('file', blobInfo.blob(), fileName);

	xhr.send(formData);
  }
    });
</script>

{{-- 更新用フォーム --}}
<div class="text-center">
    <form action="/redirect/plugin/contents/update/{{$page->id}}/{{$frame_id}}/{{$contents->id}}" method="POST" class="">
        {{ csrf_field() }}
        <input type="hidden" name="action" value="edit">

        <textarea name="contents">{!! $contents->content_text !!}</textarea>

        <div class="form-group">
            <input type="hidden" name="bucket_id" value="{{$contents->bucket_id}}">
            <br />
            <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> 変更確定</button>
            <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'"><span class="glyphicon glyphicon-remove"></span> キャンセル</button>
        </div>
    </form>
</div>
