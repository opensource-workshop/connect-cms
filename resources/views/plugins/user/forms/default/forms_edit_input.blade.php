{{--
 * 登録一覧からの編集画面テンプレート。
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.forms.forms_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- post先 --}}
<form action="{{url('/')}}/redirect/plugin/forms/storeInput/{{$page->id}}/{{$frame_id}}/{{$input->id}}#frame-{{$frame_id}}" method="POST">
    {{ csrf_field() }}
    {{-- post後、再表示するURL --}}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/forms/listInputs/{{$page->id}}/{{$frame_id}}/{{$form->id}}#frame-{{$frame_id}}">

    {{-- 固定項目：状態 --}}
    <div class="form-group container-fluid row">

        {{-- ラベル --}}
        @if (isset($is_template_label_sm_4))
            {{-- label-sm-4テンプレート --}}
            <label class="col-sm-4 control-label text-nowrap" for="status{{$frame_id}}">状態</label>

        @elseif (isset($is_template_label_sm_6))
            {{-- label-sm-6テンプレート --}}
            <label class="col-sm-6 control-label text-nowrap" for="status{{$frame_id}}">状態</label>

        @else
            {{-- defaultテンプレート --}}
            <label class="col-sm-2 control-label text-nowrap" for="status{{$frame_id}}">状態</label>
        @endif

        {{-- 項目 --}}
        <div class="col-sm">
            <select id="status{{$frame_id}}" name="status" class="custom-select">
                @foreach(FormStatusType::getMembers() as $status => $status_name)
                    <option value="{{$status}}" @if ($status == $input->status) selected @endif>{{$status}} : {{$status_name}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <hr>

    @foreach($forms_columns as $form_column)
    <div class="form-group container-fluid row">

        {{-- ラベル --}}
        @if (isset($is_template_label_sm_4))
            {{-- label-sm-4テンプレート --}}
            <label class="col-sm-4 control-label text-nowrap">{{$form_column->column_name}}</label>

        @elseif (isset($is_template_label_sm_6))
            {{-- label-sm-6テンプレート --}}
            <label class="col-sm-6 control-label text-nowrap">{{$form_column->column_name}}</label>

        @else
            {{-- defaultテンプレート --}}
            <label class="col-sm-2 control-label text-nowrap">{{$form_column->column_name}}</label>
        @endif

        {{-- 項目 --}}
        <div class="col-sm">
            @include('plugins.user.forms.default.forms_include_value', ['column' => $form_column])
        </div>
    </div>
    @endforeach

    {{-- ボタンエリア --}}
    <div class="form-group text-center">
        <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/listInputs/{{$page->id}}/{{$frame->id}}/{{$form->id}}#frame-{{$frame->id}}" class="mr-2">
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="d-none d-sm-inline">登録一覧へ</span></span>
        </a>

        <button type="submit" class="btn btn-primary" onclick="return confirm('変更を確定します。\nよろしいですか？')"><i class="fas fa-check"></i> 変更確定</button>
    </div>
</form>
@endsection
