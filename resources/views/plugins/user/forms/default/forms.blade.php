{{--
 * 登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@include('plugins.common.errors_form_line')

{{-- エラーメッセージ：publicStoreToken()リダイレクト時 --}}
@if (session('error_messages' . $frame_id))
    <div class="card border-danger form-group">
        <div class="card-body">
            <span class="text-danger">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error_messages' . $frame_id) }}
            </span>
        </div>
    </div>
@endif

@if ($form->can_view_inputs_moderator)
    @can('role_article')
        <div class="row">
            <p class="text-right col">
                {{-- 集計結果ボタン --}}
                <a href="{{url('/')}}/plugin/forms/aggregate/{{$page->id}}/{{$frame_id}}/{{$form->id}}#frame-{{$frame->id}}" class="btn btn-success"><i class="fas fa-list"></i> 集計結果</a>
            </p>
        </div>
    @endcan
@endif

<form action="{{URL::to('/')}}/plugin/forms/publicConfirm/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="form_add_column{{$frame_id}}" method="POST" class="form-horizontal" aria-label="{{$form->forms_name}}" enctype="multipart/form-data">
    <fieldset>
        <legend class="sr-only">{{$form->forms_name}}</legend>
        {{ csrf_field() }}

        @foreach($forms_columns as $form_column)
            <div class="form-group row">

                @switch($form_column->column_type)
                    @case(FormColumnType::group)
                        @if (isset($is_template_label_sm_4))
                            {{-- label-sm-4テンプレート --}}
                            <label class="col-sm-4 control-label">{{$form_column->column_name}} @if ($form_column->required)<strong class="{{ App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger lead' }}">{{__('messages.required')}}</strong> @endif</label>
                        @elseif (isset($is_template_label_sm_6))
                            {{-- label-sm-6テンプレート --}}
                            <label class="col-sm-6 control-label">{{$form_column->column_name}} @if ($form_column->required)<strong class="{{ App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger' }}">{{__('messages.required')}}</strong> @endif</label>
                        @else
                            {{-- defaultテンプレート --}}
                            <label class="col-sm-2 control-label">{{$form_column->column_name}} @if ($form_column->required)<strong class="{{ App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger' }}">{{__('messages.required')}}</strong> @endif</label>
                        @endif

                        @php
                            // グループカラムの幅の計算
                            $col_count = floor(12/count($form_column->group));
                            if ($col_count < 3) {
                                $col_count = 3;
                            }
                        @endphp
                        <div class="col-sm pr-0">
                            <div class="container-fluid row p-0">
                                @foreach($form_column->group as $group_row)
                                    @if ($group_row->column_type == FormColumnType::group)
                                        {{-- まとめ行2重設定エラー --}}
                                        @include('plugins.user.forms.default.include_error_multiple_group')
                                        @continue
                                    @endif

                                    {{-- 項目名。ラジオとチェックボックスは選択肢にラベルを使っているため、項目名のラベルにforを付けない。
                                        時間FromToは入力項目のtitleで項目説明しているため、項目名のラベルにforを付けない。--}}
                                    @if ($group_row->column_type == 'radio' || $group_row->column_type == 'checkbox')
                                        <div class="col-sm-{{$col_count}} pl-0">
                                        <label class="control-label" style="vertical-align: top; padding-left: 16px; padding-top: 8px;">{{$group_row->column_name}}</label>
                                    @elseif ($group_row->column_type == 'time_from_to')
                                        <div class="col-sm-{{$col_count}} pr-0">
                                        <label class="control-label">{{$group_row->column_name}}</label>
                                    @else
                                        <div class="col-sm-{{$col_count}} pr-0">
                                        <label class="control-label" for="column-{{$group_row->id}}-{{$frame_id}}">{{$group_row->column_name}}</label>
                                    @endif

                                    {{-- 必須 --}}
                                    @if (isset($is_template_label_sm_4))
                                        {{-- label-sm-4テンプレート --}}
                                        @if ($group_row->required)<strong class="{{ App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger lead' }}">{{__('messages.required')}}</strong> @endif
                                    @elseif (isset($is_template_label_sm_6))
                                        {{-- label-sm-6テンプレート --}}
                                        @if ($group_row->required)<strong class="{{ App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger' }}">{{__('messages.required')}}</strong> @endif
                                    @else
                                        {{-- defaultテンプレート --}}
                                        @if ($group_row->required)<strong class="{{ App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger' }}">{{__('messages.required')}}</strong> @endif
                                    @endif

                                    {{-- 項目 ※まとめ設定行 --}}
                                    @include('plugins.user.forms.default.forms_input_' . $group_row->column_type, ['form_obj' => $group_row, 'label_id' => 'column-'.$group_row->id.'-'.$frame_id])
                                    @php
                                        $caption = nl2br($group_row->caption);
                                        $caption = str_ireplace('[[upload_max_filesize]]', ini_get('upload_max_filesize'), $caption);
                                    @endphp
                                    <div class="small {{ $group_row->caption_color }}">{!! $caption !!}</div>
                                        </div>
                                @endforeach
                            </div>
                            @php
                                $caption = nl2br($form_column->caption);
                                $caption = str_ireplace('[[upload_max_filesize]]', ini_get('upload_max_filesize'), $caption);
                            @endphp
                            <div class="small {{ $form_column->caption_color }}">{!! $caption !!}</div>
                        </div>
                        @break

                    {{-- 項目 ※まとめ未設定行 --}}
                    @default
                        @php
                            // ラジオとチェックボックスは選択肢にラベルを使っているため、項目名のラベルにforを付けない
                            // 時間FromToは入力項目のtitleで項目説明しているため、項目名のラベルにforを付けない
                            if ($form_column->column_type == 'radio' || $form_column->column_type == 'checkbox' || $form_column->column_type == 'time_from_to') {
                                $label_for = '';
                            } else {
                                $label_for = 'for=column-' . $form_column->id . '-' . $frame_id;
                            }
                        @endphp

                        @if (isset($is_template_label_sm_4))
                            {{-- label-sm-4テンプレート --}}
                            <label class="col-sm-4 control-label" {{$label_for}}>{{$form_column->column_name}} @if ($form_column->required)<strong class="{{ App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger lead' }}">{{__('messages.required')}}</strong> @endif</label>
                        @elseif (isset($is_template_label_sm_6))
                            {{-- label-sm-6テンプレート --}}
                            <label class="col-sm-6 control-label" {{$label_for}}>{{$form_column->column_name}} @if ($form_column->required)<strong class="{{ App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger' }}">{{__('messages.required')}}</strong> @endif</label>
                        @else
                            {{-- defaultテンプレート --}}
                            <label class="col-sm-2 control-label" {{$label_for}}>{{$form_column->column_name}} @if ($form_column->required)<strong class="{{ App::getLocale() == ConnectLocale::ja ? 'badge badge-danger' : 'text-danger' }}">{{__('messages.required')}}</strong> @endif</label>
                        @endif

                        <div class="col-sm">
                            @include('plugins.user.forms.default.forms_input_' . $form_column->column_type, ['form_obj' => $form_column, 'label_id' => 'column-'.$form_column->id.'-'.$frame_id])
                            @php
                                $caption = nl2br($form_column->caption);
                                $caption = str_ireplace('[[upload_max_filesize]]', ini_get('upload_max_filesize'), $caption);
                            @endphp
                            <div class="small {{ $form_column->caption_color }}">{!! $caption !!}</div>
                        </div>
                @endswitch
            </div>
        @endforeach

        {{-- Captcha フィールド --}}
        @include('plugins.user.forms.default.forms_captcha_field')

        {{-- ボタンエリア --}}
        <div class="form-group text-center">
            <button class="btn btn-primary"><i class="fa-solid fa-comment"></i> {{__('messages.to_confirm')}}</button>
        </div>
    </fieldset>
</form>
@include('plugins.user.forms.default.forms_captcha_script')
@endsection
