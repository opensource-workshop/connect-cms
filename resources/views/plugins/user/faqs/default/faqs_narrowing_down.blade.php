{{--
 * FAQの絞り込み部
 *
 * @author 石垣 佑樹 <ishigaki@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category FAQプラグイン
--}}

@php
$narrowing_down_type = FrameConfig::getConfigValueAndOld($frame_configs, FaqFrameConfig::faq_narrowing_down_type, FaqNarrowingDownType::none);
@endphp

<style>
@foreach($faqs_categories as $category)
.faq-narrowing-down-button a.{{$category->classname}} {
    color:{{$category->color}};
    background-color:{{$category->background_color}};
}
.faq-narrowing-down-button a.{{$category->classname}}:hover {
    background-color:{{$category->hover_background_color}};
}
@endforeach
</style>

{{-- ドロップダウン形式 --}}
@if ($narrowing_down_type === FaqNarrowingDownType::dropdown)
<form action="{{url('/')}}/plugin/faqs/search/{{$page->id}}/{{$frame_id}}/#frame-{{$frame_id}}" method="GET" name="narrowing_down{{$frame_id}}">
    <div class="form-group">
        <select class="form-control" name="categories_id" class="form-control" id="categories_id_{{$frame_id}}" onchange="document.forms.narrowing_down{{$frame_id}}.submit();">
            <option value=""></option>
            @foreach($faqs_categories as $category)
            <option value="{{$category->id}}" @if(session('categories_id_'. $frame_id) === $category->id) selected @endif>{{$category->category}}</option>
            @endforeach
        </select>
    </div>
</form>

{{-- ボタン形式 --}}
@elseif ($narrowing_down_type === FaqNarrowingDownType::button)
<div class="mb-2 faq-narrowing-down-button">
@foreach($faqs_categories as $category)
    <a href="{{url('/')}}/plugin/faqs/search/{{$page->id}}/{{$frame_id}}/?categories_id={{$category->id}}#frame-{{$frame_id}}"
        class="badge badge-pill
        @if (session()->has('categories_id_'. $frame_id) && session('categories_id_'. $frame_id) !== $category->id)
            badge-secondary
        @else
            {{$category->classname}}
        @endif "
        id="a_category_button_{{$category->id}}"
    >
        {{$category->category}}
    </a>
@endforeach
</div>
@endif
