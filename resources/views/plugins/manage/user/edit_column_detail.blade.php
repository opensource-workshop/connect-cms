{{--
 * 項目の選択肢設定画面
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
--}}
@php
use App\Models\Core\UsersColumns;
@endphp
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<script type="text/javascript">
    /** 選択肢追加ボタン押下 */
    function submit_add_select(btn) {
        form_selects.action = "{{url('/')}}/manage/user/addSelect";
        btn.disabled = true;
        form_selects.submit();
    }

    /** 表示順操作ボタン押下 */
    function submit_display_sequence(select_id, display_sequence, display_sequence_operation) {
        form_selects.action = "{{url('/')}}/manage/user/updateSelectSequence";
        form_selects.select_id.value = select_id;
        form_selects.display_sequence.value = display_sequence;
        form_selects.display_sequence_operation.value = display_sequence_operation;
        form_selects.submit();
    }

    /** 選択肢の更新ボタン押下 */
    function submit_update_select(select_id) {
        form_selects.action = "{{url('/')}}/manage/user/updateSelect";
        form_selects.select_id.value = select_id;
        form_selects.submit();
    }

    /** 同意内容の更新ボタン押下 */
     function submit_update_agree(select_id) {
        form_selects.action = "{{url('/')}}/manage/user/updateAgree";
        form_selects.select_id.value = select_id;
        form_selects.submit();
    }

    /** その他の設定の更新ボタン押下 */
    function submit_update_column_detail() {
        form_selects.action = "{{url('/')}}/manage/user/updateColumnDetail";
        form_selects.submit();
    }

    /** 選択肢の削除ボタン押下 */
     function submit_delete_select(select_id) {
        if (confirm('選択肢を削除します。\nよろしいですか？')){
            form_selects.action = "{{url('/')}}/manage/user/deleteSelect";
            form_selects.select_id.value = select_id;
            form_selects.submit();
        }
        return false;
    }

    /** 組織追加ボタン押下 */
     function submit_add_section(btn) {
        form_selects.action = "{{url('/')}}/manage/user/addSection";
        btn.disabled = true;
        form_selects.submit();
    }

    /** 表示順操作ボタン押下 */
    function submit_section_display_sequence(section_id, display_sequence, display_sequence_operation) {
        form_selects.action = "{{url('/')}}/manage/user/updateSectionSequence";
        form_selects.section_id.value = section_id;
        form_selects.display_sequence.value = display_sequence;
        form_selects.display_sequence_operation.value = display_sequence_operation;
        form_selects.submit();
    }

    /** 組織の更新ボタン押下 */
    function submit_update_section(section_id) {
        form_selects.action = "{{url('/')}}/manage/user/updateSection";
        form_selects.section_id.value = section_id;
        form_selects.submit();
    }

    /** 組織の削除ボタン押下 */
     function submit_delete_section(section_id) {
        if (confirm('組織を削除します。\nよろしいですか？')){
            form_selects.action = "{{url('/')}}/manage/user/deleteSection";
            form_selects.section_id.value = section_id;
            form_selects.submit();
        }
        return false;
    }

    /** 変数名の使用の表示・非表示 */
    function change_use_variable(radio_value) {
        switch (radio_value) {
            case '1':
                $('#variable_name_div').collapse('show');
                break;
            case '0':
                $('#variable_name_div').collapse('hide');
                break;
            default:
                // 空の場合を想定
                $('#variable_name_div').collapse('hide');
        }
    }

    $(function () {
        /** ツールチップ有効化 */
        $('[data-toggle="tooltip"]').tooltip()

        /** 変数名の使用の制御radio.change */
        $('input[name="use_variable"]').change(function(){
            // 変数名の使用の表示・非表示
            change_use_variable($(this).val());
        });

    });
