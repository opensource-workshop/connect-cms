@php
// $nodes: Illuminate\Support\Collection (toTree() した結果)
// $depth: int インデントレベル
// インデント記号
$prefix = str_repeat('— ', $depth ?? 0);
@endphp
@foreach($nodes as $node)
    <option value="{{$node->id}}">{{$prefix}}{{$node->name}}</option>
    @if ($node->children && $node->children->count())
        @include('plugins.user.cabinets.default.folder_options', ['nodes' => $node->children, 'depth' => ($depth ?? 0) + 1])
    @endif
@endforeach

