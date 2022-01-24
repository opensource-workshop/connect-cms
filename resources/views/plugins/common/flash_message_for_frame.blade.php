{{--
 * 該当フレームのみ、登録後メッセージ表示
--}}
@if (session('flash_message_for_frame' . $frame_id))
    <div class="alert alert-success">
        <i class="fas fa-exclamation-circle"></i> {!! session('flash_message_for_frame' . $frame_id) !!}
    </div>
@endif
