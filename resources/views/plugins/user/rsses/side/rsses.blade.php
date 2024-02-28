{{--
 * 公開画面
 *
 * @author horiguchi@opensource-workshop.jp
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category RSS・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

    {{-- 未設定エラーメッセージの表示等 --}}
    @include('plugins.common.errors_form_line')

    <div id="rsses_{{ $frame_id }}">
        @if (count($merge_urls) > 0)
            {{-- マージして表示する場合 --}}
            <div class="rsses-block-inner">
                <ul class="p-0">
                    @foreach ($merge_urls as $item)
                        @php
                            $title = '';
                            if (isset($item['title'])) {
                                $title = $item['title'];
                            }
                            $link = '';
                            if (isset($item['link'])) {
                                $link = $item['link'];
                                $link = $item['link'];
                            }
                            $description = '';
                            if (isset($item['description'])) {
                                $description = $item['description'];
                            }
                            $pubDate = '';
                            if (isset($item['pubDate'])) {
                                $pubDate = $item['pubDate'];
                            }
                            $caption = '';
                            if (isset($item['caption'])) {
                                $caption = $item['caption'];
                            }
                            $rss_title = '';
                            if (isset($item['rss_title'])) {
                                $rss_title = $item['rss_title'];
                            }
                        @endphp
                        <li class="row"><div class="col-12 pubdate">{{ $pubDate }}</div><div class="col-12 title">{{ $rss_title }}</div><div class="col-12 caption">{{ $caption }}</div><div class="col-12"><a href="{{ $link }}" target="_blank">{{ $title }}</a></div></li>
                    @endforeach
                </ul>
            </div>

        @elseif ($rss_urls->count() > 0)
            @foreach ($rss_urls as $urls)
                <div class="rsses-block-inner">
                    <div class="rsses-block-inner-title"><span class="title">{{ $urls->title }}</span><span class="caption ml-3">{{ $urls->caption }}</span></div>
                    @if (isset($urls->items))
                        {{-- url(取得元URLごとにループして表示する) --}}
                        <ul class="p-0">
                        @foreach ($urls->items as $item)
                            @php
                                $title = '';
                                if (isset($item['title'])) {
                                    $title = $item['title'];
                                }
                                $link = '';
                                if (isset($item['link'])) {
                                    $link = $item['link'];
                                    $link = $item['link'];
                                }
                                $description = '';
                                if (isset($item['description'])) {
                                    $description = $item['description'];
                                }
                                $pubDate = '';
                                if (isset($item['pubDate'])) {
                                    $pubDate = $item['pubDate'];
                                }
                            @endphp
                            <li class="row"><div class="col-12 pubdate">{{ $pubDate }}</div><div class="col-12"><a href="{{ $link }}" target="_blank">{{ $title }}</a></div></li>
                        @endforeach
                        </ul>
                    @else
                        <div>RSSを取得できませんでした</div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
@endsection
