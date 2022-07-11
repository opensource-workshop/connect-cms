{{--
 * 掲示板の記事のタイトル行テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上　雅人 <inoue@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}
@php 

$margin_left = 0;
for ($i = 0; $i < $view_post->depth; $i++) {
    $margin_left = $margin_left + 1;
}

@endphp


@if ($plugin_frame->list_underline)
<div class="border-bottom clearfix {{$list_class}}" style="margin-left: {{ $margin_left }}rem;">
@else
<div class="clearfix {{$list_class}}" style="margin-left: {{ $margin_left }}rem;">
@endif
    <div class="float-left">
        {{-- 根記事のみ件名の前にアイコンを表示する --}}
        {{-- @if (!isset($view_post->depth)) --}}
            <i class="fas fa-chevron-circle-right"></i>
        {{-- @endif --}}
        @include('plugins.user.bbses.default.post_title', ['view_post' => $view_post, 'current_post' => $current_post])
    </div>
    {{-- 投稿日時、投稿者 --}}
    <div class="float-right">
        @include('plugins.user.bbses.default.post_created_at_and_name', ['post' => $view_post])
    </div>
</div>

{{-- 子ページの出力 --}}
@if ((!isset($not_show_child) || !$not_show_child) && count($view_post->children) > 0)
    @foreach($view_post->children as $child)
        @include('plugins.user.bbses.default.post_title_div_thread', ['view_post' => $child, 'current_post' => null, 'list_class' => $list_class])
    @endforeach
@endif
