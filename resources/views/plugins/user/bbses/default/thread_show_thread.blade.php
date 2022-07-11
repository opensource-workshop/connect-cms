@php 

$margin_left = 0;
for ($i = 1; $i < $tree_post->depth; $i++) {
    $margin_left = $margin_left + 1;
}

@endphp

{{-- 返信の場合は、親のpost を展開、詳細表示の場合は、自分のpost を展開 --}}
@if ((isset($reply_flag) && $reply_flag && $tree_post->id == $parent_post->id) ||
    ($tree_post->id == $post->id))
    <div class="card mb-2" style="margin-left: {{ $margin_left }}rem;">
        <div class="card-header">
            @include('plugins.user.bbses.default.post_title_div', ['view_post' => $tree_post, 'current_post' => $post, 'list_class' => ''])
        </div>
        <div class="card-body">
            {!!$tree_post->body!!}
        </div>
    </div>
@else
    @include('plugins.user.bbses.default.post_title_div_thread', ['view_post' => $tree_post, 'current_post' => null, 'list_class' => '', 'not_show_child' => true])
@endif

{{-- 子ページの出力 --}}
@if (count($tree_post->children) > 0)
    @foreach($tree_post->children as $child)
        @include('plugins.user.bbses.default.thread_show_thread', ['tree_post' => $child, 'current_post' => $post, 'list_class' => ''])
    @endforeach
@endif