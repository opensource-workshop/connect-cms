{{--
 * 表示設定画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベースプラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.databases.databases_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

@if ($errors->any())
    <div class="alert alert-danger mt-2">
        @foreach ($errors->all() as $error)
            <i class="fas fa-exclamation-circle"></i>
            {{$error}}
        @endforeach
    </div>
@else
    @if (!$database->id)
        <div class="alert alert-warning mt-2">
            <i class="fas fa-exclamation-circle"></i>
            データベース選択画面から選択するか、データベース新規作成で作成してください。
        </div>
    @else
        <div class="alert alert-info mt-2">
            <i class="fas fa-exclamation-circle"></i>
            表示設定を変更します。
        </div>
    @endif
@endif

@if (!$database->id)
@else
<form action="/plugin/databases/saveView/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- 表示件数 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">表示件数 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="view_count" value="{{old('view_count', $database_frame->view_count)}}" class="form-control">
            @if ($errors && $errors->has('view_count')) <div class="text-danger">{{$errors->first('view_count')}}</div> @endif
        </div>
    </div>

    {{-- 検索機能の表示 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">検索機能の表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if($database_frame->use_search_flag == 1)
                    <input type="radio" value="1" id="use_search_flag_1" name="use_search_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_search_flag_1" name="use_search_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_search_flag_1">表示する</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($database_frame->use_search_flag == 0)
                    <input type="radio" value="0" id="use_search_flag_0" name="use_search_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_search_flag_0" name="use_search_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_search_flag_0">表示しない</label>
            </div>
        </div>
    </div>

    {{-- 絞り込み機能の表示 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">絞り込み機能の表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if($database_frame->use_select_flag == 1)
                    <input type="radio" value="1" id="use_select_flag_1" name="use_select_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_select_flag_1" name="use_select_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_select_flag_1">表示する</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($database_frame->use_select_flag == 0)
                    <input type="radio" value="0" id="use_select_flag_0" name="use_select_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_select_flag_0" name="use_select_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_select_flag_0">表示しない</label>
            </div>
        </div>
    </div>

    {{-- 並べ替え機能の表示 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">並べ替え機能の表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if($database_frame->use_sort_flag == 1)
                    <input type="radio" value="1" id="use_sort_flag_1" name="use_sort_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_sort_flag_1" name="use_sort_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_sort_flag_1">表示する</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($database_frame->use_sort_flag == 0)
                    <input type="radio" value="0" id="use_sort_flag_0" name="use_sort_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_sort_flag_0" name="use_sort_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_sort_flag_0">表示しない</label>
            </div>
        </div>
    </div>

    {{-- 初期表示で一覧表示しない --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">初期表示での一覧表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if($database_frame->default_hide == 0)
                    <input type="radio" value="0" id="default_hide_0" name="default_hide" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="default_hide_0" name="default_hide" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="default_hide_0">表示する</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($database_frame->default_hide == 1)
                    <input type="radio" value="1" id="default_hide_1" name="default_hide" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="default_hide_1" name="default_hide" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="default_hide_1">表示しない</label>
            </div>
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
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 
                    <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    @if (empty($database_frame->databases_frames_id))
                        登録確定
                    @else
                        変更確定
                    @endif
                    </span>
                </button>
            </div>
        </div>
    </div>
</form>

@endif
@endsection
