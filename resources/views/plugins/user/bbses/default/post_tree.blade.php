{{--
 * 掲示板の記事テンプレート（ツリー形式）
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

<div class="card mt-3 cc-bbs-indent-{{$indent}}">
    {{-- タイトル行 --}}
    <div class="card-header">
        @include('plugins.user.bbses.default.post_title_div', ['view_post' => $view_post, 'current_post' => $post, 'list_class' => ''])
    </div>
    {{-- 本文 --}}
    <div class="card-body">
        {!!$view_post->body!!}
        @if (!isset($current_post) || $current_post->id != $view_post->id)
            {{-- いいねボタン --}}
            @include('plugins.common.like', [
                'use_like' => $bbs->use_like,
                'like_button_name' => $bbs->like_button_name,
                'contents_id' => $view_post->id,
                'like_id' => $view_post->like_id,
                'like_count' => $view_post->like_count,
                'like_users_id' => $view_post->like_users_id,
            ])
        @endif
    </div>
</div>
{{-- 子ページの出力 --}}
@if (count($view_post->children) > 0)
    @foreach($view_post->children as $child)
        @include('plugins.user.bbses.default.post_tree', ['view_post' => $child, 'current_post' => $post, 'list_class' => ''])
    @endforeach
@endif
