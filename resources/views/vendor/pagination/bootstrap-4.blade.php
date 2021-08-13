@if ($paginator->hasPages())
    <ul class="pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
        @else
            <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $parent_index => $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="page-item disabled d-none d-sm-block"><span class="page-link">{{ $element }}</span></li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)

                    @php
                        // スマホの場合、最初と最後、カレントは表示
                        if ($page == 1 || $page == $paginator->lastPage() || $page == $paginator->currentPage()) {
                            $sm_class = '';
                        } else {
                            // bugfix: ページ送り（paginate）が左右エリアに配置するとはみ出る事に対応
                            // $sm_class = ' d-none d-sm-block';
                            $is_expand_narrow = isset($frame) ? $frame->isExpandNarrow() : false;

                            // 右・左エリアなら、スマホ表示と同様にする
                            if ($is_expand_narrow) {
                                $sm_class = ' d-none';
                            } else {
                                $sm_class = ' d-none d-sm-block';
                            }
                        }
                    @endphp

                    {{-- 1ページ目と2ページ目以外は、カレントの前に「...」表示 --}}
                    @if ($page == $paginator->currentPage() && $page != 1 && $page != 2)
                        <li class="page-item disabled d-block d-md-none"><span class="page-link">...</span></li>
                    @endif

                    @if ($page == $paginator->currentPage())
                        <li class="page-item active{{$sm_class}}"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item{{$sm_class}}"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif

                    {{-- 最終ページと最終ページの1ページ前以外は、カレントの後に「...」表示 --}}
                    @if ($page == $paginator->currentPage() && $page != $paginator->lastPage() && $page != (intval($paginator->lastPage()) - 1))
                        <li class="page-item disabled d-block d-md-none"><span class="page-link">...</span></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a></li>
        @else
            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
        @endif
    </ul>
@endif
