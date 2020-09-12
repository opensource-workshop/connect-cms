{{--
 * 表示設定画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
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
<form action="{{url('/')}}/plugin/databases/saveView/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- 表示件数 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">表示件数 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="view_count" value="{{old('view_count', $view_frame->view_count)}}" class="form-control">
            @if ($errors && $errors->has('view_count')) <div class="text-danger">{{$errors->first('view_count')}}</div> @endif
        </div>
    </div>

    {{-- 検索機能の表示 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">検索機能の表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="1" id="use_search_flag_1" name="use_search_flag" class="custom-control-input" @if(old('use_search_flag', $view_frame->use_search_flag) == 1) checked="checked" @endif>
                <label class="custom-control-label" for="use_search_flag_1">表示する</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="0" id="use_search_flag_0" name="use_search_flag" class="custom-control-input" @if(old('use_search_flag', $view_frame->use_search_flag) == 0) checked="checked" @endif>
                <label class="custom-control-label" for="use_search_flag_0">表示しない</label>
            </div>
        </div>
    </div>

    {{-- 絞り込み機能の表示 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">絞り込み機能の表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="1" id="use_select_flag_1" name="use_select_flag" class="custom-control-input" @if(old('use_select_flag', $view_frame->use_select_flag) == 1) checked="checked" @endif>
                <label class="custom-control-label" for="use_select_flag_1">表示する</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="0" id="use_select_flag_0" name="use_select_flag" class="custom-control-input" @if(old('use_select_flag', $view_frame->use_select_flag) == 0) checked="checked" @endif>
                <label class="custom-control-label" for="use_select_flag_0">表示しない</label>
            </div>
        </div>
    </div>

    {{-- 並べ替え項目の表示 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">並べ替え項目の表示</label>
        <div class="{{$frame->getSettingInputClass(false)}}">
            @foreach (DatabaseSortFlag::getDisplaySortFlags() as $sort_key => $sort_view)
                {{--
                <div class="custom-control custom-checkbox custom-control-inline">
                    <input type="checkbox" name="use_sort_flag[created_asc]" value="created_asc" class="custom-control-input" id="use_sort_flag_created_asc" @ if(old('use_sort_flag.created_asc', $ view_frame->isUseSortFlag('created_asc'))) checked=checked @ endif>
                    <label class="custom-control-label" for="use_sort_flag_created_asc">登録日（古い順）</label>
                </div>
                --}}
                <div class="custom-control custom-checkbox custom-control-inline">
                    <input type="checkbox" name="use_sort_flag[{{$sort_key}}]" value="{{$sort_key}}" class="custom-control-input" id="use_sort_flag_{{$sort_key}}" @if(old('use_sort_flag.' . $sort_key, $view_frame->isUseSortFlag($sort_key))) checked=checked @endif>
                    <label class="custom-control-label" for="use_sort_flag_{{$sort_key}}">{{  $sort_view  }}</label>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 初期表示での並び順 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">初期表示での並び順</label>

        <div class="{{$frame->getSettingInputClass(true)}}">
            <select class="form-control" name="default_sort_flag">

            @php
            $default_sort_flag = old('default_sort_flag', $view_frame->default_sort_flag);
            @endphp

            <optgroup label="基本設定">
                <option value="">指定なし</option>
                @foreach (DatabaseSortFlag::getSortFlags() as $sort_key => $sort_view)
                    {{-- <option value="created_asc" @ if($default_sort_flag == 'created_asc') selected @ endif>登録日（古い順）</option> --}}
                    <option value="{{$sort_key}}" @if($default_sort_flag == $sort_key) selected @endif>{{  $sort_view  }}</option>
                @endforeach
            </optgroup>
            <optgroup label="各カラム設定">
                {{-- 1:昇順＆降順、2:昇順のみ、3:降順のみ --}}
                @foreach($columns->whereIn('sort_flag', [1, 2, 3]) as $sort_column)

                    @if($sort_column->sort_flag == 1 || $sort_column->sort_flag == 2)
                        <option value="{{$sort_column->id}}_asc" @if(($sort_column->id . '_asc') == $default_sort_flag) selected @endif>{{$sort_column->column_name}}(昇順)</option>
                    @endif

                    @if($sort_column->sort_flag == 1 || $sort_column->sort_flag == 3)
                        <option value="{{$sort_column->id}}_desc" @if(($sort_column->id . '_desc') == $default_sort_flag) selected @endif>{{$sort_column->column_name}}(降順)</option>
                    @endif
                @endforeach
            </optgroup>
            </select>
        </div>
    </div>

    {{-- 初期表示で一覧表示しない --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">初期表示での一覧表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="0" id="default_hide_0" name="default_hide" class="custom-control-input" @if(old('default_hide', $view_frame->default_hide) == 0) checked="checked" @endif>
                <label class="custom-control-label" for="default_hide_0">表示する</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="1" id="default_hide_1" name="default_hide" class="custom-control-input" @if(old('default_hide', $view_frame->default_hide) == 1) checked="checked" @endif>
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
                    @if (empty($view_frame->id))
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
