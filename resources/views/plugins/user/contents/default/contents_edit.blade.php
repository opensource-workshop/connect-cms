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
