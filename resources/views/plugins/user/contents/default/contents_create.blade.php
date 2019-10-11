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

{{-- 一時保存ボタンのアクション --}}
<script type="text/javascript">
    function save_action() {
        form_update.action = "/redirect/plugin/contents/temporarysave/{{$page->id}}/{{$frame_id}}";
        form_update.submit();
    }
</script>

{{-- 新規登録用フォーム --}}
<div class="text-center">
    <form action="/redirect/plugin/contents/store/{{$page->id}}/{{$frame_id}}" method="POST" class="" name="form_update">
        {{ csrf_field() }}
        <input type="hidden" name="action" value="edit">

        <textarea name="contents"></textarea>

        <div class="form-group">
            <input type="hidden" name="bucket_id" value="">
            <br />
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i> キャンセル</button>
            <button type="button" class="btn btn-info mr-2" onclick="javascript:save_action();"><i class="far fa-save"></i> 一時保存</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 登録確定</button>
        </div>
    </form>
</div>
