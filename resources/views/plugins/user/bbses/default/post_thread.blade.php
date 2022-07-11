{{-- @if(isset($view_post->depth))
        @for ($i = 0; $i < $view_post->depth; $i++)
        <div class="float-left mr-2">
            @if ($i  === $view_post->depth - 1)
                |
            @else 

                &nbsp;
            @endif
        </div>
        @endfor
    @endif --}}

@php 

$margin_left = 0;
for ($i = 1; $i < $view_post->depth; $i++) {
    $margin_left = $margin_left + 1;
}

@endphp

<div class="card mt-3" style="margin-left: {{ $margin_left }}rem;">
    <div class="card-header">
        @include('plugins.user.bbses.default.post_title', ['view_post' => $view_post, 'current_post' => null, 'list_class' => ''])
        <span class="float-right">
            @include('plugins.user.bbses.default.post_created_at_and_name', ['post' => $view_post])
        </span>
    </div>
    <div class="card-body">
        {!!$view_post->body!!}
        @if (!$current_post || $current_post->id != $view_post->id)
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
        @include('plugins.user.bbses.default.post_thread', ['view_post' => $child, 'current_post' => null, 'list_class' => 'mb-2'])
    @endforeach
@endif