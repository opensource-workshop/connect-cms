@php
use App\Models\Core\UsersColumns;
@endphp

@include('plugins.common.errors_form_line')

@if ($errors->has('undelete'))
    <div class="alert alert-danger">
        <strong>{{ $errors->first('undelete') }}</strong>
    </div>
@endif

@php
    $is_function_edit = false;
    if (isset($function) && $function == 'edit') {
        // ユーザ変更
        $is_function_edit = true;
    }
@endphp

<script>
    $(function () {
        /** ツールチップ有効化 */
        $('[data-toggle="tooltip"]').tooltip();

        // 条件付き表示の初期化
        initConditionalDisplay();
    });

    /** 項目セット変更submit */
    function changeColumnsSetIdAction(columns_set_id) {
        @if (Auth::user() && Auth::user()->can('admin_user'))
            @if ($is_function_edit)
                {{-- ユーザ管理-編集 --}}
                document.forms['form_register'].action = '{{url('/manage/user/edit/')}}/{{$id}}?columns_set_id=' + columns_set_id;
            @else
                {{-- ユーザ管理-登録 --}}
                document.forms['form_register'].action = '{{url('/manage/user/regist')}}?columns_set_id=' + columns_set_id;
            @endif
        @else
            {{-- 自動ユーザ登録-再表示 --}}
            document.forms['form_register'].action = '{{route('show_register_form.re_show')}}';
        @endif
        document.forms['form_register'].submit();
    }

    /**
     * 条件付き表示の初期化
     */
    function initConditionalDisplay() {
        @if (isset($conditional_display_settings) && !empty($conditional_display_settings))
            var settings = {!! json_encode($conditional_display_settings) !!};

            // 各トリガー項目に対してchangeイベントを設定
            settings.forEach(function(setting) {
                // 初回表示時の判定
                evaluateCondition(setting);

                // 値変更時のイベントリスナーを設定
                attachEventListeners(setting);
            });
        @endif
    }

    /**
     * イベントリスナーを設定
     */
    function attachEventListeners(setting) {
        var columnId = setting.trigger_column_id;
        var columnType = setting.trigger_column_type;

        // システム固定項目の場合
        if (columnType) {
            var fixedElement = null;
            switch(columnType) {
                case 'user_name':
                    fixedElement = document.getElementById('name');
                    break;
                case 'login_id':
                    fixedElement = document.getElementById('userid');
                    break;
                case 'user_email':
                    fixedElement = document.getElementById('email');
                    break;
                case 'user_password':
                    fixedElement = document.getElementById('password');
                    break;
            }
            if (fixedElement) {
                $(fixedElement).on('change input', function() {
                    evaluateCondition(setting);
                });
                return;
            }
        }

        // 各入力タイプに応じてイベントリスナーを設定
        // 1. id="user-column-{id}" の要素（テキスト入力、所属型セレクトボックス）
        var idElement = document.getElementById('user-column-' + columnId);
        if (idElement) {
            $(idElement).on('change input', function() {
                evaluateCondition(setting);
            });
            return; // 見つかったので他の検索は不要
        }

        // 2. ラジオボタン（users_columns_value[{id}]）
        var radioElements = document.querySelectorAll('input[name="users_columns_value[' + columnId + ']"]');
        if (radioElements.length > 0) {
            $(radioElements).on('change', function() {
                evaluateCondition(setting);
            });
            return;
        }

        // 3. チェックボックス・同意型（users_columns_value[{id}][]）
        var checkboxElements = document.querySelectorAll('input[name="users_columns_value[' + columnId + '][]"]');
        if (checkboxElements.length > 0) {
            $(checkboxElements).on('change', function() {
                evaluateCondition(setting);
            });
            return;
        }
    }

    /**
     * 入力要素を検索
     */
    function findInputElement(columnId, columnType) {
        // システム固定項目の場合、固定のname/idを使用
        if (columnType) {
            var fixedElement = null;
            switch(columnType) {
                case 'user_name':
                    fixedElement = document.getElementById('name');
                    break;
                case 'login_id':
                    fixedElement = document.getElementById('userid');
                    break;
                case 'user_email':
                    fixedElement = document.getElementById('email');
                    break;
                case 'user_password':
                    fixedElement = document.getElementById('password');
                    break;
            }
            if (fixedElement) {
                return fixedElement;
            }
        }

        // 1. user-column-{id} のIDを持つ要素を探す（テキスト入力）
        var element = document.getElementById('user-column-' + columnId);
        if (element) {
            return element;
        }

        // 2. name="users_columns_value[{id}]" のラジオボタンを探す
        var elements = document.querySelectorAll('input[name="users_columns_value[' + columnId + ']"]');
        if (elements.length > 0) {
            return elements[0];
        }

        // 3. name="users_columns_value[{id}][]" のチェックボックス・同意型を探す
        elements = document.querySelectorAll('input[name="users_columns_value[' + columnId + '][]"]');
        if (elements.length > 0) {
            return elements[0];
        }

        // 4. name="users_columns_value[{id}]" の所属型セレクトボックスを探す
        element = document.querySelector('select[name="users_columns_value[' + columnId + ']"]');
        if (element) {
            return element;
        }

        return null;
    }

    /**
     * 条件を評価して対象項目の表示/非表示を切り替え
     */
    function evaluateCondition(setting) {
        var triggerElement = findInputElement(setting.trigger_column_id, setting.trigger_column_type);
        var triggerValue = getInputValue(triggerElement);
        var conditionMet = false;

        // 条件評価
        if (setting.operator === 'equals') {
            conditionMet = (triggerValue == setting.value);
        } else if (setting.operator === 'not_equals') {
            conditionMet = (triggerValue != setting.value);
        } else if (setting.operator === 'is_empty') {
            // 空白である（空文字または未選択）
            conditionMet = (triggerValue === '' || triggerValue === null || triggerValue === undefined);
        } else if (setting.operator === 'is_not_empty') {
            // 空白でない
            conditionMet = (triggerValue !== '' && triggerValue !== null && triggerValue !== undefined);
        }

        // 対象項目のform-group rowを表示/非表示
        var targetElement = findInputElement(setting.target_column_id);
        if (targetElement) {
            var formGroup = $(targetElement).closest('.form-group.row');
            if (conditionMet) {
                formGroup.show();
            } else {
                formGroup.hide();
                // 非表示時は入力値をクリア
                clearInputValue(targetElement);
            }
        }
    }

    /**
     * 入力要素の値を取得
     */
    function getInputValue(element) {
        if (!element) {
            return '';
        }

        var elementName = element.name;

        // 複数選択チェックボックス（name="columns[X][]"）の場合
        if (elementName && elementName.endsWith('[]')) {
            var checkedElements = document.querySelectorAll('input[name="' + elementName + '"]:checked');
            var values = [];
            checkedElements.forEach(function(el) {
                values.push(el.value);
            });
            // カンマ区切りで返す（複数選択の場合）
            return values.join(',');
        }

        // ラジオボタン・単一チェックボックスの場合
        if (element.type === 'radio' || element.type === 'checkbox') {
            var checkedElement = document.querySelector('input[name="' + elementName + '"]:checked');
            return checkedElement ? checkedElement.value : '';
        }

        // セレクトボックスの場合
        if (element.tagName === 'SELECT') {
            // 所属型セレクトボックス（users_columns_value[X]）の場合、テキストを返す
            if (elementName && elementName.match(/^users_columns_value\[\d+\]$/)) {
                var selectedOption = element.options[element.selectedIndex];
                return selectedOption ? selectedOption.text : '';
            }
            // その他のセレクトボックスはvalueを返す
            return element.options[element.selectedIndex]?.value || '';
        }

        // その他の入力要素（テキスト等）
        return element.value || '';
    }

    /**
     * 入力要素の値をクリア
     */
    function clearInputValue(element) {
        if (!element) {
            return;
        }

        var elementName = element.name;

        // ラジオボタン・チェックボックスの場合、すべてのチェックを外す
        if (element.type === 'radio' || element.type === 'checkbox') {
            var elements = document.querySelectorAll('input[name="' + elementName + '"]');
            elements.forEach(function(el) {
                el.checked = false;
            });
            return;
        }

        // セレクトボックスの場合、最初のオプションを選択
        if (element.tagName === 'SELECT') {
            element.selectedIndex = 0;
            return;
        }

        // その他の入力要素（テキスト等）
        element.value = '';
    }
