{{--
 * 登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
@if ($form && $forms_columns)

    <form action="{{URL::to('/')}}/plugin/forms/confirm/{{$page->id}}/{{$frame_id}}#{{$frame_id}}" name="form_add_column{{$frame_id}}" method="POST" class="form-horizontal">
        {{ csrf_field() }}

{{--
<input name="test_value[0]" class="form-control" type="text" value="{{old('test_value.0', 'default1')}}">
<input name="test_value[1]" class="form-control" type="text" value="{{old('test_value.1', 'default2')}}">
--}}

        @foreach($forms_columns as $form_column)
        <div class="form-group">
            <label class="col-sm-2 control-label">@if ($form_column->required)<label class="label label-danger">必須</label> @endif{{$form_column->column_name}}</label>
            @switch($form_column->column_type)
            @case("group")
                @php
                    // グループカラムの幅の計算
                    $col_count = floor(10/count($form_column->group));
                @endphp
                @foreach($form_column->group as $group_row)
                    <div class="col-sm-{{$col_count}}">
                        @if ($group_row->required)<label class="label label-danger">必須</label> @endif
                        <label class="control-label" style="vertical-align: top;">{{$group_row->column_name}}</label>
                        <input name="forms_columns_value[{{$group_row->id}}]" class="form-control" type="{{$group_row->column_type}}" value="{{old('forms_columns_value.'.$group_row->id, $request->forms_columns_value[$group_row->id])}}" />
                        @if ($errors && $errors->has("forms_columns_value.$group_row->id"))
                            <div class="text-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> {{$errors->first("forms_columns_value.$group_row->id")}}</div>
                        @endif
                    </div>
                @endforeach
                @break
            @case("text")
                <div class="col-sm-10">
                <input name="forms_columns_value[{{$form_column->id}}]" class="form-control" type="{{$form_column->column_type}}" value="{{old('forms_columns_value.'.$form_column->id, $request->forms_columns_value[$form_column->id])}}">
                    @if ($errors && $errors->has("forms_columns_value.$form_column->id"))
                        <div class="text-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> {{$errors->first("forms_columns_value.$form_column->id")}}</div>
                    @endif
                </div>
                @break
            @case("textarea")
                <div class="col-sm-10">
                    <textarea name="forms_columns_value[{{$form_column->id}}]" class="form-control">{{old('forms_columns_value.'.$form_column->id, $request->forms_columns_value[$form_column->id])}}</textarea>
                    @if ($errors && $errors->has("forms_columns_value.$form_column->id"))
                        <div class="text-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> {{$errors->first("forms_columns_value.$form_column->id")}}</div>
                    @endif
                </div>
                @break
            @case("radio")
                @if (array_key_exists($form_column->id, $forms_columns_id_select))
                    @php
                        // グループカラムの幅の計算
                        $col_count = floor(12/count($forms_columns_id_select[$form_column->id]));
                        if ($col_count < 3) {
                            $col_count = 3;
                        }
                    @endphp
                    <div class="col-sm-10" style="padding-left: 0; padding-right: 0;">
                    <div class="container-fluid" style="padding: 0;">
                        @foreach($forms_columns_id_select[$form_column->id] as $select)

                        <div class="col-sm-{{$col_count}}">
                            <label class="cc_label_input_group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        @if (old('forms_columns_value.'.$form_column->id) == $select['value'] ||
                                                 (isset($request->forms_columns_value) &&
                                                  array_key_exists($form_column->id, $request->forms_columns_value) &&
                                                  $request->forms_columns_value[$form_column->id] == $select['value'])
                                            )
                                            <input name="forms_columns_value[{{$form_column->id}}]" value="{{$select['value']}}" type="{{$form_column->column_type}}" checked>
                                        @else
                                            <input name="forms_columns_value[{{$form_column->id}}]" value="{{$select['value']}}" type="{{$form_column->column_type}}">
                                        @endif
                                    </span>
                                    <span class="form-control" style="height: auto;"> {{$select['value']}}</span>
                                </div>
                            </label>
                        </div>
                        @endforeach
                        @if ($errors && $errors->has("forms_columns_value.$form_column->id"))
                            <div class="text-danger" style="padding-left: 15px;">
                                <span class="glyphicon glyphicon-exclamation-sign"></span> {{$errors->first("forms_columns_value.$form_column->id")}}
                            </div>
                        @endif
                    </div>
                </div>
                @endif
                @break
            @case("checkbox")
                @if (array_key_exists($form_column->id, $forms_columns_id_select))
                    @php
                        // グループカラムの幅の計算
                        $col_count = floor(12/count($forms_columns_id_select[$form_column->id]));
                        if ($col_count < 3) {
                            $col_count = 3;
                        }
                    @endphp
                    <div class="col-sm-10" style="padding-left: 0; padding-right: 0;">
                    <div class="container-fluid" style="padding: 0;">
                        @foreach($forms_columns_id_select[$form_column->id] as $select)
                        <div class="col-sm-{{$col_count}}">
                            <label class="cc_label_input_group">
                                <div class="input-group">
                                    <span class="input-group-addon">

                                        @php
                                        // チェック用変数
                                        $column_checkbox_checked = "";

                                        // old でチェックされていたもの
                                        if (!empty(old('forms_columns_value.'.$form_column->id))) {
                                            foreach(old('forms_columns_value.'.$form_column->id) as $old_value) {
                                                if ( $old_value == $select['value'] ) {
                                                    $column_checkbox_checked = " checked";
                                                }
                                            }
                                        }

                                        // 画面が戻ってきたもの
                                        if (isset($request->forms_columns_value) &&
                                            array_key_exists($form_column->id, $request->forms_columns_value)) {

                                            foreach($request->forms_columns_value[$form_column->id] as $request_value) {
                                                if ( $request_value == $select['value'] ) {
                                                    $column_checkbox_checked = " checked";
                                                }
                                            }
                                        }
                                        @endphp

                                        <input name="forms_columns_value[{{$form_column->id}}][]" value="{{$select['value']}}" type="{{$form_column->column_type}}"{{$column_checkbox_checked}}>
                                    </span>
                                    <span class="form-control" style="height: auto;"> {{$select['value']}}</span>
                                </div>
                            </label>
                        </div>
                        @endforeach
                        @if ($errors && $errors->has("forms_columns_value.$form_column->id"))
                            <div class="text-danger" style="padding-left: 15px;">
                                <span class="glyphicon glyphicon-exclamation-sign"></span> {{$errors->first("forms_columns_value.$form_column->id")}}
                            </div>
                        @endif
                    </div>
                </div>
                @endif
                @break
            @endswitch
        </div>{{-- /form-group --}}
        @endforeach
        <div class="form-group text-center">
            <button class="btn btn-primary"><span class="glyphicon glyphicon-pencil"></span> 確認画面へ</button>
        </div>
    </form>

@else
    フォームが定義されていません。
@endif
