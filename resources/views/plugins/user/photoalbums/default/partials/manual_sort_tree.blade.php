@php
    $children_map = $sorted_children_map ?? [];
    $current_sort_folder = ($sort_folder === null || $sort_folder === '') ? \App\Enums\PhotoalbumSort::name_asc : $sort_folder;
    $current_sort_file = ($sort_file === null || $sort_file === '') ? \App\Enums\PhotoalbumSort::name_asc : $sort_file;
    $children = collect();
    if (!empty($node) && array_key_exists($node->id, $children_map)) {
        $children = $children_map[$node->id];
    }
    $show_controls = isset($show_controls) ? $show_controls : true;
    $focus_open_ids = $focus_open_ids ?? [];
    $focus_open_map = array_fill_keys($focus_open_ids, true);
    $focus_content_id = session('photoalbum_sort_focus');
    $hidden_folder_map = $hidden_folder_map ?? [];
    $hidden_parent = $hidden_parent ?? false;
@endphp

@if ($children->isNotEmpty())
    <ul class="list-group list-group-flush photoalbum-manual-sort__list {{ ($level ?? 0) > 0 ? 'mt-2' : '' }}" data-photoalbum-parent-id="{{ $node->id }}">
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
                $has_children = $is_folder && array_key_exists($child->id, $children_map) && $children_map[$child->id]->isNotEmpty();
                $collapse_id = 'photoalbum-sort-children-' . $child->id;
                $should_open_children = $has_children && (isset($focus_open_map[$child->id]) || $child->id == $focus_content_id);
                $is_hidden = $is_folder && array_key_exists($child->id, $hidden_folder_map);
                $is_hidden_by_parent = $hidden_parent || $is_hidden;
            @endphp
            {{-- 各行にアンカーIDを付与し並び替え後に同じ位置へ戻れるようにする --}}
            <li class="list-group-item photoalbum-manual-sort__item {{ session('photoalbum_sort_focus') == $child->id ? 'photoalbum-manual-sort__item--active' : '' }} {{ $is_hidden_by_parent ? 'photoalbum-manual-sort__item--hidden' : '' }}" id="photoalbum-sort-item-{{ $child->id }}" data-photoalbum-content-id="{{ $child->id }}" data-photoalbum-is-folder="{{ $is_folder ? 1 : 0 }}">
                <div class="d-flex justify-content-between align-items-center" style="padding-left: {{ ($level ?? 0) * 1.5 }}rem;">
                    <div class="d-flex align-items-center">
                        @if ($is_folder)
                            <div class="photoalbum-visibility-toggle mr-2">
                                <input type="checkbox"
                                       class="photoalbum-visibility-toggle__input"
                                       id="photoalbum-hidden-{{ $child->id }}"
                                       name="hidden_folder_ids[]"
                                       value="{{ $child->id }}"
                                       form="photoalbum-frame-settings-{{ $frame_id }}"
                                       data-initial-hidden="{{ $is_hidden ? 1 : 0 }}"
                                       aria-label="表示/非表示"
                                       {{ $is_hidden ? 'checked' : '' }}
                                >
                                <label class="photoalbum-visibility-toggle__label" for="photoalbum-hidden-{{ $child->id }}" title="表示/非表示">
                                    <span class="sr-only">表示/非表示</span>
                                    <i class="fas fa-eye photoalbum-visibility-toggle__icon photoalbum-visibility-toggle__icon--on"></i>
                                    <i class="fas fa-eye-slash photoalbum-visibility-toggle__icon photoalbum-visibility-toggle__icon--off"></i>
                                </label>
                            </div>
                        @endif
                        @if (!$is_folder && (!empty($preview_url) || $is_video))
                            <span class="photoalbum-manual-sort__thumb mr-2 {{ empty($preview_url) ? 'photoalbum-manual-sort__thumb--video' : '' }}">
                                @if (!empty($preview_url))
                                    <img src="{{ $preview_url }}" alt="{{ $child->displayName }}" class="photoalbum-manual-sort__thumb-image" loading="lazy" decoding="async" width="64" height="64">
                                @else
                                    <i class="fas fa-video"></i>
                                @endif
                            </span>
                        @endif
                        @if ($is_folder && $has_children)
                            <button
                                type="button"
                                class="btn btn-link p-0 text-left photoalbum-manual-sort__toggle {{ $should_open_children ? '' : 'collapsed' }}"
                                data-toggle="collapse"
                                data-target="#{{ $collapse_id }}"
                                aria-expanded="{{ $should_open_children ? 'true' : 'false' }}"
                                aria-controls="{{ $collapse_id }}"
                            >
                                <i class="fas fa-chevron-right mr-2 photoalbum-manual-sort__toggle-icon"></i>
                                <i class="fas fa-folder text-warning mr-2"></i>
                                <span class="font-weight-bold">{{ $child->displayName }}</span>
                            </button>
                        @elseif ($is_folder)
                            <span class="d-flex align-items-center">
                                <i class="fas fa-folder text-warning mr-2"></i>
                                <span class="font-weight-bold">{{ $child->displayName }}</span>
                            </span>
                        @else
                            <span class="font-weight-bold">{{ $child->displayName }}</span>
                        @endif
                    </div>
                    @if ($show_controls && $manual_enabled)
                        <div class="text-nowrap d-flex align-items-center photoalbum-manual-sort__controls">
                            <form action="{{ url('/') }}/redirect/plugin/photoalbums/updateViewSequence/{{ $page->id }}/{{ $frame_id }}#frame-{{ $frame_id }}" method="POST" class="d-inline photoalbum-sequence-form">
                                {{ csrf_field() }}
                                <input type="hidden" name="redirect_path" value="{{ $redirect_path }}">
                                <input type="hidden" name="photoalbum_content_id" value="{{ $child->id }}">
                                <input type="hidden" name="display_sequence_operation" value="up">
                                {{-- リダイレクト後に押下行へスクロールさせるためのアンカー --}}
                                <input type="hidden" name="anchor_target" value="photoalbum-sort-item-{{ $child->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-secondary mr-1 photoalbum-sequence-button" data-sequence-operation="up"
                                    @if (!$can_move_up) disabled @endif
                                >
                                    <i class="fas fa-arrow-up"></i>
                                </button>
                            </form>
                            <form action="{{ url('/') }}/redirect/plugin/photoalbums/updateViewSequence/{{ $page->id }}/{{ $frame_id }}#frame-{{ $frame_id }}" method="POST" class="d-inline photoalbum-sequence-form">
                                {{ csrf_field() }}
                                <input type="hidden" name="redirect_path" value="{{ $redirect_path }}">
                                <input type="hidden" name="photoalbum_content_id" value="{{ $child->id }}">
                                <input type="hidden" name="display_sequence_operation" value="down">
                                {{-- リダイレクト後に押下行へスクロールさせるためのアンカー --}}
                                <input type="hidden" name="anchor_target" value="photoalbum-sort-item-{{ $child->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-secondary photoalbum-sequence-button" data-sequence-operation="down"
                                    @if (!$can_move_down) disabled @endif
                                >
                                    <i class="fas fa-arrow-down"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                @if ($has_children)
                    <div id="{{ $collapse_id }}" class="collapse {{ $should_open_children ? 'show' : '' }}">
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
                            'focus_open_ids' => $focus_open_ids,
                            'hidden_folder_map' => $hidden_folder_map,
                            'hidden_parent' => $is_hidden_by_parent,
                        ])
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
@endif
