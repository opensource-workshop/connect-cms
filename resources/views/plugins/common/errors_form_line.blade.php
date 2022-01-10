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
