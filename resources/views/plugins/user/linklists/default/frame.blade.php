{{--
 * フレーム表示設定編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category リンクリスト・プラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.linklists.linklists_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
<div class="alert alert-info">
    <i class="fas fa-exclamation-circle"></i>
    フレームごとの表示設定を変更します。
</div>

<form action="{{url('/')}}/redirect/plugin/linklists/saveView/{{$page->id}}/{{$frame_id}}/{{$linklist->id}}" method="POST" class="">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="/plugin/linklists/editView/{{$page->id}}/{{$frame_id}}/{{$linklist->bucket_id}}#frame-{{$frame_id}}">
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">表示件数</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="view_count" value="{{old('name', $linklist_frame->view_count)}}" class="form-control">
            @if ($errors && $errors->has('view_count')) <div class="text-danger">{{$errors->first('view_count')}}</div> @endif
            <small class="text-muted">※ 未設定時は10件</small>
        </div>
    </div>
    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
        </button>
        <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 
            <span class="{{$frame->getSettingButtonCaptionClass()}}">
                変更確定
            </span>
        </button>
    </div>
</form>
@endsection
