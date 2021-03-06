{{--
 * 掲示板の記事のタイトル行テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上　雅人 <inoue@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}
@if ($plugin_frame->list_underline)
<div class="border-bottom clearfix {{$list_class}}">
@else
<div class="clearfix {{$list_class}}">
@endif
    {{-- 記事タイトル --}}
    <div class="float-left">
        <i class="fas fa-chevron-circle-right"></i>
        @include('plugins.user.bbses.default.post_title', ['view_post' => $view_post, 'current_post' => $current_post])
    </div>
    {{-- 投稿日時、投稿者 --}}
    <div class="float-right">
        @include('plugins.user.bbses.default.post_created_at_and_name', ['post' => $view_post])
    </div>
</div>
