<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>プレビューモード | {{ config('app.name', 'Connect-CMS') }}</title>
    <link href="{{ url('/') }}{{ mix('css/app.css') }}" rel="stylesheet">
    <script src="{{ url('/') }}{{ mix('/js/preview.js') }}" defer></script>
</head>
<body class="cc-preview-shell">
    <header class="cc-preview-toolbar" aria-label="プレビュー画面サイズ切替">
        <div class="btn-group btn-group-sm cc-preview-toolbar__group" role="group" aria-label="画面サイズ">
            @foreach ($devices as $device_name => $device_info)
                <button type="button"
                        class="btn {{ $device == $device_name ? 'btn-primary' : 'btn-light' }}"
                        data-preview-device-option="{{ $device_name }}"
                        data-preview-device-label="{{ $device_info['label'] }}"
                        @if ($device_info['width']) data-preview-width="{{ $device_info['width'] }}" @endif
                        @if ($device_info['height']) data-preview-height="{{ $device_info['height'] }}" @endif
                        aria-pressed="{{ $device == $device_name ? 'true' : 'false' }}">
                    {{ $device_info['label'] }}
                </button>
            @endforeach
        </div>

        <span class="cc-preview-size-label" aria-live="polite"></span>

        <div class="btn-group btn-group-sm cc-preview-toolbar__group" role="group" aria-label="表示倍率">
            <button type="button" class="btn btn-primary" data-preview-scale-option="fit" aria-pressed="true">画面に合わせる</button>
            <button type="button" class="btn btn-light" data-preview-scale-option="actual" aria-pressed="false">等倍表示</button>
        </div>

        <a href="{{ url($page_url) }}" class="btn btn-sm btn-secondary cc-preview-toolbar__exit">
            <i class="fas fa-times"></i> プレビュー終了
        </a>

        <p class="cc-preview-toolbar__help mb-0">表示幅の確認用です。実機とは一部異なる場合があります。</p>
    </header>

    <main class="cc-preview-stage"
          data-preview-device
          data-selected-device="{{ $device }}">
        <div class="cc-preview-canvas">
            <iframe class="cc-preview-frame"
                    src="{{ url($frame_url) }}"
                    title="{{ $devices[$device]['label'] }}のページプレビュー"></iframe>
        </div>
    </main>

    <noscript>
        <p class="alert alert-warning m-3">画面サイズを切り替えるにはJavaScriptを有効にしてください。</p>
    </noscript>
</body>
</html>
