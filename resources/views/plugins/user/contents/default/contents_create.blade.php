{{--
 * 新規登録画面テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
{{-- WYSIWYG 呼び出し --}}
@include('plugins.common.wysiwyg', ['target_class' => 'wysiwyg' . $frame->id])

{{-- 一時保存ボタンのアクション --}}
<script type="text/javascript">
    function save_action() {
        form_update.action = "{{url('/')}}/redirect/plugin/contents/temporarysave/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}";
        form_update.submit();
    }
</script>

{{-- 新規登録用フォーム --}}
<form action="{{url('/')}}/redirect/plugin/contents/store/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="" name="form_update" id="{{$frame->plugin_name}}-{{$frame->id}}-form">
    {{ csrf_field() }}
    <input type="hidden" name="action" value="edit">

    <div class="form-group">
        <textarea name="contents" class="wysiwyg{{$frame->id}}"></textarea>
    </div>

    <div class="form-group">
        <label class="control-label">データ名</label>
        <input type="text" name="bucket_name" value="{{old('bucket_name')}}" class="form-control">
        <small class="text-muted">※ 空の場合「無題」で登録します。</small>
    </div>

    <div class="form-group text-center">
        <input type="hidden" name="bucket_id" value="">
        <br />
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span></button>
        <button type="button" class="btn btn-info mr-2" onclick="save_action();"><i class="far fa-save"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 一時保存</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 登録確定</button>
    </div>
</form>
@endsection
