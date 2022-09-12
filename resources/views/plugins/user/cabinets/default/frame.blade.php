{{--
 * フレーム表示設定編集画面テンプレート。
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.cabinets.cabinets_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

@if (empty($cabinet->id) && $action != 'createBuckets')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        選択画面から、使用するキャビネットを選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i>
        フレームごとの表示設定を変更します。
    </div>

    <form action="{{url('/')}}/redirect/plugin/cabinets/saveView/{{$page->id}}/{{$frame_id}}/{{$cabinet->id}}#frame-{{$frame->id}}" method="POST" class="">
        {{ csrf_field() }}
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/cabinets/editView/{{$page->id}}/{{$frame_id}}/{{$cabinet->bucket_id}}#frame-{{$frame_id}}">

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">並び順</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                <select class="form-control" name="sort">
                    @foreach (CabinetSort::getMembers() as $sort_key => $sort_view)
                        {{-- 未設定時の初期値 --}}
                        @if ($sort_key == CabinetSort::name_asc && FrameConfig::getConfigValueAndOld($frame_configs, CabinetFrameConfig::sort) == '')
                            <option value="{{$sort_key}}" selected>{{  $sort_view  }}</option>
                        @else
                            <option value="{{$sort_key}}" @if(FrameConfig::getConfigValueAndOld($frame_configs, CabinetFrameConfig::sort) == $sort_key) selected @endif>{{  $sort_view  }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ダウンロード件数の表示 --}}
        @php
            $show_download_count = FrameConfig::getConfigValueAndOld($frame_configs, CabinetFrameConfig::show_download_count, ShowType::not_show);
        @endphp
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{CabinetFrameConfig::getDescription(CabinetFrameConfig::show_download_count)}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach (ShowType::getMembers() as $sort_key => $sort_view)
                <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="{{$sort_key}}" id="show_download_count_{{$sort_key}}" name="show_download_count" class="custom-control-input" @if ($show_download_count == $sort_key) checked="checked" @endif>
                        <label class="custom-control-label" for="show_download_count_{{$sort_key}}">{{$sort_view}}</label>
                </div>
                @endforeach
            </div>
        </div>

        {{-- 作成者の表示 --}}
        @php
            $show_created_name = FrameConfig::getConfigValueAndOld($frame_configs, CabinetFrameConfig::show_created_name, ShowType::not_show);
        @endphp
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{CabinetFrameConfig::getDescription(CabinetFrameConfig::show_created_name)}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach (ShowType::getMembers() as $sort_key => $sort_view)
                <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="{{$sort_key}}" id="show_created_name_{{$sort_key}}" name="show_created_name" class="custom-control-input" @if ($show_created_name == $sort_key) checked="checked" @endif>
                        <label class="custom-control-label" for="show_created_name_{{$sort_key}}">{{$sort_view}}</label>
                </div>
                @endforeach
            </div>
        </div>

        {{-- 更新者の表示 --}}
        @php
            $show_updated_name = FrameConfig::getConfigValueAndOld($frame_configs, CabinetFrameConfig::show_updated_name, ShowType::not_show);
        @endphp
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{CabinetFrameConfig::getDescription(CabinetFrameConfig::show_updated_name)}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach (ShowType::getMembers() as $sort_key => $sort_view)
                <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="{{$sort_key}}" id="show_updated_name_{{$sort_key}}" name="show_updated_name" class="custom-control-input" @if ($show_updated_name == $sort_key) checked="checked" @endif>
                        <label class="custom-control-label" for="show_updated_name_{{$sort_key}}">{{$sort_view}}</label>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Submitボタン --}}
        <div class="text-center">
            <a class="btn btn-secondary mr-2" href="{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}">
                <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
            </a>
            <button type="submit" class="btn btn-primary form-horizontal">
                <i class="fas fa-check"></i>
                <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    変更確定
                </span>
            </button>
        </div>
    </form>
@endif
@endsection
