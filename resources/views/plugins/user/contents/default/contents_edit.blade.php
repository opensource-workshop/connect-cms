{{--
 * 編集画面テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
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
    <form action="{{url('/')}}/redirect/plugin/contents/update/{{$page->id}}/{{$frame_id}}/{{$contents->id}}" method="POST" class="" name="form_update">
        {{ csrf_field() }}
        <input type="hidden" name="action" value="edit">

        <textarea name="contents">{!! $contents->content_text !!}</textarea>

        @if ($contents->status == 1)
            <span class="badge badge-warning align-bottom float-left mt-1">一時保存</span>
        @endif

        @if ($contents->status == 2)
            @can('role_update_or_approval',[[$contents, 'contents', $buckets]])
                <span class="badge badge-warning align-bottom float-left mt-1">承認待ち</span>
            @endcan
        @endif

        <div class="form-group">
            <input type="hidden" name="bucket_id" value="{{$contents->bucket_id}}">
            <br />
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span></button>
            <button type="button" class="btn btn-info mr-2" onclick="javascript:save_action();"><i class="far fa-save"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 一時保存</span></button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更確定</button>
        </div>
    </form>
</div>
@endsection
