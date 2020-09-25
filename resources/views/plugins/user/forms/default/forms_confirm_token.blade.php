{{--
 * トークンを使った本登録の確定画面テンプレート。
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
<form action="{{url('/')}}/plugin/forms/publicStoreToken/{{$page->id}}/{{$frame_id}}/{{$id}}?token={{$token}}#frame-{{$frame_id}}" name="form_add_column{{$frame_id}}" method="POST" class="form-horizontal">
    {{ csrf_field() }}

    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i> 本登録ボタンを押して登録を確定してください。
    </div>

    {{-- ボタンエリア --}}
    <div class="form-group text-center">
        <button class="btn btn-primary"><i class="fas fa-check"></i> {{__('messages.main_regist')}}</button>
    </div>
</form>
@endsection
