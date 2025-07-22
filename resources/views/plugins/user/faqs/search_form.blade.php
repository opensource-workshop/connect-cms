{{--
 * FAQ検索フォーム共通部品
 *
 * @author 井上 雅人 <inoue@opensource-workshop.co.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category FAQプラグイン
--}}

@if (FrameConfig::getConfigValue($frame_configs, FaqFrameConfig::faq_keyword_search_display) == ShowType::show)
<div class="faq-search-form mb-3">
    <form action="{{url('/')}}/plugin/faqs/search/{{$page->id}}/{{$frame_id}}/#frame-{{$frame_id}}" method="GET" name="search_form{{$frame_id}}" role="search" aria-label="FAQ検索">
        <div class="input-group mb-2">
            <input type="text" 
                   class="form-control" 
                   name="search_keyword" 
                   value="{{session('search_keyword_'. $frame_id)}}" 
                   placeholder="検索はキーワードを入力してください。"
                   title="検索キーワード"
                   id="search_keyword_{{$frame_id}}">
            <div class="input-group-append">
                <button type="submit" class="btn btn-primary" title="検索">
                    <i class="fas fa-search" role="presentation"></i>
                </button>
            </div>
        </div>
    </form>
</div>
@endif