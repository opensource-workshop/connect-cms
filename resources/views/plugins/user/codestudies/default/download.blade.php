{{--
 * 成績ダウンロード指示画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コードスタディプラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.codestudies.codestudies_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- ダウンロードフォーム --}}
<div class="text-center">
    <form action="{{url('/')}}/download/plugin/codestudies/download/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="GET" class="" name="form_download_{{$frame->id}}" id="form_download_{{$frame->id}}">
        {{ csrf_field() }}
        <div class="form-group">
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> ダウンロード実行</button>
        </div>
    </form>
</div>

@endsection
