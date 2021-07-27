{{--
 * 一般プラグインのページング処理テンプレート
 *
 * @param $posts ページングがあるデータ
 * @param $frame フレーム
 * @param $aria_label_name aria-label名
--}}
@php
    $aria_label = isset($aria_label_name) ? "{$aria_label_name}のページ付け" : 'ページ付け';
    // dd($frame);
@endphp

{{-- アクセシビリティ対応。1ページしかない時に、空navを表示するとスクリーンリーダーに不要な Navigation がひっかかるため表示させない。 --}}
@if ($posts->lastPage() > 1)
    <nav class="text-center" aria-label="{{$aria_label}}">
        {{ $posts->fragment('frame-' . $frame->id)->links(null, ['frame' => $frame]) }}
    </nav>
@endif
