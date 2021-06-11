{{--
 * 登録後メッセージ表示
--}}
@if (session('flash_message'))
    <div class="alert alert-success">
        <i class="fas fa-exclamation-circle"></i> {!! session('flash_message') !!}
    </div>
@endif
