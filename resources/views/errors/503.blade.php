{{--
 * メンテナンスモード画面（503）
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
 *
 * @see resources\views\layouts\app.blade.php
 * @see vendor\laravel\framework\src\Illuminate\Foundation\Exceptions\views\503.blade.php
 * @see vendor\laravel\framework\src\Illuminate\Foundation\Exceptions\views\minimal.blade.php
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Fonts -->
    <link href="{{asset('fontawesome/css/all.min.css')}}" rel='stylesheet' type='text/css'>

    <!-- Scripts -->
    {{-- <script src="{{asset('js/app.js')}}"></script> --}}

    <title>サイトメンテナンス</title>
</head>
<body>
    @php
        $default_html = '
<div class="text-center mt-4">
    <h1>サイトメンテナンス</h1>
    <p>作業終了までしばらくお待ちください。</p>
</div>
';
    @endphp
    {!! __($exception->getMessage() ?: $default_html) !!}
</body>
</html>
