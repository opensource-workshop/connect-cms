{{--
 * 掲示板の記事テンプレート（ツリー形式 - 詳細表示している記事のみ展開）
 *
 * @author 石垣　佑樹 <ishigaki@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}

@php
$indent = $view_post->depth;
$max_indents = (int)FrameConfig::getConfigValue($frame_configs, BbsFrameConfig::tree_indents, App\Plugins\User\Bbses\BbsesPlugin::max_tree_indents);
if ($max_indents <= $indent)  {
    $indent = $max_indents;
}
@endphp

{{-- 返信の場合は、親のpost を展開、詳細表示の場合は、自分のpost を展開 --}}
@if ((isset($reply_flag) && $reply_flag && $view_post->id == $parent_post->id) ||
    ($view_post->id == $post->id))
    <div class="card mb-2 cc-bbs-indent-{{$indent}}">
        <div class="card-header">
            @include('plugins.user.bbses.default.post_title_div', ['view_post' => $view_post, 'current_post' => $post, 'list_class' => ''])
        </div>
        <div class="card-body">
            {!!$view_post->body!!}
        </div>
    </div>
@else
    @include('plugins.user.bbses.default.post_title_div_tree', ['view_post' => $view_post, 'current_post' => null, 'list_class' => '', 'not_show_child' => true])
@endif

{{-- 子ページの出力 --}}
@if (count($view_post->children) > 0)
    @foreach($view_post->children as $child)
        @include('plugins.user.bbses.default.post_tree_current_open', ['view_post' => $child, 'current_post' => $post, 'list_class' => ''])
    @endforeach
@endif