</script>

@if ($is_function_edit)
    <form class="form-horizontal" method="POST" action="{{url('/manage/user/update/')}}/{{$id}}" name="form_register">
@else
    <form class="form-horizontal" method="POST" action="{{route('register')}}" name="form_register">
@endif
    {{ csrf_field() }}

    @if (Auth::user() && Auth::user()->can('admin_user'))
        <div class="form-group row">
            <label for="name" class="col-md-4 col-form-label text-md-right">状態 <span class="badge badge-danger">必須</span></label>
            <div class="col-md-8 d-sm-flex align-items-center">
                @foreach (UserStatus::getMembers() as $enum_value => $enum_label)
                    <div class="custom-control custom-radio custom-control-inline">
                        @if (old('status', $user->status) == $enum_value)
                            <input type="radio" value="{{$enum_value}}" id="status_{{$enum_value}}" name="status" class="custom-control-input" checked="checked" {{$user->getStstusDisabled($enum_value, $is_function_edit)}}>
                        @else
                            <input type="radio" value="{{$enum_value}}" id="status_{{$enum_value}}" name="status" class="custom-control-input" {{$user->getStstusDisabled($enum_value, $is_function_edit)}}>
                        @endif
                        <label class="custom-control-label" for="status_{{$enum_value}}">{{$enum_label}}</label>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- 画面として必須項目なので、ステータスを固定のパラメータとして配置する --}}
        {{-- この値はUserを登録する際に使われず、サーバーサイドでシステム設定値をもとにステータスを決定する --}}
        {{-- see also : App\Http\Controllers\Auth userStatus() --}}
        <input type="hidden" value="{{UserStatus::active}}" name="status">
    @endif

    @if (config('connect.USE_USERS_COLUMNS_SET'))
        @if (Auth::user() && Auth::user()->can('admin_user'))
            {{-- 管理者によるユーザ登録 --}}
            <div class="form-group row">
                <label for="columns_set_id" class="col-md-4 col-form-label text-md-right">項目セット <span class="badge badge-danger">必須</span></label>
                <div class="col-md-8">
                    <select name="columns_set_id" id="columns_set_id" class="form-control @if ($errors->has('columns_set_id')) border-danger @endif" onchange="changeColumnsSetIdAction(this.value)">
                        <option value=""></option>
                        @foreach ($columns_sets as $columns_set)
                            <option value="{{$columns_set->id}}" @if (old('columns_set_id', $user->columns_set_id) == $columns_set->id) selected="selected" @endif>{{$columns_set->name}}</option>
                        @endforeach
                    </select>
                    @include('plugins.common.errors_inline', ['name' => 'columns_set_id'])
                    <small class="form-text text-muted">※ 選択すると、関連した項目に自動切替します。</small>
                </div>
            </div>
        @else
            {{-- 自動登録 --}}
            <div class="form-group row">
                <label for="columns_set_id" class="col-md-4 col-form-label text-md-right">{{Configs::getConfigsValue($configs, "user_columns_set_label_name", '項目セット')}} <span class="badge badge-danger">必須</span></label>
                <div class="col-md-8">
                    <select name="columns_set_id" id="columns_set_id" class="form-control @if ($errors->has('columns_set_id')) border-danger @endif" onchange="changeColumnsSetIdAction()">
                        @foreach ($columns_sets as $columns_set)
                            <option value="{{$columns_set->id}}" @if (old('columns_set_id', $columns_set_id) == $columns_set->id) selected="selected" @endif>{{$columns_set->name}}</option>
                        @endforeach
                    </select>
                    @include('plugins.common.errors_inline', ['name' => 'columns_set_id'])
                    <small class="form-text text-muted">※ 選択すると、関連した項目に自動切替します。</small>
                </div>
            </div>
        @endif
    @else
        <input type="hidden" name="columns_set_id" value="{{$columns_set_id}}">
    @endif

    {{-- ユーザーの追加カラム --}}
    @foreach($users_columns as $column)
        @if ($column->column_type == UserColumnType::user_name)
            {{-- ユーザ名 --}}
            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">{{$column->column_name}} <span class="badge badge-danger">必須</span></label>
                <div class="col-md-8">
                    <input id="name" type="text" class="form-control @if ($errors->has('name')) border-danger @endif" name="name" value="{{ old('name', $user->name) }}" placeholder="{{ $column->place_holder ?? __('messages.input_user_name') }}" required>
                    @include('plugins.common.errors_inline', ['name' => 'name'])
                    <div class="small {{ $column->caption_color }}">{!! nl2br((string)$column->caption) !!}</div>
                </div>
            </div>

        @elseif ($column->column_type == UserColumnType::login_id)
            {{-- ログインID --}}
            <div class="form-group row">
                <label for="userid" class="col-md-4 col-form-label text-md-right">{{$column->column_name}} <span class="badge badge-danger">必須</span></label>
                <div class="col-md-8">
                    <input id="userid" type="text" class="form-control @if ($errors->has('userid')) border-danger @endif" name="userid" value="{{ old('userid', $user->userid) }}" placeholder="{{ $column->place_holder ?? __('messages.input_login_id') }}" required>
                    @include('plugins.common.errors_inline', ['name' => 'userid'])
                    <div class="small {{ $column->caption_color }}">{!! nl2br((string)$column->caption) !!}</div>
                </div>
            </div>

        @elseif ($column->column_type == UserColumnType::user_email)
            {{-- メールアドレス --}}
            @if (Auth::user() && Auth::user()->can('admin_user'))
                {{-- 管理者によるユーザ登録 --}}
                <div class="form-group row">
                    <label for="email" class="col-md-4 col-form-label text-md-right">{{$column->column_name}}</label>
                    <div class="col-md-8">
                        <input id="email" type="text" class="form-control @if ($errors->has('email')) border-danger @endif" name="email" value="{{ old('email', $user->email) }}" placeholder="{{ $column->place_holder ?? __('messages.input_email') }}">
                        @include('plugins.common.errors_inline', ['name' => 'email'])
                        @if (!$is_function_edit)
                            <small class="text-muted">
                                ※ 登録時に{{$column->column_name}}がある場合、登録メール送信画面に移動します。<br />
                            </small>
                        @endif
                        <div class="small {{ $column->caption_color }}">{!! nl2br((string)$column->caption) !!}</div>
                    </div>
                </div>
            @else
                {{-- 自動登録 --}}
                <div class="form-group row">
                    <label for="email" class="col-md-4 col-form-label text-md-right">{{$column->column_name}} <span class="badge badge-danger">必須</span></label>
                    <div class="col-md-8">
                        <input id="email" type="text" class="form-control @if ($errors->has('email')) border-danger @endif" name="email" value="{{ old('email', $user->email) }}" placeholder="{{ $column->place_holder ?? __('messages.input_email') }}" required>
                        @include('plugins.common.errors_inline', ['name' => 'email'])
                        <div class="small {{ $column->caption_color }}">{!! nl2br((string)$column->caption) !!}</div>
                    </div>
                </div>
            @endif

        @elseif ($column->column_type == UserColumnType::user_password)
            {{-- パスワード --}}
            <div class="form-group row">
                @if ($is_function_edit)
                    <label for="password" class="col-md-4 col-form-label text-md-right">{{$column->column_name}}</label>
                @else
                    <label for="password" class="col-md-4 col-form-label text-md-right">{{$column->column_name}} <span class="badge badge-danger">必須</span></label>
                @endif
                <div class="col-md-8">
                    @if ($is_function_edit)
                        <input id="password" type="password" class="form-control @if ($errors->has('password')) border-danger @endif" name="password" autocomplete="new-password" placeholder="{{ __('messages.input_password') }}">
                    @else
                        <input id="password" type="password" class="form-control @if ($errors->has('password')) border-danger @endif" name="password" autocomplete="new-password" required placeholder="{{ __('messages.input_password') }}">
                    @endif
                    @if ($errors->has('password'))
                        @foreach ($errors->get('password') as $error)
                            <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$error}}</div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="form-group row">
                @if ($is_function_edit)
                    <label for="password-confirm" class="col-md-4 col-form-label text-md-right">確認用{{$column->column_name}}</label>
                @else
                    <label for="password-confirm" class="col-md-4 col-form-label text-md-right">確認用{{$column->column_name}} <span class="badge badge-danger">必須</span></label>
                @endif
                <div class="col-md-8">
                    @if ($is_function_edit)
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="{{ __('messages.input_password_confirm') }}">
                    @else
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required placeholder="{{ __('messages.input_password_confirm') }}">
                    @endif
                    <div class="small {{ $column->caption_color }}">{!! nl2br((string)$column->caption) !!}</div>
                </div>
            </div>

        @elseif (UsersColumns::isShowOnlyAutoRegistColumnType($column->column_type))
            {{-- 表示しない --}}
        @elseif (UsersColumns::isAutoRegistOnlyColumnTypes($column->column_type))
            {{-- 未ログイン（自動登録）時のみ表示 --}}
            @if (!Auth::user())
                @php
                    $label_for = 'for=user-column-' . $column->id;
                    $label_class = '';
                @endphp
                <div class="form-group row">
                    <label class="col-md-4 col-form-label text-md-right {{$label_class}}" {{$label_for}}>{{$column->column_name}} @if ($column->required)<span class="badge badge-danger">必須</span> @endif</label>
                    <div class="col-md-8">
                        @includeFirst(["auth_option.registe_form_input_$column->column_type", "auth.registe_form_input_$column->column_type"], ['user_obj' => $column, 'label_id' => 'user-column-'.$column->id])
                        <div class="small {{ $column->caption_color }}">{!! nl2br((string)$column->caption) !!}</div>
                    </div>
                </div>
            @endif

        @else
            @php
                // ラジオとチェックボックスは選択肢にラベルを使っているため、項目名のラベルにforを付けない
                if (UsersColumns::isChoicesColumnType($column->column_type)) {
                    $label_for = '';
                    $label_class = 'pt-0';
                } else {
                    $label_for = 'for=user-column-' . $column->id;
                    $label_class = '';
                }
            @endphp

            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right {{$label_class}}" {{$label_for}}>{{$column->column_name}} @if ($column->required)<span class="badge badge-danger">必須</span> @endif</label>
                <div class="col-md-8">
                    @includeFirst(["auth_option.registe_form_input_$column->column_type", "auth.registe_form_input_$column->column_type"], ['user_obj' => $column, 'label_id' => 'user-column-'.$column->id])
                    <div class="small {{ $column->caption_color }}">{!! nl2br((string)$column->caption) !!}</div>
                </div>
            </div>
        @endif
    @endforeach

    {{-- 未ログイン（自動登録）時に個人情報保護方針への同意関係が設定されている場合 --}}
    @if (!Auth::user())
        @if (Configs::getConfigsValue($configs, "user_register_requre_privacy") == 1)
            <div class="form-group row">
                <label for="password-confirm" class="col-md-4 col-form-label text-md-right pt-0">個人情報保護方針への同意  <span class="badge badge-danger">必須</span></label>

                <div class="col-md-8">
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input name="user_register_requre_privacy" value="以下の内容に同意します。" type="checkbox" class="custom-control-input @if ($errors->has('user_register_requre_privacy')) is-invalid @endif" id="user_register_requre_privacy">
                        <label class="custom-control-label" for="user_register_requre_privacy"> 以下の内容に同意します。</label>
                    </div>
                    @include('plugins.common.errors_inline', ['name' => 'user_register_requre_privacy'])
                    {!!Configs::getConfigsValue($configs, "user_register_privacy_description", null)!!}
                </div>
            </div>
        @endif
    @endif

    {{-- コンテンツ権限 --}}
    @if (Auth::user() && Auth::user()->can('admin_user'))
        <div class="form-group row">
            <label for="password-confirm" class="col-md-4 text-md-right">コンテンツ権限</label>
            <div class="col-md-8">
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["base"]) && isset($users_roles["base"]["role_article_admin"]) && $users_roles["base"]["role_article_admin"] == 1) ||
                          old('base.role_article_admin') == 1)
                        <input name="base[role_article_admin]" value="1" type="checkbox" class="custom-control-input" id="role_article_admin" checked="checked">
                    @else
                        <input name="base[role_article_admin]" value="1" type="checkbox" class="custom-control-input" id="role_article_admin">
                    @endif
                    <label class="custom-control-label" for="role_article_admin" id="label_role_article_admin">
                        コンテンツ管理者
                        <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-placement="right" title="プラグイン管理者・モデレータ・承認者・編集者すべての権限を含む記事の管理者権限"></i>
                    </label>
                </div>
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["base"]) && isset($users_roles["base"]["role_arrangement"]) && $users_roles["base"]["role_arrangement"] == 1) ||
                          old('base.role_arrangement') == 1)
                        <input name="base[role_arrangement]" value="1" type="checkbox" class="custom-control-input" id="role_arrangement" checked="checked">
                    @else
                        <input name="base[role_arrangement]" value="1" type="checkbox" class="custom-control-input" id="role_arrangement">
                    @endif
                    <label class="custom-control-label" for="role_arrangement" id="label_role_arrangement">
                        プラグイン管理者
                        <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-placement="right" title="ページにプラグインを配置し、プラグインの設定画面を操作できる権限"></i>
                    </label>
                </div>
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["base"]) && isset($users_roles["base"]["role_article"]) && $users_roles["base"]["role_article"] == 1) ||
                          old('base.role_article') == 1)
                        <input name="base[role_article]" value="1" type="checkbox" class="custom-control-input" id="role_article" checked="checked">
                    @else
                        <input name="base[role_article]" value="1" type="checkbox" class="custom-control-input" id="role_article">
                    @endif
                    <label class="custom-control-label" for="role_article" id="label_role_article">
                        モデレータ
                        <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-placement="right" title="記事の投稿が可能。他者の記事の変更も可能。"></i>
                    </label>
                </div>
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["base"]) && isset($users_roles["base"]["role_approval"]) && $users_roles["base"]["role_approval"] == 1) ||
                          old('base.role_approval') == 1)
                        <input name="base[role_approval]" value="1" type="checkbox" class="custom-control-input" id="role_approval" checked="checked">
                    @else
                        <input name="base[role_approval]" value="1" type="checkbox" class="custom-control-input" id="role_approval">
                    @endif
                    <label class="custom-control-label" for="role_approval" id="label_role_approval">
                        承認者
                        <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-placement="right" title="記事の承認が可能"></i>
                    </label>
                </div>
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["base"]) && isset($users_roles["base"]["role_reporter"]) && $users_roles["base"]["role_reporter"] == 1) ||
                          old('base.role_reporter') == 1)
                        <input name="base[role_reporter]" value="1" type="checkbox" class="custom-control-input" id="role_reporter" checked="checked">
                    @else
                        <input name="base[role_reporter]" value="1" type="checkbox" class="custom-control-input" id="role_reporter">
                    @endif
                    <label class="custom-control-label" for="role_reporter" id="label_role_reporter">
                        編集者
                        <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-placement="right" title="記事の投稿が可能"></i>
                    </label>
                </div>
                <small class="text-muted">
                    ※「編集者」、「モデレータ」の記事投稿については、各プラグイン側の権限設定も必要です。<br />
                    ※「コンテンツ管理者」は、「コンテンツ管理者」権限と同時に「プラグイン管理者」「モデレータ」「承認者」「編集者」権限も併せて持ちます。<br />
                    {{-- ※ 全てのユーザは、「ゲスト」権限も併せて持ちます。<br /> --}}
                    ※「ゲスト」にする場合、「コンテンツ権限」「管理権限」のすべてのチェックを外します。<br />
                </small>
            </div>
        </div>

        {{-- 管理権限 --}}
        <div class="form-group row">
            <label for="password-confirm" class="col-md-4 text-md-right">管理権限</label>
            <div class="col-md-8">
                {{-- システム管理者権限付与は、システム管理者のみ可 --}}
                @if (Auth::user()->can('admin_system'))
                    <div class="custom-control custom-checkbox">
                        @if ((isset($users_roles["manage"]) && isset($users_roles["manage"]["admin_system"]) && $users_roles["manage"]["admin_system"] == 1) ||
                            old('manage.admin_system') == 1)
                            <input name="manage[admin_system]" value="1" type="checkbox" class="custom-control-input" id="admin_system" checked="checked">
                        @else
                            <input name="manage[admin_system]" value="1" type="checkbox" class="custom-control-input" id="admin_system">
                        @endif
                        <label class="custom-control-label" for="admin_system" id="label_admin_system">
                            システム管理者
                            <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-placement="right" title="管理機能をすべて操作できる権限"></i>
                        </label>
                    </div>
                @endif
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["manage"]) && isset($users_roles["manage"]["admin_site"]) && $users_roles["manage"]["admin_site"] == 1) ||
                          old('manage.admin_site') == 1)
                        <input name="manage[admin_site]" value="1" type="checkbox" class="custom-control-input" id="admin_site" checked="checked">
                    @else
                        <input name="manage[admin_site]" value="1" type="checkbox" class="custom-control-input" id="admin_site">
                    @endif
                    <label class="custom-control-label" for="admin_site" id="label_admin_site">
                        サイト管理者
                        <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-placement="right" title="サイト管理を中心にWebサイトの設定を行うメニューが操作できる権限"></i>
                    </label>
                </div>
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["manage"]) && isset($users_roles["manage"]["admin_page"]) && $users_roles["manage"]["admin_page"] == 1) ||
                          old('manage.admin_page') == 1)
                        <input name="manage[admin_page]" value="1" type="checkbox" class="custom-control-input" id="admin_page" checked="checked">
                    @else
                        <input name="manage[admin_page]" value="1" type="checkbox" class="custom-control-input" id="admin_page">
                    @endif
                    <label class="custom-control-label" for="admin_page" id="label_admin_page">
                        ページ管理者
                        <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-placement="right" title="ページ管理が操作できる権限"></i>
                    </label>
                </div>
                <div class="custom-control custom-checkbox">
                    @if ((isset($users_roles["manage"]) && isset($users_roles["manage"]["admin_user"]) && $users_roles["manage"]["admin_user"] == 1) ||
                          old('manage.admin_user') == 1)
                        <input name="manage[admin_user]" value="1" type="checkbox" class="custom-control-input" id="admin_user" checked="checked">
                    @else
                        <input name="manage[admin_user]" value="1" type="checkbox" class="custom-control-input" id="admin_user">
                    @endif
                    <label class="custom-control-label" for="admin_user" id="label_admin_user">
                        ユーザ管理者
                        <i class="fas fa-info-circle text-muted ml-1" data-toggle="tooltip" data-placement="right" title="ユーザ管理が操作できる権限"></i>
                    </label>
                </div>
            </div>
        </div>
    @endif

    {{-- 役割設定 --}}
    @if (isset($original_role_configs))
    <div class="form-group row">
        <label for="password-confirm" class="col-md-4 text-md-right">役割設定</label>
        <div class="col-md-6">
            @foreach($original_role_configs as $original_role_config)
            <div class="custom-control custom-checkbox">
                @if ((isset($original_role_config["role_value"]) && $original_role_config["role_value"] == 1) ||
                      old("original_role." . $original_role_config->name) == 1)
                    <input name="original_role[{{$original_role_config->name}}]" value="1" type="checkbox" class="custom-control-input" id="original_role{{$original_role_config->id}}" checked="checked">
                @else
                    <input name="original_role[{{$original_role_config->name}}]" value="1" type="checkbox" class="custom-control-input" id="original_role{{$original_role_config->id}}">
                @endif
                <label class="custom-control-label" for="original_role{{$original_role_config->id}}" id="label_original_role{{$original_role_config->id}}">{{$original_role_config->value}}</label>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="form-group row text-center">
        <div class="col-sm-3"></div>
        <div class="col-sm-6">
            @if (Auth::user() && Auth::user()->can('admin_user'))
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/user')}}'">
                    <i class="fas fa-times"></i> キャンセル
                </button>
                @if ($is_function_edit)
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> ユーザ変更</button>
                @else
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> ユーザ登録</button>
                @endif
            @else
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}'">
                    <i class="fas fa-times"></i> キャンセル
                </button>
                {{-- ユーザ仮登録ON --}}
                @if (Configs::getConfigsValue($configs, "user_register_temporary_regist_mail_flag") == 1)
                    <button type="submit" class="btn btn-info"><i class="fas fa-check"></i> ユーザ仮登録</button>
                @else
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> ユーザ登録</button>
                @endif
            @endif
        </div>
        {{-- 既存ユーザの場合は削除処理のボタンも表示(自分自身の場合は表示しない, 最後のシステム管理者保持者は削除させない) --}}
        @if (isset($id) && $id && $id != Auth::user()->id && $can_deleted)
            <div class="col-sm-3 pull-right text-right">
                <a data-toggle="collapse" href="#collapse{{$id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> <span class="d-none d-sm-inline">削除</span></span>
                </a>
            </div>
        @endif
    </div>
</form>

@if (isset($id) && $id && $id != Auth::user()->id && $can_deleted)
<div id="collapse{{$id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">ユーザを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/manage/user/destroy/')}}/{{$id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('ユーザを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
