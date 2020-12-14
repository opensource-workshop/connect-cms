{{--
 * トークンを使った本登録の確定画面テンプレート。
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
<form action="{{url('/')}}/plugin/forms/publicStoreToken/{{$page->id}}/{{$frame_id}}/{{$id}}?token={{$token}}#frame-{{$frame_id}}" name="form_add_column{{$frame_id}}" method="POST" class="form-horizontal">
    {{ csrf_field() }}

    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i> 本登録ボタンを押して登録を確定してください。
        <br>
        <i class="fas fa-exclamation-circle"></i> 本登録ボタンは１度だけ押してください。
        <br>
        <i class="fas fa-exclamation-circle"></i> 本登録処理が完了するまでには時間を要する場合があります。
        <br>
        <i class="fas fa-exclamation-circle"></i> 本登録完了メールが到着するまでには時間を要する場合があります。
        <br>
        <i class="fas fa-exclamation-circle"></i> 本登録完了後に再度、仮登録メールより本登録は行わないでください。
    </div>

    {{-- ボタンエリア --}}
    <div class="form-group text-center">
        <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('本登録します。よろしいですか？')">
        <i class="fas fa-check"></i> {{__('messages.main_regist')}}
        </button>
    </div>
</form>
@endsection
