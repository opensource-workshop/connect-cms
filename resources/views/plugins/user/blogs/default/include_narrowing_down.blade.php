{{--
 * 絞り込み部
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}
{{-- カテゴリ絞込：ドロップダウン形式 --}}
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

    <form action="{{url('/')}}/plugin/blogs/search/{{$page->id}}/{{$frame_id}}/#frame-{{$frame_id}}" method="GET" name="narrowing_down{{$frame_id}}">
        <select class="form-control form-control-sm" name="categories_id" class="form-control" id="categories_id_{{$frame_id}}" onchange="document.forms.narrowing_down{{$frame_id}}.submit();">
            <option value="">カテゴリ</option>
            @foreach($blogs_categories as $category)
                <option value="{{$category->id}}" @if(session('categories_id_'. $frame_id) === $category->id) selected @endif>{{$category->category}}</option>
            @endforeach
        </select>
    </form>
@endif

{{-- 投稿者絞込：ドロップダウン形式 --}}
@if (FrameConfig::getConfigValue($frame_configs, BlogFrameConfig::narrowing_down_type_for_created_id) == BlogNarrowingDownTypeForCreatedId::dropdown)
    <form action="{{url('/')}}/plugin/blogs/search/{{$page->id}}/{{$frame_id}}/#frame-{{$frame_id}}" method="GET" name="narrowing_down_type_for_created_id{{$frame_id}}">
        <select class="form-control form-control-sm" name="created_id" class="form-control" id="created_id_{{$frame_id}}" onchange="document.forms.narrowing_down_type_for_created_id{{$frame_id}}.submit();">
            <option value="">投稿者</option>
            @foreach($created_users as $created_user)
                <option value="{{$created_user->id}}" @if(session('created_id_'. $frame_id) === $created_user->id) selected @endif>{{$created_user->name}}</option>
            @endforeach
        </select>
    </form>
@endif
