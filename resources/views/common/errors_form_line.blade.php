@if ($errors && count($errors) > 0)
    <!-- Form Error List -->
    <div class="alert alert-danger">
        <strong>エラーがあります。</strong>
        <ul>
            エラーの詳細は各項目のメッセージを参照してください。
{{--
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
--}}
        </ul>
    </div>
@endif
