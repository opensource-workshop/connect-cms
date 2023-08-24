{{--
 * copy by resources\views\auth\registe_form.blade.php
--}}
@php
use App\Models\Core\UsersColumns;
@endphp

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

{{-- 登録後メッセージ表示 --}}
@include('plugins.common.flash_message')

<form action="{{url('/')}}/mypage/profile/update/{{$id}}" class="form-horizontal" method="POST">
    {{ csrf_field() }}

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
            <div class="form-group row">
                <label for="email" class="col-md-4 col-form-label text-md-right">{{$column->column_name}}</label>

                <div class="col-md-8">
                    <input id="email" type="text" class="form-control @if ($errors->has('email')) border-danger @endif" name="email" value="{{ old('email', $user->email) }}" placeholder="{{ $column->place_holder ?? __('messages.input_email') }}">
                    @include('plugins.common.errors_inline', ['name' => 'email'])
                    <div class="small {{ $column->caption_color }}">{!! nl2br((string)$column->caption) !!}</div>
                </div>
            </div>

        @elseif ($column->column_type == UserColumnType::user_password)
            {{-- パスワード --}}
            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right">現在の{{$column->column_name}}</label>

                <div class="col-md-8">
                    <input type="password" class="form-control @if ($errors->has('now_password')) border-danger @endif" name="now_password" autocomplete="new-password" placeholder="現在のパスワードを入力します。">
                    @include('plugins.common.errors_inline', ['name' => 'now_password'])
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right">新しい{{$column->column_name}}</label>

                <div class="col-md-8">
                    <input type="password" class="form-control @if ($errors->has('new_password')) border-danger @endif" name="new_password" autocomplete="new-password" placeholder="新しいパスワードを入力します。">
                    @include('plugins.common.errors_inline', ['name' => 'new_password'])
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-4 col-form-label text-md-right">新しい{{$column->column_name}}の確認</label>

                <div class="col-md-8">
                    <input type="password" class="form-control @if ($errors->has('new_password_confirmation')) border-danger @endif" name="new_password_confirmation" placeholder="新しいパスワードと同じものを入力します。">
                    @include('plugins.common.errors_inline', ['name' => 'new_password_confirmation'])
                    <div class="small {{ $column->caption_color }}">{!! nl2br((string)$column->caption) !!}</div>
                </div>
            </div>

        @elseif ($column->column_type == UserColumnType::created_at)
            {{-- 表示しない --}}
        @elseif ($column->column_type == UserColumnType::updated_at)
            {{-- 表示しない --}}
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
                    @include('auth.registe_form_input_' . $column->column_type, ['user_obj' => $column, 'label_id' => 'user-column-'.$column->id])
                    <div class="small {{ $column->caption_color }}">{!! nl2br((string)$column->caption) !!}</div>
                </div>
            </div>
        @endif
    @endforeach

    <div class="form-group row text-center">
        <div class="col-sm-3"></div>
        <div class="col-sm-6">
            <button type="submit" class="btn btn-primary" @if($users_columns->isEmpty()) disabled @endif><i class="fas fa-check"></i>
                更新
            </button>
        </div>
    </div>
</form>

