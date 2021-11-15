{{--
 * フレーム表示設定編集画面テンプレート。
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.photoalbums.photoalbums_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

@if (empty($photoalbum->id) && $action != 'createBuckets')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        選択画面から、使用するフォトアルバムを選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i>
        フレームごとの表示設定を変更します。
    </div>

    <form action="{{url('/')}}/redirect/plugin/photoalbums/saveView/{{$page->id}}/{{$frame_id}}/{{$photoalbum->id}}#frame-{{$frame->id}}" method="POST" class="">
        {{ csrf_field() }}
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/photoalbums/editView/{{$page->id}}/{{$frame_id}}/{{$photoalbum->bucket_id}}#frame-{{$frame_id}}">

        {{-- 1ページの表示件数 --}}
        {{-- 現時点では、データ読み込み後にソートしているので、ページングする際は、ソートロジックも見直してから。
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::view_count]}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                <input type="text" name="view_count" value="{{ FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::view_count, 10)}}" class="form-control">
            </div>
        </div>
        --}}
        {{-- 投稿日 --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::posted_at]}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach (ShowType::enum as $key => $value)
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                            type="radio"
                            value="{{ $key }}"
                            id="{{ "posted_at_${key}" }}"
                            name="posted_at"
                            class="custom-control-input"
                            {{ FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::posted_at, 0) == $key ? 'checked' : '' }}
                        >
                        <label class="custom-control-label" for="{{ "posted_at_${key}" }}">
                            {{ $value }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
        {{-- 撮影日 --}}
        {{-- 現時点では、テストなど実施しきれないので、今後へ。
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::shooting_at]}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach (ShowType::enum as $key => $value)
                    <div class="custom-control custom-radio custom-control-inline">
                        <input
                            type="radio"
                            value="{{ $key }}"
                            id="{{ "shooting_at_${key}" }}"
                            name="shooting_at"
                            class="custom-control-input"
                            {{ FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::shooting_at, 0) == $key ? 'checked' : '' }}
                        >
                        <label class="custom-control-label" for="{{ "shooting_at_${key}" }}">
                            {{ $value }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
        --}}
        {{-- アルバム並び順 --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::sort_folder]}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                <select class="form-control" name="sort_folder">
                    @foreach (PhotoalbumSort::getMembers() as $sort_key => $sort_view)
                        {{-- 未設定時の初期値 --}}
                        @if ($sort_key == PhotoalbumSort::name_asc && FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::sort_folder) == '')
                            <option value="{{$sort_key}}" selected>{{  $sort_view  }}</option>
                        @else
                            <option value="{{$sort_key}}" @if(FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::sort_folder) == $sort_key) selected @endif>{{  $sort_view  }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
        {{-- 写真並び順 --}}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">{{PhotoalbumFrameConfig::enum[PhotoalbumFrameConfig::sort_file]}}</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                <select class="form-control" name="sort_file">
                    @foreach (PhotoalbumSort::getMembers() as $sort_key => $sort_view)
                        {{-- 未設定時の初期値 --}}
                        @if ($sort_key == PhotoalbumSort::name_asc && FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::sort_file) == '')
                            <option value="{{$sort_key}}" selected>{{  $sort_view  }}</option>
                        @else
                            <option value="{{$sort_key}}" @if(FrameConfig::getConfigValueAndOld($frame_configs, PhotoalbumFrameConfig::sort_file) == $sort_key) selected @endif>{{  $sort_view  }}</option>
                        @endif
                    @endforeach
                </select>
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
