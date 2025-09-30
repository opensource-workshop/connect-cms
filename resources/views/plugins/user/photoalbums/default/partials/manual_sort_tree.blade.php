@php
    $children_map = $sorted_children_map ?? [];
    $current_sort_folder = ($sort_folder === null || $sort_folder === '') ? \App\Enums\PhotoalbumSort::name_asc : $sort_folder;
    $current_sort_file = ($sort_file === null || $sort_file === '') ? \App\Enums\PhotoalbumSort::name_asc : $sort_file;
    $children = collect();
    if (!empty($node) && array_key_exists($node->id, $children_map)) {
        $children = $children_map[$node->id];
    }
    $show_controls = isset($show_controls) ? $show_controls : true;
@endphp

@if ($children->isNotEmpty())
    <ul class="list-group list-group-flush {{ ($level ?? 0) > 0 ? 'mt-2' : '' }}">
        @foreach ($children as $child)
            @php
                $is_folder = $child->is_folder == \App\Models\User\Photoalbums\PhotoalbumContent::is_folder_on;
                $manual_enabled = $is_folder
                    ? $current_sort_folder == \App\Enums\PhotoalbumSort::manual_order
                    : $current_sort_file == \App\Enums\PhotoalbumSort::manual_order;
                $is_video = !$is_folder && \App\Models\Common\Uploads::isVideo($child->mimetype);
                $preview_url = '';
                if (!$is_folder) {
                    if (!empty($child->poster_upload_id)) {
                        $preview_url = url('/') . '/file/' . $child->poster_upload_id . '?size=small';
                    } elseif (!$is_video && !empty($child->upload_id) && optional($child->upload)->is_image) {
                        $preview_url = url('/') . '/file/' . $child->upload_id . '?size=small';
                    }
                }
                $previous_same = $children->slice(0, $loop->index)->reverse()->first(function ($item) use ($child) {
                    return $item->is_folder == $child->is_folder;
                });
                $next_same = $children->slice($loop->index + 1)->first(function ($item) use ($child) {
                    return $item->is_folder == $child->is_folder;
                });
                $can_move_up = $manual_enabled && !is_null($previous_same);
                $can_move_down = $manual_enabled && !is_null($next_same);
            @endphp
            {{-- 各行にアンカーIDを付与し並び替え後に同じ位置へ戻れるようにする --}}
            <li class="list-group-item photoalbum-manual-sort__item {{ session('photoalbum_sort_focus') == $child->id ? 'photoalbum-manual-sort__item--active' : '' }}" id="photoalbum-sort-item-{{ $child->id }}">
                <div class="d-flex justify-content-between align-items-center" style="padding-left: {{ ($level ?? 0) * 1.5 }}rem;">
                    <div class="d-flex align-items-center">
                        @if (!$is_folder && (!empty($preview_url) || $is_video))
                            <span class="photoalbum-manual-sort__thumb mr-2 {{ empty($preview_url) ? 'photoalbum-manual-sort__thumb--video' : '' }}">
                                @if (!empty($preview_url))
                                    <img src="{{ $preview_url }}" alt="{{ $child->displayName }}" class="photoalbum-manual-sort__thumb-image">
                                @else
                                    <i class="fas fa-video"></i>
                                @endif
                            </span>
                        @endif
                        @if ($child->is_folder)
                            <i class="fas fa-folder text-warning mr-2"></i>
                        @endif
                        <span class="font-weight-bold">{{ $child->displayName }}</span>
                    </div>
                    @if ($show_controls && $manual_enabled)
                        <div class="text-nowrap d-flex align-items-center">
                            <form action="{{ url('/') }}/redirect/plugin/photoalbums/updateViewSequence/{{ $page->id }}/{{ $frame_id }}#frame-{{ $frame_id }}" method="POST" class="d-inline">
                                {{ csrf_field() }}
                                <input type="hidden" name="redirect_path" value="{{ $redirect_path }}">
                                <input type="hidden" name="photoalbum_content_id" value="{{ $child->id }}">
                                <input type="hidden" name="display_sequence_operation" value="up">
                                {{-- リダイレクト後に押下行へスクロールさせるためのアンカー --}}
                                <input type="hidden" name="anchor_target" value="photoalbum-sort-item-{{ $child->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-secondary mr-1"
                                    @if (!$can_move_up) disabled @endif
                                >
                                    <i class="fas fa-arrow-up"></i>
                                </button>
                            </form>
                            <form action="{{ url('/') }}/redirect/plugin/photoalbums/updateViewSequence/{{ $page->id }}/{{ $frame_id }}#frame-{{ $frame_id }}" method="POST" class="d-inline">
                                {{ csrf_field() }}
                                <input type="hidden" name="redirect_path" value="{{ $redirect_path }}">
                                <input type="hidden" name="photoalbum_content_id" value="{{ $child->id }}">
                                <input type="hidden" name="display_sequence_operation" value="down">
                                {{-- リダイレクト後に押下行へスクロールさせるためのアンカー --}}
                                <input type="hidden" name="anchor_target" value="photoalbum-sort-item-{{ $child->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                    @if (!$can_move_down) disabled @endif
                                >
                                    <i class="fas fa-arrow-down"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                @include('plugins.user.photoalbums.default.partials.manual_sort_tree', [
                    'node' => $child,
                    'sorted_children_map' => $children_map,
                    'level' => ($level ?? 0) + 1,
                    'page' => $page,
                    'frame_id' => $frame_id,
                    'photoalbum' => $photoalbum,
                    'redirect_path' => $redirect_path,
                    'sort_folder' => $current_sort_folder,
                    'sort_file' => $current_sort_file,
                    'show_controls' => $show_controls,
                ])
            </li>
        @endforeach
    </ul>
@endif
