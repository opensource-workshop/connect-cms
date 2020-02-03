{{--
 * 登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($form && $forms_columns)

    <form action="{{URL::to('/')}}/plugin/forms/publicConfirm/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="form_add_column{{$frame_id}}" method="POST" class="form-horizontal">
        {{ csrf_field() }}

{{--
<input name="test_value[0]" class="form-control" type="text" value="{{old('test_value.0', 'default1')}}">
<input name="test_value[1]" class="form-control" type="text" value="{{old('test_value.1', 'default2')}}">
--}}

        @foreach($forms_columns as $form_column)
        <div class="form-group row">
            <label class="col-sm-4 control-label">{{$form_column->column_name}} @if ($form_column->required)<label class="badge badge-danger">必須</label> @endif</label>
            @switch($form_column->column_type)
            @case("group")
                @php
                    // グループカラムの幅の計算
                    $col_count = floor(12/count($form_column->group));
                    if ($col_count < 3) {
                        $col_count = 3;
                    }
                @endphp
                <div class="col-sm-8 pr-0">
                <div class="container-fluid row" style="padding: 0;">
                @foreach($form_column->group as $group_row)

                    {{-- 項目名 --}}
                    @if ($group_row->column_type == 'radio' || $group_row->column_type == 'checkbox')
                        <div class="col-sm-{{$col_count}}" style="padding-left: 0px;">
                        <label class="control-label" style="vertical-align: top; padding-left: 16px; padding-top: 8px;">{{$group_row->column_name}}</label>
                    @else
                        <div class="col-sm-{{$col_count}} pr-0">
                        <label class="control-label">{{$group_row->column_name}}</label>
                    @endif

                    {{-- 必須 --}}
                    @if ($group_row->required)<label class="badge badge-danger">必須</label> @endif

                    {{-- 項目 ※まとめ設定行 --}}
                    @switch($group_row->column_type)
                        @case(FormColumnType::text)
                            @include('plugins.user.forms.default.forms_input_text',['form_obj' => $group_row])
                            @break
                        @case(FormColumnType::textarea)
                            @include('plugins.user.forms.default.forms_input_textarea',['form_obj' => $group_row])
                            @break
                        @case(FormColumnType::radio)
                            @include('plugins.user.forms.default.forms_input_radio',['form_obj' => $group_row])
                            @break
                        @case(FormColumnType::checkbox)
                            @include('plugins.user.forms.default.forms_input_checkbox',['form_obj' => $group_row])
                            @break
                        @case(FormColumnType::select)
                            @include('plugins.user.forms.default.forms_input_select',['form_obj' => $group_row])
                            @break
                        @case(FormColumnType::mail)
                            @include('plugins.user.forms.default.forms_input_mail',['form_obj' => $group_row])
                            @break
                        @case(FormColumnType::date)
                            @include('plugins.user.forms.default.forms_input_date',['form_obj' => $group_row])
                            @break
                        @case(FormColumnType::time)
                            @include('plugins.user.forms.default.forms_input_time',['form_obj' => $group_row])
                            @break
                    @endswitch
                        </div>
                @endforeach
                    </div>
                </div>
                @break
            {{-- 項目 ※まとめ未設定行 --}}
            @case(FormColumnType::text)
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_text',['form_obj' => $form_column])
                </div>
                @break
            @case(FormColumnType::textarea)
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_textarea',['form_obj' => $form_column])
                </div>
                @break
            @case(FormColumnType::radio)
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_radio',['form_obj' => $form_column])
                </div>
                @break
            @case(FormColumnType::checkbox)
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_checkbox',['form_obj' => $form_column])
                </div>
                @break
            @case(FormColumnType::select)
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_select',['form_obj' => $form_column])
                </div>
                @break
            @case(FormColumnType::mail)
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_mail',['form_obj' => $form_column])
                </div>
                @break
            @case(FormColumnType::date)
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_date',['form_obj' => $form_column])
                </div>
                @break
            @case(FormColumnType::time)
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_time',['form_obj' => $form_column])
                </div>
                @break
            @endswitch
        </div>
        @endforeach
        {{-- ボタンエリア --}}
        <div class="form-group text-center">
            <button class="btn btn-primary"><i class="fab fa-facebook-messenger"></i> 確認画面へ</button>
        </div>
    </form>

@else
    {{-- フレームに紐づくコンテンツがない場合、データ登録を促すメッセージを表示 --}}
    <div class="card border-danger">
        <div class="card-body">
            {{-- フレームに紐づく親データがない場合 --}}
            @if (!$form)
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用するフォームを選択するか、作成してください。</p>
            @endif
            {{-- 項目データがない場合 --}}
            @if (!$forms_columns)
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、項目データを作成してください。</p>
            @endif
        </div>
    </div>
@endif
@endsection
