{{--
 * 登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
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

                    @if ($group_row->column_type == 'radio' || $group_row->column_type == 'checkbox')
                        <div class="col-sm-{{$col_count}}" style="padding-left: 0px;">
                    @else
                        <div class="col-sm-{{$col_count}} pr-0">
                    @endif

                            @if ($group_row->column_type == 'radio' || $group_row->column_type == 'checkbox')
                                <label class="control-label" style="vertical-align: top; padding-left: 16px; padding-top: 8px;">{{$group_row->column_name}}</label>
                            @else
                                <label class="control-label">{{$group_row->column_name}}</label>
                            @endif
                            @if ($group_row->required)<label class="badge badge-danger">必須</label> @endif

                            @switch($group_row->column_type)
                            @case("text")
                                @include('plugins.user.forms.default.forms_input_text',['form_obj' => $group_row])
                                @break
                            @case("textarea")
                                @include('plugins.user.forms.default.forms_input_textarea',['form_obj' => $group_row])
                                @break
                            @case("radio")
                                @include('plugins.user.forms.default.forms_input_radio',['form_obj' => $group_row])
                                @break
                            @case("checkbox")
                                @include('plugins.user.forms.default.forms_input_checkbox',['form_obj' => $group_row])
                                @break
                            @case("select")
                                @include('plugins.user.forms.default.forms_input_select',['form_obj' => $group_row])
                                @break
                            @case("mail")
                                @include('plugins.user.forms.default.forms_input_mail',['form_obj' => $group_row])
                                @break
                            @endswitch
                        </div>
                    @endforeach
                    </div>
                </div>
                @break
            @case("text")
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_text',['form_obj' => $form_column])
                </div>
                @break
            @case("textarea")
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_textarea',['form_obj' => $form_column])
                </div>
                @break
            @case("radio")
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_radio',['form_obj' => $form_column])
                </div>
                @break
            @case("checkbox")
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_checkbox',['form_obj' => $form_column])
                </div>
                @break
            @case("select")
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_select',['form_obj' => $form_column])
                </div>
                @break
            @case("mail")
                <div class="col-sm-8">
                    @include('plugins.user.forms.default.forms_input_mail',['form_obj' => $form_column])
                </div>
                @break
            @endswitch
        </div>{{-- /form-group --}}
        @endforeach
        <div class="form-group text-center">
            <button class="btn btn-primary"><i class="fab fa-facebook-messenger"></i> 確認画面へ</button>
        </div>
    </form>

@else
    フォームが定義されていません。
@endif
