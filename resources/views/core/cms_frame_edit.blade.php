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

<div class="frame-setting">
<div class="frame-setting-menu">
    <nav class="navbar {{$frame->getNavbarExpand()}} navbar-light bg-light">
        <span class="{{$frame->getNavbarBrand()}}">設定メニュー</span>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg">
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
    <form action="/core/frame/update/{{$page->id}}/{{ $frame->frame_id }}" name="form_{{ $frame->frame_id }}_setting" method="POST">
        {{ csrf_field() }}
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
                    <option value="none"    @if($frame->frame_design=="none")    selected @endif>None</option>
                    <option value="default" @if($frame->frame_design=="default") selected @endif>Default</option>
                    <option value="primary" @if($frame->frame_design=="primary") selected @endif>Primary</option>
                    <option value="success" @if($frame->frame_design=="success") selected @endif>Success</option>
                    <option value="info"    @if($frame->frame_design=="info")    selected @endif>Info</option>
                    <option value="warning" @if($frame->frame_design=="warning") selected @endif>Warning</option>
                    <option value="danger"  @if($frame->frame_design=="danger")  selected @endif>Danger</option>
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
                    <option value="default">default</option>
                    @foreach ($action_core_frame->getTemplates() as $template_name)
                        <option value="{{$template_name}}"@if($frame->template == $template_name) selected @endif>{{$template_name}}</option>
                    @endforeach
                </select>
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

        {{-- このページのみ表示するチェック。メインエリアはもともとページ内のみなので対象外 --}}
        @if ($frame->area_id != 2)
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}"></label>
            <div class="{{$frame->getSettingInputClass()}} d-flex align-items-center">
                <div class="custom-control custom-checkbox">
                    @if($frame->page_only)
                        <input name="page_only" value="1" type="checkbox" class="custom-control-input" id="page_only" checked="checked">
                    @else
                        <input name="page_only" value="1" type="checkbox" class="custom-control-input" id="page_only">
                    @endif
                    <label class="custom-control-label" for="page_only">このページにのみ表示する。</label>
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
                <button type="button" class="btn btn-secondary form-horizontal mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span></button>
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>
        </div>
    </form>
</div>
{{-- </td></tr></table> --}}
