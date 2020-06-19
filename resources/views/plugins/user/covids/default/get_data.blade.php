{{--
 * データ取得画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 感染症数値集計プラグイン(covid)
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.covids.covids_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if (!$covid || !$covid->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        表示するコンテンツを選択するか、新規作成してください。
    </div>
@else

@if ($cc_massage)
    <div class="alert alert-danger" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        {{$cc_massage}}
    </div>
@endif

<h5><span class="badge badge-secondary">CSV取り込み</span></h5>

<div class="alert alert-primary" role="alert">
    取り込み済の最終データ：{{$csv_last_date}}
</div>

<form action="{{url('/')}}/plugin/covids/pullData/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" name="form_pull">
    {{ csrf_field() }}

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">取り込み開始日付 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="csv_next_date" value="{{old('csv_next_date', $csv_next_date)}}" class="form-control">
            @if ($errors && $errors->has('csv_next_date')) <div class="text-danger">{{$errors->first('csv_next_date')}}</div> @endif
            <span class="text-muted">※ 年-月-日で入力してください。</span><br>
        </div>
    </div>
    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                </button>
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 
                    <span class="{{$frame->getSettingButtonCaptionClass()}}">
                        取り込み
                    </span>
                </button>
            </div>
        </div>
    </div>
</form>

<h5><span class="badge badge-secondary">データベースImport</span></h5>

<form action="{{url('/')}}/plugin/covids/importData/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" name="form_pull">
    {{ csrf_field() }}

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">取り込み開始日付 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="start_date" value="{{old('start_date', $start_date)}}" class="form-control">
            @if ($errors && $errors->has('start_date')) <div class="text-danger">{{$errors->first('start_date')}}</div> @endif
            <span class="text-muted">※ 年-月-日で入力してください。</span><br>
        </div>
    </div>
    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                </button>
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 
                    <span class="{{$frame->getSettingButtonCaptionClass()}}">
                        取り込み
                    </span>
                </button>
            </div>
        </div>
    </div>
</form>

@endif
@endsection
