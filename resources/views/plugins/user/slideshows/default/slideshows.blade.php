{{--
 * 公開画面
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スライドショー・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

    {{-- 未設定エラーメッセージの表示等 --}}
    @include('plugins.common.errors_form_line')

    @if ($slideshows_items->count() > 0)
        {{-- インジケータの形状は標準だとクリックしづらいので、とりあえず丸型にしておきます。後々の改修でインジケータ形状のデザイン機能も入れられるといいと考えてます。 --}}
        <style>
            .carousel-indicators li{
                border-radius: 50%;
                height: 20px;
                width: 20px;
                background-color: #ffffff;
            }
        </style>
        <div id="carousel_{{ $frame_id }}" class="carousel slide {{ $slideshow->fade_use_flag == ShowType::show ? 'carousel-fade' : '' }}" data-ride="carousel" data-interval="{{ $slideshow->image_interval }}">
            <div class="carousel-inner">
                @foreach ($slideshows_items as $item)
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                        @if ($item->link_url)
                            {{-- 画像＋リンク --}}
                            <a
                                href="{{ $item->link_url }}"
                                @if ($item->link_target)
                                    target="{{ $item->link_target }}"
                                @endif
                            >
                                <img
                                    src="{{url('/')}}/file/{{ $item->uploads_id }}"
                                    class="d-block w-100"
                                    @if (!empty($slideshow->height)) style="object-fit: contain; height: {{$slideshow->height}}px;" @endif
                                >
                            </a>
                        @else
                            {{-- 画像のみ --}}
                            <img
                                src="{{url('/')}}/file/{{ $item->uploads_id }}"
                                class="d-block w-100"
                                @if (!empty($slideshow->height)) style="object-fit: contain; height: {{$slideshow->height}}px;" @endif
                            >
                        @endif
                        {{-- キャプション ※設定があれば表示 --}}
                        @if ($item->caption)
                            <div class="carousel-caption d-none d-md-block">
                                <h5>{{ $item->caption }}</h5>
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
            {{-- コントロール表示 --}}
            @if ($slideshow->control_display_flag == ShowType::show)
                <a class="carousel-control-prev" href="#carousel_{{ $frame_id }}" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carousel_{{ $frame_id }}" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            @endif
            {{-- インジケータ表示 --}}
            @if ($slideshow->indicators_display_flag == ShowType::show)
                <ol class="carousel-indicators">
                    @foreach ($slideshows_items as $item)
                        <li
                            data-target="#carousel_{{ $frame_id }}"
                            data-slide-to="{{ $loop->iteration - 1 }}"
                            @if ($loop->first)
                                class="active"
                            @endif
                        ></li>
                    @endforeach
                </ol>
            @endif
        </div>
    @endif
@endsection
