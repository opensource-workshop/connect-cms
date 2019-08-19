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
@include('plugins.common.wysiwyg')

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
