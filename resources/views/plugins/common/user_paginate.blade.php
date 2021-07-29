{{--
 * 一般プラグインのページング処理テンプレート
 *
 * @param $posts ページングがあるデータ
 * @param $frame フレーム
 * @param $class navタグの追加cssクラス (任意)
 * @param $appends array ペジネーションリンクのクエリ文字列を加える (任意)
 * @param $aria_label_name aria-label名 (任意)
--}}
@php
    $aria_label = isset($aria_label_name) ? "{$aria_label_name}のページ付け" : 'ページ付け';
    $class= isset($class) ? $class : null;
    $appends = isset($appends) ? $appends : null;
@endphp

{{-- アクセシビリティ対応。1ページしかない時に、空navを表示するとスクリーンリーダーに不要な Navigation がひっかかるため表示させない。 --}}
@if ($posts->lastPage() > 1)
    <nav class="text-center {{$class}}" aria-label="{{$aria_label}}">
        @if (is_array($appends))
            {{ $posts->appends($appends)->fragment('frame-' . $frame->id)->links(null, ['frame' => $frame]) }}
        @else
            {{ $posts->fragment('frame-' . $frame->id)->links(null, ['frame' => $frame]) }}
        @endif
    </nav>
@endif
