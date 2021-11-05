{{--
 * フレーム表示設定編集画面テンプレート。
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.whatsnews.whatsnews_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

@if (empty($whatsnew->id) && $action != 'createBuckets')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        選択画面から、使用する新着情報を選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i>
        フレームごとの表示設定を変更します。
    </div>

    <form action="{{url('/')}}/redirect/plugin/whatsnews/saveView/{{$page->id}}/{{$frame_id}}/{{$whatsnew->id}}#frame-{{$frame->id}}" method="POST" class="">
        {{ csrf_field() }}
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/whatsnews/editView/{{$page->id}}/{{$frame_id}}/{{$whatsnew->bucket_id}}#frame-{{$frame_id}}">

        {{-- 本文 --}}
        <h5><span class="badge badge-secondary">本文</span></h5>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">本文</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach (ShowType::enum as $key => $value)
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                            type="radio"
                            value="{{ $key }}"
                            id="{{ "post_detail_${key}" }}"
                            name="post_detail"
                            class="custom-control-input"
                            {{ FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::post_detail) == $key ? 'checked' : '' }}
                        >
                        <label class="custom-control-label" for="{{ "post_detail_${key}" }}">
                            {{ $value }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 本文の表示文字数 --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">本文の表示文字数</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="post_detail_length" value="{{ FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::post_detail_length) }}" class="form-control col-sm-3">
                @if ($errors && $errors->has('post_detail_length')) <div class="text-danger">{{$errors->first('post_detail_length')}}</div> @endif
                <small class="text-muted">※ 0の場合、全文が表示されます。</small>
            </div>
        </div>

        {{-- サムネイル --}}
        <h5><span class="badge badge-secondary">サムネイル画像</span></h5>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">サムネイル画像</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach (ShowType::enum as $key => $value)
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                            type="radio"
                            value="{{ $key }}"
                            id="{{ "thumbnail_${key}" }}"
                            name="thumbnail"
                            class="custom-control-input"
                            {{ FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::thumbnail) == $key ? 'checked' : '' }}
                        >
                        <label class="custom-control-label" for="{{ "thumbnail_${key}" }}">
                            {{ $value }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- サムネイル画像の表示幅 --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">最大画像サイズ</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="thumbnail_size" value="{{ FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::thumbnail_size) }}" class="form-control col-sm-3">
                @if ($errors && $errors->has('thumbnail_size')) <div class="text-danger">{{$errors->first('thumbnail_size')}}</div> @endif
                <small class="text-muted">※ 縦横の長い方に適用。0の場合、200が適用されます。</small>
            </div>
        </div>

        {{-- 記事間の罫線 --}}
        <h5><span class="badge badge-secondary">記事間の罫線</span></h5>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">記事間の罫線</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach (ShowType::enum as $key => $value)
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                            type="radio"
                            value="{{ $key }}"
                            id="{{ "border_${key}" }}"
                            name="border"
                            class="custom-control-input"
                            {{ FrameConfig::getConfigValueAndOld($frame_configs, WhatsnewFrameConfig::border) == $key ? 'checked' : '' }}
                        >
                        <label class="custom-control-label" for="{{ "border_${key}" }}">
                            {{ $value }}
                        </label>
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
