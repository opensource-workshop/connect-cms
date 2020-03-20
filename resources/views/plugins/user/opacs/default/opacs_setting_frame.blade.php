{{--
 * 編集画面(フレーム設定)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.opacs.opacs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

@if (empty($opac_frame) || empty($opac_frame->opacs_id))
    <div class="alert alert-warning text-center">
        <i class="fas fa-exclamation-circle"></i>
        Opac の設定を行ってからOpac フレームの設定を行います。
    </div>
@else
    <form action="{{url('/')}}/redirect/plugin/opacs/saveOpacFrame/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="opacs_id" value="{{$opac_frame->opacs_id}}">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/opacs/settingOpacFrame/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}} @if(!$frame->isExpandNarrow()) pt-sm-0 @endif">表示画面 <label class="badge badge-danger">必須</label></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <div class="mb-0 col-12">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if (old('view_form', $opac_frame_setting->view_form) == 0)
                            <input type="radio" value="0" id="view_form0" name="view_form" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="view_form0" name="view_form" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="view_form0">貸し出し情報＆新規貸し出し</label>
                    </div>
                </div>
                <div class="mb-0 col-12">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if (old('view_form', $opac_frame_setting->view_form) == 1)
                            <input type="radio" value="1" id="view_form1" name="view_form" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="view_form1" name="view_form" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="view_form1">書籍検索</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submitボタン --}}
        <div class="form-group text-center mt-3">
            <div class="row">
                <div class="col-12">
                    <button type="button" class="btn btn-secondary mr-3" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                        <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span>
                    </button>
                    <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 
                        <span class="{{$frame->getSettingButtonCaptionClass()}}">
                            設定変更
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
@endif
@endsection
