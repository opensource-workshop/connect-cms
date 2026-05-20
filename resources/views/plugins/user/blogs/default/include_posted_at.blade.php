{{--
 * ブログ投稿日時の表示部品。
 *
 * フレーム設定が未保存の場合は、呼び出し元テンプレートの改修前表示に合わせるため、
 * default_display_posted_time で時刻表示の既定値を指定できる。
--}}
@php
    $posted_at_format = FrameConfig::getConfigValue($frame_configs, BlogFrameConfig::blog_posted_at_format, BlogPostedAtFormat::japanese);
    $default_display_posted_time = $default_display_posted_time ?? ShowType::show;
    $display_posted_time = FrameConfig::getConfigValue($frame_configs, BlogFrameConfig::blog_display_posted_time, $default_display_posted_time);
@endphp
<time class="blog-posted-at" datetime="{{$posted_at->format('c')}}">
    <span class="blog-posted-at-date">{{BlogPostedAtFormat::formatDate($posted_at, $posted_at_format)}}</span>@if ($display_posted_time == ShowType::show)<span class="blog-posted-at-time"> {{$posted_at->format(BlogPostedAtFormat::getTimeFormat($posted_at_format))}}</span>@endif
</time>