</script>

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.user.user_manage_tab')
    </div>
    <div class="card-body">

        <form action="" id="form_selects" name="form_selects" method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="columns_set_id" value="{{$columns_set->id}}">
            <input type="hidden" name="column_id" value="{{ $column->id }}">
            <input type="hidden" name="select_id" value="">
            <input type="hidden" name="section_id" value="">
            <input type="hidden" name="display_sequence" value="">
            <input type="hidden" name="display_sequence_operation" value="">

            {{-- 登録後メッセージ表示 --}}
            @include('plugins.common.flash_message')

            {{-- メッセージエリア --}}
            <div class="alert alert-info mt-2">
                <i class="fas fa-exclamation-circle"></i> {{ '項目【 ' . $column->column_name . ' 】の詳細設定を行います。' }}
            </div>

            {{-- エラーメッセージエリア --}}
            @if ($errors && $errors->any())
                <div class="alert alert-danger mt-2">
                    @foreach ($errors->all() as $error)
                        <i class="fas fa-exclamation-circle"></i> {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            @if (
                $column->column_type == UserColumnType::radio ||
                $column->column_type == UserColumnType::select ||
                $column->column_type == UserColumnType::checkbox
                )
                {{-- 選択肢の設定 --}}
                <div class="card form-group">
                    <h5 class="card-header">選択肢の設定</h5>
                    <div class="card-body">

                        <div class="table-responsive">

                            {{-- 選択項目の一覧 --}}
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        @if (count($selects) > 0)
                                            <th class="text-center" nowrap>表示順</th>
                                            <th class="text-center" nowrap>選択肢名</th>
                                            <th class="text-center" nowrap>更新</th>
                                            <th class="text-center" nowrap>削除</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- 更新用の行 --}}
                                    @foreach($selects as $select)
                                        <tr  @if ($select->hide_flag) class="table-secondary" @endif>
                                            {{-- 表示順操作 --}}
                                            <td class="text-center" nowrap>
                                                {{-- 上移動 --}}
                                                <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->first) disabled @endif onclick="javascript:submit_display_sequence({{ $select->id }}, {{ $select->display_sequence }}, 'up')">
                                                    <i class="fas fa-arrow-up"></i>
                                                </button>

                                                {{-- 下移動 --}}
                                                <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->last) disabled @endif onclick="javascript:submit_display_sequence({{ $select->id }}, {{ $select->display_sequence }}, 'down')">
                                                    <i class="fas fa-arrow-down"></i>
                                                </button>
                                            </td>

                                            {{-- 選択肢名 --}}
                                            <td>
                                                <input class="form-control @if ($errors && $errors->has('select_name_'.$select->id)) border-danger @endif" type="text" name="select_name_{{ $select->id }}" value="{{ old('select_name_'.$select->id, $select->value)}}">
                                            </td>

                                            {{-- 更新ボタン --}}
                                            <td class="align-middle text-center">
                                                <button
                                                    class="btn btn-primary btn-sm text-nowrap"
                                                    onclick="javascript:submit_update_select({{ $select->id }});"
                                                >
                                                    <i class="fas fa-check"></i> 更新
                                                </button>
                                            </td>

                                            {{-- 削除ボタン --}}
                                            <td class="align-middle text-center">
                                                <button
                                                    class="btn btn-danger btn-sm text-nowrap"
                                                    onclick="javascript:return submit_delete_select({{ $select->id }});"
                                                >
                                                    <i class="fas fa-trash-alt"></i> 削除
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="thead-light">
                                        <th colspan="7">【選択肢の追加行】</th>
                                    </tr>

                                    {{-- 新規登録用の行 --}}
                                    <tr>
                                        <td></td>
                                        <td>
                                            {{-- 選択肢名 --}}
                                            <input class="form-control @if ($errors && $errors->has('select_name')) border-danger @endif" type="text" name="select_name" value="{{ old('select_name') }}" placeholder="選択肢名">
                                        </td>
                                        <td class="align-middle text-center">
                                            {{-- ＋ボタン --}}
                                            <button class="btn btn-primary btn-sm text-nowrap" onclick="javascript:submit_add_select(this);"><i class="fas fa-plus"></i> 選択肢追加</button>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            @endif

            @if ($column->column_type == UserColumnType::agree)
                {{-- 同意内容の設定 --}}
                <div class="card form-group">
                    <h5 class="card-header">同意内容の設定</h5>
                    <div class="card-body">

                        {{-- 指定桁数（数値）以下を許容 --}}
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right">チェックボックスの名称 <span class="badge badge-danger">必須</span></label>
                            <div class="col-md-9 align-items-center">
                                <input type="text" name="value" value="{{old('value', $select_agree->value)}}" class="form-control" placeholder="（例）以下の内容に同意します。">
                                @include('plugins.common.errors_inline', ['name' => 'value'])
                            </div>
                        </div>

                        {{-- 同意内容 --}}
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right pt-0">同意内容 </label>
                            <div class="col-md-9 align-items-center">
                                <textarea name="agree_description" class="form-control" rows="3">{{old('agree_description', $select_agree->agree_description)}}</textarea>
                                <small class="text-muted">
                                    ※ 自動ユーザ登録時に求める同意の説明文<br />
                                </small>
                            </div>
                            @include('plugins.common.errors_inline', ['name' => 'agree_description'])
                        </div>

                        {{-- ボタンエリア --}}
                        <div class="form-group text-center">
                            <button onclick="javascript:submit_update_agree({{ $select_agree->id }});" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
                        </div>
                    </div>
                </div>
                <br>
            @endif

            @if ($column->column_type == UserColumnType::text || $column->column_type == UserColumnType::textarea || $column->column_type == UserColumnType::mail)
                {{-- チェック処理の設定 --}}
                <div class="card form-group" id="div_rule">
                    <h5 class="card-header">チェック処理の設定</h5>
                    <div class="card-body">
                        {{-- 1行文字列型／複数行文字列型のチェック群 --}}
                        @if ($column->column_type == UserColumnType::text || $column->column_type == UserColumnType::textarea)
                            {{-- 数値のみ許容 --}}
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label text-md-right pt-0">入力制御</label>
                                <div class="col-md-9 align-items-center">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="rule_allowed_numeric" id="rule_allowed_numeric" value="1" class="custom-control-input"
                                            @if(old('rule_allowed_numeric', $column->rule_allowed_numeric)) checked @endif >
                                        <label class="custom-control-label" for="rule_allowed_numeric">半角数値のみ許容</label><br>
                                        <small class="text-muted">※ 全角数値を入力した場合は半角数値に補正します。</small>
                                    </div>
                                </div>
                            </div>

                            {{-- 英数値のみ許容 --}}
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label text-md-right pt-0"></label>
                                <div class="col-md-9 align-items-center">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="rule_allowed_alpha_numeric" id="rule_allowed_alpha_numeric" value="1" class="custom-control-input"
                                            @if(old('rule_allowed_alpha_numeric', $column->rule_allowed_alpha_numeric)) checked @endif>
                                        <label class="custom-control-label" for="rule_allowed_alpha_numeric">半角英数値のみ許容</label>
                                    </div>
                                </div>
                            </div>

                            {{-- 指定桁数（数値）以下を許容 --}}
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label text-md-right">入力桁数</label>
                                <div class="col-md-9 align-items-center">
                                    <input type="text" name="rule_digits_or_less" value="{{old('rule_digits_or_less', $column->rule_digits_or_less)}}" class="form-control">
                                    <small class="text-muted">
                                        ※ 数値で入力します。<br>
                                        ※ 入力桁数の指定時は「半角数値のみ許容」も適用されます。
                                    </small><br>
                                    @if ($errors && $errors->has('rule_digits_or_less'))
                                        <div class="text-danger">{{$errors->first('rule_digits_or_less')}}</div>
                                    @endif
                                </div>
                            </div>

                            {{-- 指定文字数以下を許容 --}}
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label text-md-right">入力最大文字数</label>
                                <div class="col-md-9 align-items-center">
                                    <input type="text" name="rule_word_count" value="{{old('rule_word_count', $column->rule_word_count)}}" class="form-control">
                                    <small class="text-muted">
                                        ※ 数値で入力します。<br>
                                        ※ 全角は2文字、半角は1文字として換算します。
                                    </small>
                                    @if ($errors && $errors->has('rule_word_count'))
                                        <div class="text-danger">{{$errors->first('rule_word_count')}}</div>
                                    @endif
                                </div>
                            </div>

                            {{-- 最大値設定 --}}
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label text-md-right">最大値</label>
                                <div class="col-md-9 align-items-center">
                                    <input type="text" name="rule_max" value="{{old('rule_max', $column->rule_max)}}" class="form-control">
                                    <small class="text-muted">※ 数値で入力します。</small>
                                    @if ($errors && $errors->has('rule_max'))
                                        <div class="text-danger">{{$errors->first('rule_max')}}</div>
                                    @endif
                                </div>
                            </div>

                            {{-- 最小値設定 --}}
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label text-md-right">最小値</label>
                                <div class="col-md-9 align-items-center">
                                    <input type="text" name="rule_min" value="{{old('rule_min', $column->rule_min)}}" class="form-control">
                                    <small class="text-muted">※ 数値で入力します。</small>
                                    @if ($errors && $errors->has('rule_min'))
                                        <div class="text-danger">{{$errors->first('rule_min')}}</div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($column->column_type == UserColumnType::text || $column->column_type == UserColumnType::textarea || $column->column_type == UserColumnType::mail)
                            {{-- 正規表現設定 --}}
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label text-md-right">正規表現</label>
                                <div class="col-md-9 align-items-center">
                                    <input type="text" name="rule_regex" value="{{old('rule_regex', $column->rule_regex)}}" class="form-control">
                                    @if ($errors && $errors->has('rule_regex')) <div class="text-danger">{{$errors->first('rule_regex')}}</div> @endif
                                    <small class="text-muted">
                                        ※ エラーメッセージは「正しい形式の＜項目名＞を指定してください。」と表示されるため、併せてキャプションの設定をする事をオススメします。<br>
                                        ※ （設定例：電話番号ハイフンあり）/0\d{1,4}-\d{1,4}-\d{4}/<br>
                                        ※ （設定例：指定ドメインのメールアドレスのみ）/@example\.com$/<br>
                                    </small>
                                </div>
                            </div>
                        @endif

                        {{-- ボタンエリア --}}
                        <div class="form-group text-center">
                            <button onclick="javascript:submit_update_column_detail();" class="btn btn-primary database-horizontal">
                                <i class="fas fa-check"></i> 更新
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 所属型 --}}
            @if ($column->column_type == UserColumnType::affiliation)
                {{-- 選択肢の設定 --}}
                <div class="card form-group">
                    <h5 class="card-header">選択肢の設定</h5>
                    <div class="card-body">

                        <div class="table-responsive">

                            {{-- 選択項目の一覧 --}}
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        @if (count($sections) > 0)
                                            <th class="text-center" nowrap>表示順</th>
                                            <th class="text-center" nowrap>組織名</th>
                                            <th class="text-center" nowrap>コード</th>
                                            <th class="text-center" nowrap>更新</th>
                                            <th class="text-center" nowrap>削除</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- 更新用の行 --}}
                                    @foreach ($sections as $section)
                                        <tr>
                                            {{-- 表示順操作 --}}
                                            <td class="text-center" nowrap>
                                                {{-- 上移動 --}}
                                                <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->first) disabled @endif onclick="javascript:submit_section_display_sequence({{ $section->id }}, {{ $section->display_sequence }}, 'up')">
                                                    <i class="fas fa-arrow-up"></i>
                                                </button>

                                                {{-- 下移動 --}}
                                                <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->last) disabled @endif onclick="javascript:submit_section_display_sequence({{ $section->id }}, {{ $section->display_sequence }}, 'down')">
                                                    <i class="fas fa-arrow-down"></i>
                                                </button>
                                            </td>

                                            {{-- 組織名 --}}
                                            <td>
                                                <input class="form-control @if ($errors && $errors->has('section_name_'.$section->id)) border-danger @endif" type="text" name="section_name_{{ $section->id }}" value="{{ old('section_name_'.$section->id, $section->name)}}">
                                            </td>

                                            {{-- コード --}}
                                            <td>
                                                <input class="form-control @if ($errors && $errors->has('section_code_'.$section->id)) border-danger @endif" type="text" name="section_code_{{ $section->id }}" value="{{ old('section_code_'.$section->id, $section->code)}}">
                                            </td>

                                            {{-- 更新ボタン --}}
                                            <td class="align-middle text-center">
                                                <button
                                                    class="btn btn-primary btn-sm text-nowrap"
                                                    onclick="javascript:submit_update_section({{ $section->id }});"
                                                >
                                                    <i class="fas fa-check"></i> 更新
                                                </button>
                                            </td>

                                            {{-- 削除ボタン --}}
                                            <td class="align-middle text-center">
                                                <div class="button-wrapper" @if ($section->users->count()) data-toggle="tooltip" title="所属しているユーザがいるため削除できません。" @endif>
                                                <button
                                                    class="btn btn-danger btn-sm text-nowrap"
                                                    onclick="javascript:return submit_delete_section({{ $section->id }});"
                                                    @if ($section->users->count()) disabled @endif
                                                >
                                                    <i class="fas fa-trash-alt"></i> 削除
                                                </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="thead-light">
                                        <th colspan="7">【組織の追加行】</th>
                                    </tr>

                                    {{-- 新規登録用の行 --}}
                                    <tr>
                                        <td></td>
                                        <td>
                                            {{-- 組織名 --}}
                                            <input class="form-control @if ($errors && $errors->has('section_name')) border-danger @endif" type="text" name="section_name" value="{{ old('section_name') }}" placeholder="組織名">
                                        </td>
                                        <td>
                                            {{-- コード --}}
                                            <input class="form-control @if ($errors && $errors->has('section_code')) border-danger @endif" type="text" name="section_code" value="{{ old('section_code') }}" placeholder="コード">
                                        </td>
                                        <td class="align-middle text-center">
                                            {{-- ＋ボタン --}}
                                            <button class="btn btn-primary btn-sm text-nowrap" onclick="javascript:submit_add_section(this);"><i class="fas fa-plus"></i> 組織追加</button>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            @endif

            @if (UsersColumns::isShowOnlyColumnType($column->column_type))
                {{-- 表示しない --}}
            @else
                {{-- キャプション設定 --}}
                <div class="card form-group" id="div_caption">
                    <h5 class="card-header">キャプションの設定</h5>
                    <div class="card-body">

                        {{-- キャプション内容 --}}
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right pt-0">内容 </label>
                            <div class="col-md-9 align-items-center">
                                <textarea name="caption" class="form-control" rows="3">{{old('caption', $column->caption)}}</textarea>
                            </div>
                        </div>

                        {{-- キャプション文字色 --}}
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right">文字色 </label>
                            <div class="col-md-9 align-items-center">
                                <select class="form-control" name="caption_color">
                                    @foreach (Bs4TextColor::getMembers() as $key=>$value)
                                        <option value="{{$key}}" class="{{ $key }}" @if($key == old('caption_color', $column->caption_color)) selected @endif>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- ボタンエリア --}}
                        <div class="form-group text-center">
                            <button onclick="javascript:submit_update_column_detail();" class="btn btn-primary database-horizontal">
                                <i class="fas fa-check"></i> 更新
                            </button>
                        </div>
                    </div>
                </div>

                @if (
                    $column->column_type == UserColumnType::text ||
                    $column->column_type == UserColumnType::textarea ||
                    $column->column_type == UserColumnType::mail ||
                    $column->column_type == UserColumnType::user_name ||
                    $column->column_type == UserColumnType::login_id ||
                    $column->column_type == UserColumnType::user_email
                    )
                    {{-- プレースホルダ設定 --}}
                    <div class="card form-group">
                        <h5 class="card-header">プレースホルダの設定</h5>
                        <div class="card-body">

                            {{-- プレースホルダ内容 --}}
                            <div class="form-group row">
                                <label class="col-md-3 col-form-label text-md-right">内容 </label>
                                <div class="col-md-9 align-items-center">
                                    <input type="text" name="place_holder" class="form-control" value="{{old('place_holder', $column->place_holder)}}">
                                    @if ($column->column_type == UserColumnType::user_name)
                                        <small class="text-muted">※ 未設定の場合「{{__('messages.input_user_name')}}」（多言語対応）を表示します。設定した場合、英日どちらでも同じ設定値を表示します。</small>
                                    @elseif ($column->column_type == UserColumnType::login_id)
                                        <small class="text-muted">※ 未設定の場合「{{__('messages.input_login_id')}}」（多言語対応）を表示します。設定した場合、英日どちらでも同じ設定値を表示します。</small>
                                    @elseif ($column->column_type == UserColumnType::user_email)
                                        <small class="text-muted">※ 未設定の場合「{{__('messages.input_email')}}」（多言語対応）を表示します。設定した場合、英日どちらでも同じ設定値を表示します。</small>
                                    @endif
                                </div>
                            </div>

                            {{-- ボタンエリア --}}
                            <div class="form-group text-center">
                                <button onclick="javascript:submit_update_column_detail();" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- 表示・編集設定 --}}
            <div class="card form-group">
                <h5 class="card-header">表示・編集設定</h5>
                <div class="card-body">

                    @if (UsersColumns::isShowOnlyColumnType($column->column_type))
                        {{-- 表示しない --}}
                        <input type="hidden" name="is_show_auto_regist" value="{{$column->is_show_auto_regist}}">
                    @else
                        {{-- 自動ユーザ登録時の表示指定 --}}
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right pt-0">自動ユーザ登録時の表示指定</label>
                            <div class="col-md-9 align-items-center">
                                @php
                                    $show_auto_regist_disabled = '';
                                    if (UsersColumns::isFixedColumnType($column->column_type) || $column->required == Required::on) {
                                        $show_auto_regist_disabled = 'disabled';
                                    }
                                @endphp
                                @if ($show_auto_regist_disabled)
                                    <input type="hidden" name="is_show_auto_regist" value="{{$column->is_show_auto_regist}}">
                                @endif
                                @foreach (ShowType::getMembers() as $enum_value => $enum_label)
                                    <div class="custom-control custom-radio custom-control-inline">
                                        @if ($column->is_show_auto_regist == $enum_value)
                                            <input type="radio" value="{{$enum_value}}" id="is_show_auto_regist_{{$enum_value}}" name="is_show_auto_regist" class="custom-control-input" checked="checked" {{$show_auto_regist_disabled}}>
                                        @else
                                            <input type="radio" value="{{$enum_value}}" id="is_show_auto_regist_{{$enum_value}}" name="is_show_auto_regist" class="custom-control-input" {{$show_auto_regist_disabled}}>
                                        @endif
                                        {{-- duskでradioの選択にlabelのid必要 --}}
                                        <label class="custom-control-label" for="is_show_auto_regist_{{$enum_value}}" id="label_is_show_auto_regist_{{$enum_value}}">{{$enum_label}}</label>
                                    </div>
                                @endforeach
                                <br />
                                <small class="text-muted">※ 自動ユーザ登録時に表示するか設定できます。<br /></small>
                                @if ($column->required == Required::on)
                                    <small class="text-danger">※ 必須入力の場合、表示指定を変更できません。<br /></small>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($column->column_type == UserColumnType::user_password)
                        {{-- 表示しない --}}
                        <input type="hidden" name="is_show_my_page" value="{{ShowType::not_show}}">
                    @else
                        {{-- マイページの表示指定 --}}
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right pt-0">マイページの表示指定</label>
                            <div class="col-md-9 align-items-center">
                                @foreach (ShowType::getMembers() as $enum_value => $enum_label)
                                    <div class="custom-control custom-radio custom-control-inline">
                                        @if ($column->is_show_my_page == $enum_value)
                                            <input type="radio" value="{{$enum_value}}" id="is_show_my_page_{{$enum_value}}" name="is_show_my_page" class="custom-control-input" checked="checked">
                                        @else
                                            <input type="radio" value="{{$enum_value}}" id="is_show_my_page_{{$enum_value}}" name="is_show_my_page" class="custom-control-input">
                                        @endif
                                        {{-- duskでradioの選択にlabelのid必要 --}}
                                        <label class="custom-control-label" for="is_show_my_page_{{$enum_value}}" id="label_is_show_my_page_{{$enum_value}}">{{$enum_label}}</label>
                                    </div>
                                @endforeach
                                <br />
                                <small class="text-muted">※ マイページでユーザ自身に表示するか設定できます。</small>
                            </div>
                        </div>
                    @endif

                    @if (UsersColumns::isShowOnlyColumnType($column->column_type))
                        {{-- 表示しない --}}
                        <input type="hidden" name="is_edit_my_page" value="{{$column->is_edit_my_page}}">
                    @else
                        {{-- マイページの編集指定 --}}
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label text-md-right pt-0">マイページの編集指定</label>
                            <div class="col-md-9 align-items-center">
                                @foreach (EditType::getMembers() as $enum_value => $enum_label)
                                    <div class="custom-control custom-radio custom-control-inline">
                                        @if ($column->is_edit_my_page == $enum_value)
                                            <input type="radio" value="{{$enum_value}}" id="is_edit_my_page_{{$enum_value}}" name="is_edit_my_page" class="custom-control-input" checked="checked">
                                        @else
                                            <input type="radio" value="{{$enum_value}}" id="is_edit_my_page_{{$enum_value}}" name="is_edit_my_page" class="custom-control-input">
                                        @endif
                                        {{-- duskでradioの選択にlabelのid必要 --}}
                                        <label class="custom-control-label" for="is_edit_my_page_{{$enum_value}}" id="label_is_edit_my_page_{{$enum_value}}">{{$enum_label}}</label>
                                    </div>
                                @endforeach
                                <br />
                                <small class="text-muted">※ マイページ＞マイプロフィール変更でユーザ自身に編集させるか設定できます。</small>
                            </div>
                        </div>
                    @endif

                    {{-- ボタンエリア --}}
                    <div class="form-group text-center">
                        <button onclick="javascript:submit_update_column_detail();" class="btn btn-primary database-horizontal">
                            <i class="fas fa-check"></i> 更新
                        </button>
                    </div>
                </div>
            </div>

            {{-- 変数名の設定 --}}
            <div class="card form-group">
                <h5 class="card-header">変数名の設定</h5>
                <div class="card-body">
                    {{-- 変数名の使用 --}}
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label text-md-right pt-0">変数名の使用</label>
                        <div class="col-md-9 align-items-center">
                            @foreach (UseType::getMembers() as $enum_value => $enum_label)
                                <div class="custom-control custom-radio custom-control-inline">
                                    @if (old('use_variable', $column->use_variable) == $enum_value)
                                        <input type="radio" value="{{$enum_value}}" id="use_variable_{{$enum_value}}" name="use_variable" class="custom-control-input" checked="checked">
                                    @else
                                        <input type="radio" value="{{$enum_value}}" id="use_variable_{{$enum_value}}" name="use_variable" class="custom-control-input">
                                    @endif
                                    {{-- duskでradioの選択にlabelのid必要 --}}
                                    <label class="custom-control-label" for="use_variable_{{$enum_value}}" id="label_use_variable_{{$enum_value}}">{{$enum_label}}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 変数名 --}}
                    <div class="form-group row collapse" id="variable_name_div">
                        <label class="col-md-3 col-form-label text-md-right">変数名 <span class="badge badge-danger">必須</span></label>
                        <div class="col-md-9 align-items-center">
                            <input type="text" name="variable_name" value="{{old('variable_name', $column->variable_name)}}" class="form-control @if ($errors && $errors->has("variable_name")) border-danger @endif" />
                            @include('plugins.common.errors_inline', ['name' => "variable_name"])
                        </div>
                    </div>

                    {{-- ボタンエリア --}}
                    <div class="form-group text-center">
                        <button onclick="javascript:submit_update_column_detail();" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
                    </div>
                </div>
            </div>

            {{-- ボタンエリア --}}
            <div class="form-group text-center">
                <a href="{{url('/')}}/manage/user/editColumns/{{$columns_set->id}}" class="btn btn-secondary">
                    <i class="fas fa-chevron-left"></i> 項目設定へ
                </a>
            </div>
        </form>

    </div>
</div>

{{-- disableのボタンにツールチップを置くための対応 --}}
<style>
.button-wrapper {
  display: inline-block;
}

.button-wrapper .btn:disabled {
  pointer-events: none;
}
</style>

<script>
    {{-- 初期状態で開くもの --}}
    change_use_variable('{{old('use_variable', $column->use_variable)}}');
</script>
@endsection
