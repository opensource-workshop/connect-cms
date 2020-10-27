{{--
 * ワーニングメッセージのテンプレート。
--}}
<div class="alert alert-warning mt-2">
    @foreach ($warning_messages as $warning_message)
        <i class="fas fa-exclamation-circle"></i> {!! nl2br(e($warning_message)) !!}<br>
    @endforeach
</div>
