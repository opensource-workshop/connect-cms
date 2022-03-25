{{--
 * メール送信画面のテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.user.user_manage_tab')
    </div>
    <div class="card-body">

        @include('plugins.common.errors_form_line')

        <form action="{{url('/')}}/manage/user/mailSend/{{$user->id}}" method="POST">
            {{csrf_field()}}

            <input type="hidden" name="mail" value="{{$user->email}}">

            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right">件名</label>
                <div class="col">
                    <input type="text" name="subject" value="{{ old('subject', $subject) }}" class="form-control">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-3 col-form-label text-md-right pt-0">本文</label>
                <div class="col-md-9">
                    <textarea name="body" class="form-control" rows=8>{{ old('body', $body) }}</textarea>
                </div>
            </div>

            <div class="form-group row text-center">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/user')}}'">
                        <i class="fas fa-times"></i> キャンセル
                    </button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-envelope"></i> 送信</button>
                </div>
            </div>
        </form>

    </div>
</div>

@endsection
