{{--
 * 編集画面テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}

{{-- WYSIWYG 呼び出し --}}
@include('plugins.common.wysiwyg')

{{-- 一時保存ボタンのアクション --}}
<script type="text/javascript">
    function save_action() {
        form_update.action = "/redirect/plugin/contents/temporarysave/{{$page->id}}/{{$frame_id}}/{{$contents->id}}";
        form_update.submit();
    }
</script>

{{-- 更新用フォーム --}}
<div class="text-center">
    <form action="/redirect/plugin/contents/update/{{$page->id}}/{{$frame_id}}/{{$contents->id}}" method="POST" class="" name="form_update">
        {{ csrf_field() }}
        <input type="hidden" name="action" value="edit">

        <textarea name="contents">{!! $contents->content_text !!}</textarea>

        <div class="form-group">
            <input type="hidden" name="bucket_id" value="{{$contents->bucket_id}}">
            <br />
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i> キャンセル</button>
            <button type="button" class="btn btn-info mr-2" onclick="javascript:save_action();"><i class="far fa-save"></i> 一時保存</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更確定</button>
        </div>
    </form>
</div>
