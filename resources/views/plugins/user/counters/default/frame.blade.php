{{--
 * フレーム表示設定編集画面テンプレート。
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.counters.counters_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

<div class="alert alert-info">
    <i class="fas fa-exclamation-circle"></i> フレームごとの表示設定を変更します。
</div>

<form action="{{url('/')}}/redirect/plugin/counters/saveView/{{$page->id}}/{{$frame_id}}/{{$counter->id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/counters/editView/{{$page->id}}/{{$frame_id}}/{{$counter->bucket_id}}#frame-{{$frame_id}}">

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">現在の表示</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <div class="card mt-1 border-info">
                <div class="card-body p-0">
                    @include('plugins.user.counters.default.index', [
                        'plugin_frame' => $counter_frame,
                    ])
                </div>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">表示形式</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <select class="form-control" name="design_type" class="form-control">
                @foreach (CounterDesignType::getMembers() as $enum_value => $enum_label)
                    <option value="{{$enum_value}}" @if(old('design_type', $counter_frame->design_type) == $enum_value) selected="selected" @endif>{{$enum_label}}</option>
                @endforeach
            </select>
            @include('plugins.common.errors_inline', ['name' => 'design_type'])
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}} pt-0">累計カウント</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="col pl-0">

                <div class="row">
                    <div class="col-md">
                        <label>累計カウントの表示</label><br>

                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="0" id="use_total_count_0" name="use_total_count" class="custom-control-input" @if(old('use_total_count', $counter_frame->use_total_count) == 0) checked="checked" @endif>
                            <label class="custom-control-label" for="use_total_count_0">表示しない</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="1" id="use_total_count_1" name="use_total_count" class="custom-control-input" @if(old('use_total_count', $counter_frame->use_total_count) == 1) checked="checked" @endif>
                            <label class="custom-control-label" for="use_total_count_1">表示する</label>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md">
                        <label>累計カウントの項目名</label><br>
                        <input type="text" name="total_count_title" value="{{old('total_count_title', $counter_frame->total_count_title)}}" class="form-control" placeholder="（例）累計">
                        @include('plugins.common.errors_inline', ['name' => 'total_count_title'])
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md">
                        <label>累計カウントの単位</label><br>
                        <input type="text" name="total_count_after" value="{{old('total_count_after', $counter_frame->total_count_after)}}" class="form-control" placeholder="（例）人">
                        @include('plugins.common.errors_inline', ['name' => 'total_count_after'])
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}} pt-0">本日のカウント</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="col pl-0">

                <div class="row">
                    <div class="col-md">
                        <label>本日のカウント表示</label><br>

                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="0" id="use_today_count_0" name="use_today_count" class="custom-control-input" @if(old('use_today_count', $counter_frame->use_today_count) == 0) checked="checked" @endif>
                            <label class="custom-control-label" for="use_today_count_0">表示しない</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="1" id="use_today_count_1" name="use_today_count" class="custom-control-input" @if(old('use_today_count', $counter_frame->use_today_count) == 1) checked="checked" @endif>
                            <label class="custom-control-label" for="use_today_count_1">表示する</label>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md">
                        <label>本日のカウントの項目名</label><br>
                        <input type="text" name="today_count_title" value="{{old('today_count_title', $counter_frame->today_count_title)}}" class="form-control" placeholder="（例）本日">
                        @include('plugins.common.errors_inline', ['name' => 'today_count_title'])
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md">
                        <label>本日のカウントの単位</label><br>
                        <input type="text" name="today_count_after" value="{{old('today_count_after', $counter_frame->today_count_after)}}" class="form-control" placeholder="（例）人">
                        @include('plugins.common.errors_inline', ['name' => 'today_count_after'])
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}} pt-0">昨日のカウント</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="col pl-0">

                <div class="row">
                    <div class="col-md">
                        <label>昨日のカウント表示</label><br>

                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="0" id="use_yesterday_count_0" name="use_yesterday_count" class="custom-control-input" @if(old('use_yesterday_count', $counter_frame->use_yesterday_count) == 0) checked="checked" @endif>
                            <label class="custom-control-label" for="use_yesterday_count_0">表示しない</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="1" id="use_yesterday_count_1" name="use_yesterday_count" class="custom-control-input" @if(old('use_yesterday_count', $counter_frame->use_yesterday_count) == 1) checked="checked" @endif>
                            <label class="custom-control-label" for="use_yesterday_count_1">表示する</label>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md">
                        <label>昨日のカウントの項目名</label><br>
                        <input type="text" name="yesterday_count_title" value="{{old('yesterday_count_title', $counter_frame->yesterday_count_title)}}" class="form-control" placeholder="（例）昨日">
                        @include('plugins.common.errors_inline', ['name' => 'yesterday_count_title'])
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md">
                        <label>昨日のカウントの単位</label><br>
                        <input type="text" name="yesterday_count_after" value="{{old('yesterday_count_after', $counter_frame->yesterday_count_after)}}" class="form-control" placeholder="（例）人">
                        @include('plugins.common.errors_inline', ['name' => 'yesterday_count_after'])
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <a href="{{URL::to($page->permanent_link)}}" class="btn btn-secondary mr-2">
            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
        </a>
        <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
            変更確定
        </button>
    </div>
</form>

@endsection
