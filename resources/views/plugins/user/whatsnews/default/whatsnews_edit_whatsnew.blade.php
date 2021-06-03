{{--
 * 新着情報編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 新着情報プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.whatsnews.whatsnews_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if ($errors)
    <div class="alert alert-danger" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        エラーがあります。詳しくは各項目を参照してください。
    </div>
@elseif (isset($whatsnew) && !$whatsnew->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        設定画面から、使用する新着情報を選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>

        @if ($message)
            {{$message}}
        @else
            @if (empty($whatsnew) || $create_flag)
                新しい新着情報設定を登録します。
            @else
                新着情報設定を変更します。
            @endif
        @endif
    </div>
@endif

@if (isset($whatsnew))
@if (!$whatsnew->id && !$create_flag)
@else
<form action="{{url('/')}}/plugin/whatsnews/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}
    <div id="app_{{ $frame->id }}">

    {{-- create_flag がtrue の場合、新規作成するためにwhatsnews_id を空にする --}}
    @if ($create_flag)
        <input type="hidden" name="whatsnews_id" value="">
    @else
        <input type="hidden" name="whatsnews_id" value="{{$whatsnew->id}}">
    @endif

    {{-- バケツ名 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">新着情報名 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="whatsnew_name" value="{{old('whatsnew_name', $whatsnew->whatsnew_name)}}" class="form-control">
            @if ($errors && $errors->has('whatsnew_name')) <div class="text-danger">{{$errors->first('whatsnew_name')}}</div> @endif
        </div>
    </div>

    <h5><span class="badge badge-secondary">新着の取得方式・表示件数</span></h5>

    {{-- 新着の取得方式 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">取得方式</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if($whatsnew->view_pattern == 0)
                    <input type="radio" value="0" id="view_pattern_0" name="view_pattern" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="view_pattern_0" name="view_pattern" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="view_pattern_0">件数で表示する。</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($whatsnew->view_pattern == 1)
                    <input type="radio" value="1" id="view_pattern_1" name="view_pattern" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="view_pattern_1" name="view_pattern" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="view_pattern_1">日数で表示する。</label>
            </div>
        </div>
    </div>

    {{-- 表示件数 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">表示件数</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="count" value="{{old('count', $whatsnew->count)}}" class="form-control col-sm-3">
            @if ($errors && $errors->has('count')) <div class="text-danger">{{$errors->first('count')}}</div> @endif
        </div>
    </div>

    {{-- 表示日数 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">表示日数</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="days" value="{{old('days', $whatsnew->days)}}" class="form-control col-sm-3">
            @if ($errors && $errors->has('days')) <div class="text-danger">{{$errors->first('days')}}</div> @endif
        </div>
    </div>

    <h5><span class="badge badge-secondary">RSS</span></h5>

    {{-- RSS --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">RSS</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            @foreach (ShowType::enum as $key => $value)
                <div class="custom-control custom-radio custom-control-inline">
                    <input 
                        type="radio" 
                        value="{{ $key }}" 
                        id="{{ "rss_${key}" }}"
                        name="rss" 
                        class="custom-control-input" 
                        {{ $whatsnew->rss == $key ? 'checked' : '' }}
                    >
                    <label class="custom-control-label" for="{{ "rss_${key}" }}">
                        {{ $value }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    {{-- RSS件数 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">RSS件数</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="rss_count" value="{{old('rss_count', $whatsnew->rss_count)}}" class="form-control col-sm-3">
            @if ($errors && $errors->has('rss_count')) <div class="text-danger">{{$errors->first('rss_count')}}</div> @endif
        </div>
    </div>

    <h5><span class="badge badge-secondary">その他情報の表示</span></h5>

    {{-- 登録者の表示 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">登録者の表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            @foreach (ShowType::enum as $key => $value)
                <div class="custom-control custom-radio custom-control-inline">
                    <input 
                        type="radio" 
                        value="{{ $key }}" 
                        id="{{ "view_posted_name_${key}" }}"
                        name="view_posted_name" 
                        class="custom-control-input" 
                        {{ $whatsnew->view_posted_name == $key ? 'checked' : '' }}
                    >
                    <label class="custom-control-label" for="{{ "view_posted_name_${key}" }}">
                        {{ $value }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 登録日時の表示 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">登録日時の表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            @foreach (ShowType::enum as $key => $value)
                <div class="custom-control custom-radio custom-control-inline">
                    <input 
                        type="radio" 
                        value="{{ $key }}" 
                        id="{{ "view_posted_at_${key}" }}"
                        name="view_posted_at" 
                        class="custom-control-input" 
                        {{ $whatsnew->view_posted_at == $key ? 'checked' : '' }}
                    >
                    <label class="custom-control-label" for="{{ "view_posted_at_${key}" }}">
                        {{ $value }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <h5><span class="badge badge-secondary">重要記事の扱い</span></h5>

    {{-- 重要記事の扱い --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} @if(!$frame->isExpandNarrow()) pt-sm-0 @endif">重要記事の扱い</label><br />
        <div class="{{$frame->getSettingInputClass()}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if($whatsnew->important == "")
                    <input type="radio" value="" id="important_0" name="important" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="important_0" name="important" class="custom-control-input">
                @endif
                <label class="custom-control-label text-nowrap" for="important_0">区別しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($whatsnew->important == "top")
                    <input type="radio" value="top" id="important_1" name="important" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="top" id="important_1" name="important" class="custom-control-input">
                @endif
                <label class="custom-control-label text-nowrap" for="important_1">上に表示する</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($whatsnew->important == "important_only")
                    <input type="radio" value="important_only" id="important_2" name="important" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="important_only" id="important_2" name="important" class="custom-control-input">
                @endif
                <label class="custom-control-label text-nowrap" for="important_2">重要記事のみ表示する</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($whatsnew->important == "not_important")
                    <input type="radio" value="not_important" id="important_3" name="important" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="not_important" id="important_3" name="important" class="custom-control-input">
                @endif
                <label class="custom-control-label text-nowrap" for="important_3">重要記事を表示しない</label>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">もっと見る機能</span></h5>

    {{-- もっと見るボタンの表示 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">ボタンの表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            @foreach (ShowType::enum as $key => $value)
                <div class="custom-control custom-radio custom-control-inline">
                    <input 
                        type="radio" 
                        value="{{ $key }}" 
                        id="{{ "read_more_use_flag_${key}" }}"
                        name="read_more_use_flag" 
                        class="custom-control-input" 
                        {{ $whatsnew->read_more_use_flag == $key ? 'checked' : '' }}
                    >
                    <label class="custom-control-label" for="{{ "read_more_use_flag_${key}" }}">
                        {{ $value }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>
    
    {{-- もっと見る取得件数／回 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">ボタン押下時の<br>取得件数／回</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="read_more_fetch_count" value="{{old('read_more_fetch_count', $whatsnew->read_more_fetch_count ? $whatsnew->read_more_fetch_count : 5)}}" class="form-control col-sm-3">
            @if ($errors && $errors->has('read_more_fetch_count')) <div class="text-danger">{{$errors->first('read_more_fetch_count')}}</div> @endif
        </div>
    </div>

    {{-- もっと見るボタン名 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">ボタン名</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="read_more_name" value="{{old('read_more_name', $whatsnew->read_more_name ? $whatsnew->read_more_name : 'もっと見る')}}" class="form-control">
            @if ($errors && $errors->has('read_more_name')) <div class="text-danger">{{$errors->first('read_more_name')}}</div> @endif
        </div>
    </div>

    {{-- もっと見るボタン色 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">ボタン色</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <select class="form-control" name="read_more_btn_color_type">
                @foreach (Bs4Color::getMembers() as $key=>$value)
                    <option value="{{$key}}" class="{{ 'text-' . $key }}" @if($key == old('read_more_btn_color_type', $whatsnew->read_more_btn_color_type)) selected @endif>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- もっと見るボタンの形 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">ボタンの形</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <select class="form-control" name="read_more_btn_type">
                @foreach (RadiusType::getMembers() as $key=>$value)
                    <option value="{{$key}}" @if($key == old('read_more_btn_type', $whatsnew->read_more_btn_type)) selected @endif>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- もっと見るボタン透過設定 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">ボタン透過の使用</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            @foreach (UseType::enum as $key => $value)
                <div class="custom-control custom-radio custom-control-inline">
                    <input 
                        type="radio" 
                        value="{{ $key }}" 
                        id="{{ "read_more_btn_transparent_flag_${key}" }}"
                        name="read_more_btn_transparent_flag" 
                        class="custom-control-input" 
                        {{ $whatsnew->read_more_btn_transparent_flag == $key ? 'checked' : '' }}
                    >
                    <label class="custom-control-label" for="{{ "read_more_btn_transparent_flag_${key}" }}">
                        {{ $value }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>
    
    <h5><span class="badge badge-secondary">表示対象プラグイン・フレーム</span></h5>

    {{-- 対象プラグイン --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}} pt-0">対象プラグイン <label class="badge badge-danger mb-0">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            @foreach($whatsnew->getTargetPlugins() as $target_plugin => $use_flag)
                <div class="custom-control custom-checkbox custom-control-inline">
                    <input type="checkbox" name="target_plugin[{{$target_plugin}}]" value="{{$target_plugin}}" class="custom-control-input" id="target_plugin_{{$target_plugin}}" @if(old("target_plugin.$target_plugin", $use_flag)) checked=checked @endif>
                    <label class="custom-control-label" for="target_plugin_{{$target_plugin}}">{{$target_plugin}}</label>
                </div>
            @endforeach
            @if ($errors && $errors->has('target_plugin')) <div class="text-danger float-none">{{$errors->first('target_plugin')}}</div> @endif
        </div>
    </div>

    {{-- フレームの選択 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">フレームの選択</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                <input
                    type="radio"
                    value="0"
                    id="frame_select_0"
                    name="frame_select"
                    class="custom-control-input"
                    {{ $whatsnew->frame_select == 0 ? 'checked' : '' }}
                    v-on:click="setDisabledTargetFrame(0)"
                >
                <label class="custom-control-label" for="frame_select_0">全て表示する</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input
                    type="radio"
                    value="1"
                    id="frame_select_1"
                    name="frame_select"
                    class="custom-control-input"
                    {{ $whatsnew->frame_select == 1 ? 'checked' : '' }}
                    v-on:click="setDisabledTargetFrame(1)"
                >
                <label class="custom-control-label" for="frame_select_1">選択したものだけ表示する</label>
            </div>
        </div>
    </div>

    {{-- 対象ページ - フレーム --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">対象ページ - フレーム</label>
        <div class="{{$frame->getSettingInputClass(false, true)}}">
            <ul class="nav nav-pills" role="tablist">
                @foreach(WhatsnewsTargetPlugin::getMembers() as $target_plugin => $target_plugin_full)
                    {{--
                    <li class="nav-item">
                        <a href="#blogs{{frame->id}}" class="nav-link active" data-toggle="tab" role="tab">ブログ</a>
                    </li>
                    --}}
                    <li class="nav-item">
                        <a href="#{{$target_plugin}}{{$frame->id}}" class="nav-link @if($loop->first) active @endif" data-toggle="tab" role="tab">{{$target_plugin_full}}</a>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content">
                @foreach(WhatsnewsTargetPlugin::getMembers() as $target_plugin => $target_plugin_full)
                    <div id="{{$target_plugin}}{{$frame->id}}" class="tab-pane card @if($loop->first) active @endif" role="tabpanel">
                        <div class="card-body py-2 pl-3">
                            @foreach($target_plugins_frames as $target_plugins_frame)
                                @if ($target_plugins_frame->plugin_name == $target_plugin)
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="target_frame_ids[{{$target_plugins_frame->id}}]" value="{{$target_plugins_frame->id}}" class="custom-control-input" id="target_plugins_frame_{{$target_plugins_frame->id}}" @if(old("target_frame_ids.$target_plugins_frame->id", $whatsnew->isTargetFrame($target_plugins_frame->id))) checked=checked @endif>
                                        <label class="custom-control-label" for="target_plugins_frame_{{$target_plugins_frame->id}}">{{$target_plugins_frame->page_name}} - {{$target_plugins_frame->frame_title}}</label>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
                @if ($errors && $errors->has('target_plugins_frames')) <div class="text-danger">{{$errors->first('target_plugins_frames')}}</div> @endif
            </div>
        </div>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span>
                </button>
                <button type="submit" class="btn btn-primary form-horizontal mr-2"><i class="fas fa-check"></i>
                    <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    @if (empty($whatsnew) || $create_flag)
                        登録
                    @else
                        変更
                    @endif
                    </span>
                </button>
            </div>
            {{-- 既存新着情報設定の場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
                <div class="col-3 text-right">
                    <a data-toggle="collapse" href="#collapse{{$whatsnew_frame->id}}">
                        <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                    </a>
                </div>
            @endif
        </div>
    </div>
    </div>
</form>
@endif
@endif

@if(isset($whatsnew))
<div id="collapse{{$whatsnew_frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">新着情報設定を削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/whatsnews/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$whatsnew->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<script>
    new Vue({
        el: "#app_{{ $frame->id }}",
        methods: {
            // 対象フレームのチェックボックスdisabled制御
            setDisabledTargetFrame:function(frame_select_value){
                const elms = document.querySelectorAll("[id^=target_plugins_frame]");
                for (var i = 0; i < elms.length; i++) {
                    // disabledでチェックボックスの選択状態がクリアされてしまうが、再選択時に意識的に選択してもらう
                    elms[i].disabled = frame_select_value == '0' ? true : false;
                }
            }
        },
        // 初期表示にvueメソッドをcallする
        mounted: function(){
            this.setDisabledTargetFrame({{ $whatsnew->frame_select }});
        }
    })
</script>

@endsection
