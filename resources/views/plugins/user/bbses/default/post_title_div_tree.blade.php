{{--
 * 掲示板の記事のタイトル行テンプレート（ツリー形式）
 *
 * @author 石垣　佑樹 <ishigaki@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}

{{-- 階層数 --}}
@php
$indent = $view_post->depth;
$max_indents = (int)FrameConfig::getConfigValue($frame_configs, BbsFrameConfig::tree_indents, App\Plugins\User\Bbses\BbsesPlugin::max_tree_indents);
if ($max_indents <= $indent)  {
    $indent = $max_indents;
}
@endphp

@if ($plugin_frame->list_underline)
<div class="border-bottom clearfix {{$list_class}} cc-bbs-indent-{{$indent}}">
@else
<div class="clearfix {{$list_class}} cc-bbs-indent-{{$indent}}">
@endif
    {{-- タイトル --}}
    <div class="float-left">
        <i class="fas fa-chevron-circle-right"></i>
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
        @include('plugins.user.bbses.default.post_title_div_tree', ['view_post' => $child, 'current_post' => null, 'list_class' => $list_class])
    @endforeach
@endif
