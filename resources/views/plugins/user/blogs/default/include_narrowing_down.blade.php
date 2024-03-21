{{--
 * カテゴリ絞り込み部
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}
@if ($blog_frame->narrowing_down_type === BlogNarrowingDownType::dropdown)
    <style>
        @foreach($blogs_categories as $category)
            .blog-narrowing-down-button a.{{$category->classname}} {
                color:{{$category->color}};
                background-color:{{$category->background_color}};
            }
            .blog-narrowing-down-button a.{{$category->classname}}:hover {
                background-color:{{$category->hover_background_color}};
            }
        @endforeach
    </style>

    {{-- ドロップダウン形式 --}}
    <form action="{{url('/')}}/plugin/blogs/search/{{$page->id}}/{{$frame_id}}/#frame-{{$frame_id}}" method="GET" name="narrowing_down{{$frame_id}}">
        <select class="form-control form-control-sm" name="categories_id" class="form-control" id="categories_id_{{$frame_id}}" onchange="document.forms.narrowing_down{{$frame_id}}.submit();">
            <option value="">カテゴリ</option>
            @foreach($blogs_categories as $category)
                <option value="{{$category->id}}" @if(session('categories_id_'. $frame_id) === $category->id) selected @endif>{{$category->category}}</option>
            @endforeach
        </select>
    </form>
@endif
