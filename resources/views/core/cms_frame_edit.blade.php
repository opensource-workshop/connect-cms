{{--
 * CMSフレーム編集画面
 *
 * @param obj $frames 表示すべきフレームの配列
 * @param obj $page 現在表示中のページ
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
{{-- フレーム(編集) --}}
{{--
@php
    // エリアが左か右の場合、フレーム編集画面のbootstrap グリッドを使わない。(LABEL が折り返されて見にくくなるため)
//    if ($frame->area_id == 1 || $frame->area_id == 3) {
//        $class_label = "col-md-9 col-form-label ml-2";
//        $class_input = "col-md-9 ml-2";
//    }
//    else {
//        $class_label = "col-md-3 col-form-label text-md-right";
//        $class_input = "col-md-9";
//    }
@endphp
--}}
{{-- <table class="table"><tr><td> --}}

{{-- 設定系メニューがデザインに引きずられて画面が不完全になるのを防ぐための措置 --}}
<style type="text/css">
<!--
#frame-{{$frame->id}} {
    background: #fff;
    color: #000000;
    max-height: 100%;
}
-->
</style>
{{-- フレームが配置ページでない場合の注意 --}}
@if($frame->page_id != $page_id)
<script type="text/javascript">
    // ツールチップ
    $(function () {
        // 有効化
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
<div class="card-header bg-warning">
    配置されたページと異なるページです。<span class="fas fa-info-circle" data-toggle="tooltip" title="" data-original-title="設定を変更すると、配置されたページ以下のページに影響があります。"></span>
</div>
@endif
<div class="frame-setting">
<div class="frame-setting-menu">
    <nav class="navbar {{$frame->getNavbarExpand()}} navbar-light bg-light">
        <span class="{{$frame->getNavbarBrand()}}">設定メニュー</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg" id="button_collapsing_navbar_lg">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg">
            <ul class="navbar-nav">
                {{-- プラグイン側のフレームメニュー --}}
                {{$action_core_frame->includeFrameTab($page, $frame, $action)}}

                {{-- コア側のフレームメニュー --}}
                <li class="nav-item">
                    <span class="nav-link"><span class="active">フレーム編集</span></span>
                </li>
                <li class="nav-item">
                    <a href="{{URL::to('/')}}/plugin/{{$frame->plugin_name}}/frame_delete/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">フレーム削除</a>
                </li>
            </ul>
        </div>
    </nav>
</div>
</div>

{{--
    <div class="card-body">
        <ul class="nav nav-tabs">
--}}
            {{-- プラグイン側のフレームメニュー --}}
{{--
            {{$action_core_frame->includeFrameTab($page, $frame, $action)}}
--}}

            {{-- コア側のフレームメニュー --}}
{{--
            <li class="nav-item"><a href="{{URL::to('/')}}/plugin/{{$frame->plugin_name}}/frame_setting/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link active">フレーム編集</a></li>
            <li class="nav-item"><a href="{{URL::to('/')}}/plugin/{{$frame->plugin_name}}/frame_delete/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}" class="nav-link">フレーム削除</a></li>
        </ul>
    </div>
--}}

<div class="card-body frame-setting-body">
    <form action="{{url('/')}}/core/frame/update/{{$page->id}}/{{ $frame->frame_id }}" name="form_{{ $frame->frame_id }}_setting" method="POST">
        {{ csrf_field() }}
        @include('plugins.common.errors_form_line')
        <h5><span class="badge badge-secondary">デザイン設定</span></h5>
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">フレームタイトル</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="frame_title" id="frame_title" class="form-control" value="{{$frame->frame_title}}">
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">フレームデザイン</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <select class="form-control" name="frame_design" id="frame_design">
                    <option value="">Choose...</option>
                    <option value="none"      @if($frame->frame_design=="none")      selected @endif>None</option>
                    <option value="default"   @if($frame->frame_design=="default")   selected @endif>Default</option>
                    <option value="primary"   @if($frame->frame_design=="primary")   selected @endif>Primary</option>
                    <option value="secondary" @if($frame->frame_design=="secondary") selected @endif>Secondary</option>
                    <option value="success"   @if($frame->frame_design=="success")   selected @endif>Success</option>
                    <option value="info"      @if($frame->frame_design=="info")      selected @endif>Info</option>
                    <option value="warning"   @if($frame->frame_design=="warning")   selected @endif>Warning</option>
                    <option value="danger"    @if($frame->frame_design=="danger")    selected @endif>Danger</option>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">フレーム幅</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <select class="form-control" name="frame_col" id="frame_col">
                    <option value="">Choose...</option>
                    <option value="0"  @if($frame->frame_col==0)    selected @endif>100%</option>
                    <option value="1"  @if($frame->frame_col==1)    selected @endif>1</option>
                    <option value="2"  @if($frame->frame_col==2)    selected @endif>2</option>
                    <option value="3"  @if($frame->frame_col==3)    selected @endif>3</option>
                    <option value="4"  @if($frame->frame_col==4)    selected @endif>4</option>
                    <option value="5"  @if($frame->frame_col==5)    selected @endif>5</option>
                    <option value="6"  @if($frame->frame_col==6)    selected @endif>6</option>
                    <option value="7"  @if($frame->frame_col==7)    selected @endif>7</option>
                    <option value="8"  @if($frame->frame_col==8)    selected @endif>8</option>
                    <option value="9"  @if($frame->frame_col==9)    selected @endif>9</option>
                    <option value="10" @if($frame->frame_col==10)   selected @endif>10</option>
                    <option value="11" @if($frame->frame_col==11)   selected @endif>11</option>
                    <option value="12" @if($frame->frame_col==12)   selected @endif>12</option>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">テンプレート</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <select class="form-control" name="template" id="template">
                    @foreach ($action_core_frame->getTemplates() as $template_key => $template_name)
                        <option value="{{$template_name}}"@if($frame->template == $template_name) selected @endif>{{$template_key}}</option>
                    @endforeach
                </select>
                @if ($frame->plugin_name == 'menus')
                    <small class="text-muted">※ 「タブ」「ドロップダウン」「マウスオーバードロップダウン」系テンプレートは、スマートフォンでは表示されません。</small>
                @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">class名</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="classname" id="classname" class="form-control" value="{{$frame->classname}}">
            </div>
        </div>

        {{-- フレーム表示設定 --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}} py-sm-0">フレーム表示設定</label>
            <div class="{{$frame->getSettingInputClass()}} d-flex align-items-center">
                <div class="custom-control custom-checkbox">
                    @if($frame->browser_width == "100%")
                        <input name="browser_width" value="100%" type="checkbox" class="custom-control-input" id="browser_width" checked="checked">
                    @else
                        <input name="browser_width" value="100%" type="checkbox" class="custom-control-input" id="browser_width">
                    @endif
                    <label class="custom-control-label" for="browser_width">フレームをブラウザ幅100％にする。</label>
                </div>
            </div>
        </div>

        {{-- 初期状態を非表示とする --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}"></label>
            <div class="{{$frame->getSettingInputClass()}} d-flex align-items-center">
                <div class="custom-control custom-checkbox">
                    @if($frame->default_hidden)
                        <input name="default_hidden" value="1" type="checkbox" class="custom-control-input" id="default_hidden" checked="checked">
                    @else
                        <input name="default_hidden" value="1" type="checkbox" class="custom-control-input" id="default_hidden">
                    @endif
                    <label class="custom-control-label" for="default_hidden">初期状態を非表示とする。</label>
                </div>
            </div>
        </div>

        <h5><span class="badge badge-secondary">公開設定</span></h5>

        <div id="app_{{ $frame->id }}">
            {{-- コンテンツ公開区分 --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass(true)}}">公開設定</label>
                <div class="{{$frame->getSettingInputClass(true)}}">
                    @foreach (ContentOpenType::enum as $key => $value)
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                type="radio"
                                value="{{ $key }}"
                                id="{{ "content_open_type_${key}" }}"
                                name="content_open_type"
                                class="custom-control-input"
                                {{ old('content_open_type', $frame->content_open_type) ? 'checked' : '' }}
                                v-model="v_content_open_type"
                            >
                            <label class="custom-control-label" for="{{ "content_open_type_${key}" }}">
                                {{ $value }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            {{-- 公開日時From --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass(true)}}">公開日時From</label>
                <div class="col-md-9 input-group date" id="content_open_date_from" data-target-input="nearest">
                    <input
                        type="text"
                        name="content_open_date_from"
                        value="{{old('content_open_date_from', $frame ? $frame->content_open_date_from : '')}}"
                        class="form-control datetimepicker-input {{ $errors->has('content_open_date_from') ? ' border-danger' : '' }}"
                        data-target="#content_open_date_from"
                        placeholder="YYYY-MM-DD hh:mm:ss"
                        :readonly="v_content_open_type != {{ ContentOpenType::limited_open }}"
                    >
                    <div class="input-group-append" data-target="#content_open_date_from" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="far fa-clock"></i></div>
                    </div>
                </div>
                <small class="offset-md-3 col-md-9 text-muted">
                    ※右のボタンからカレンダー入力も可能です。
                </small>
                @if ($errors && $errors->has('content_open_date_from'))
                    <label class="{{$frame->getSettingLabelClass(true)}}"></label>
                    <div class="text-danger" style="padding-left:15px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{$errors->first('content_open_date_from')}}
                    </div>
                @endif
            </div>
            {{-- 公開日時To --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass(true)}}">公開日時To</label>
                <div class="col-md-9 input-group date" id="content_open_date_to" data-target-input="nearest">
                    <input
                        type="text"
                        name="content_open_date_to"
                        value="{{old('content_open_date_to', $frame ? $frame->content_open_date_to : '')}}"
                        class="form-control datetimepicker-input {{ $errors->has('content_open_date_to') ? ' border-danger' : '' }}"
                        data-target="#content_open_date_to"
                        placeholder="YYYY-MM-DD hh:mm:ss"
                        :readonly="v_content_open_type != {{ ContentOpenType::limited_open }}"
                    >
                    <div class="input-group-append" data-target="#content_open_date_to" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="far fa-clock"></i></div>
                    </div>
                </div>
                <small class="offset-md-3 col-md-9 text-muted">
                    ※右のボタンからカレンダー入力も可能です。
                </small>
                @if ($errors && $errors->has('content_open_date_to'))
                    <label class="{{$frame->getSettingLabelClass(true)}}"></label>
                    <div class="text-danger" style="padding-left:15px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{$errors->first('content_open_date_to')}}
                    </div>
                @endif
            </div>

        {{-- このページのみ表示するチェック。メインエリアはもともとページ内のみなので対象外 --}}
        @if ($frame->area_id != 2)
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">対象ページ</label>
            <div class="{{$frame->getSettingInputClass()}} d-flex align-items-center row m-0">

                <div class="custom-control custom-radio custom-control-inline">
                    @if ($frame->page_only == 0)
                        <input type="radio" value="0" id="page_only_0" name="page_only" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="page_only_0" name="page_only" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="page_only_0">対象ページ全てで表示する。</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($frame->page_only == 1)
                        <input type="radio" value="1" id="page_only_1" name="page_only" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="page_only_1" name="page_only" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="page_only_1">このページのみ表示する。</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if ($frame->page_only == 2)
                        <input type="radio" value="2" id="page_only_2" name="page_only" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="2" id="page_only_2" name="page_only" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="page_only_2">このページのみ表示しない。</label>
                </div>
            </div>
        </div>
        @endif

        {{-- 新着の表示制限を行うプラグインをConfig ファイルで設定 --}}
        @if (Config::get("connect.CC_DISABLE_WHATSNEWS_PLUGIN.$frame->plugin_name"))
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}"></label>
            <div class="{{$frame->getSettingInputClass()}} d-flex align-items-center">
                <div class="custom-control custom-checkbox">
                    @if($frame->disable_whatsnews)
                        <input name="disable_whatsnews" value="1" type="checkbox" class="custom-control-input" id="disable_whatsnews" checked="checked">
                    @else
                        <input name="disable_whatsnews" value="1" type="checkbox" class="custom-control-input" id="disable_whatsnews">
                    @endif
                    <label class="custom-control-label" for="disable_whatsnews">新着に表示しない。</label>
                </div>
            </div>
        </div>
        @endif

        {{-- データがない場合にフレームも非表示にする --}}
        @if (Config::get("connect.CC_NONE_HIDDEN_PLUGIN.$frame->plugin_name"))
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}"></label>
            <div class="{{$frame->getSettingInputClass()}} d-flex align-items-center">
                <div class="custom-control custom-checkbox">
                    @if($frame->none_hidden)
                        <input name="none_hidden" value="1" type="checkbox" class="custom-control-input" id="none_hidden" checked="checked">
                    @else
                        <input name="none_hidden" value="1" type="checkbox" class="custom-control-input" id="none_hidden">
                    @endif
                    <label class="custom-control-label" for="none_hidden">データがない場合にフレームも非表示にする。</label>
                </div>
            </div>
        </div>
        @endif

        <div class="form-group row mx-auto text-center">
            <div class="col-md-12">
                <button type="button" class="btn btn-secondary form-horizontal mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span></button>
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>
        </div>
    </form>
</div>
<script>
    new Vue({
      el: "#app_{{ $frame->id }}",
      data: {
        // コンテンツ公開区分
        v_content_open_type: '{{ old('content_open_type', $frame->content_open_type) }}'
      }
    })
    // 公開日時Fromのpicker
    $(function () {
        $('#content_open_date_from').datetimepicker({
            locale: 'ja',
            sideBySide: true,
            dayViewHeaderFormat: 'YYYY年 M月',
            format: 'YYYY-MM-DD HH:mm:ss'
        });
    });
    // 公開日時Toのpicker
    $(function () {
        $('#content_open_date_to').datetimepicker({
            locale: 'ja',
            sideBySide: true,
            dayViewHeaderFormat: 'YYYY年 M月',
            format: 'YYYY-MM-DD HH:mm:ss'
        });
    });
</script>
{{-- </td></tr></table> --}}
