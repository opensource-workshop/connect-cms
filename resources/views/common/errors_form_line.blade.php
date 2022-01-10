{{--
 * @deprecation [非推奨] resources\views\plugins\common\errors_form_line.blade.php にコピー済み。当ソースは今後廃止予定。
 *--}}
@if ($errors && count($errors) > 0)
    <!-- Form Error List -->
    <div class="alert alert-danger">
        <strong>{{ __('messages.there_is_an_error') }}</strong>
        <ul>
            {{ __('messages.there_is_an_error_refer_to_the_message_of_each_item') }}
{{--
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
--}}
        </ul>
    </div>
@endif
