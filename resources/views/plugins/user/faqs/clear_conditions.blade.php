{{--
 * FAQ条件クリア共通部品
 *
 * @author 井上 雅人 <inoue@opensource-workshop.co.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category FAQプラグイン
--}}

{{-- 条件クリアボタン --}}
@if(session('search_keyword_'. $frame_id) || session('categories_id_'. $frame_id))
<div class="mb-3">
    <form action="{{url('/')}}/plugin/faqs/search/{{$page->id}}/{{$frame_id}}/#frame-{{$frame_id}}" method="GET" style="display: inline;">
        <button type="submit" name="clear_search" value="1" class="btn btn-secondary btn-sm">
            <i class="fas fa-times" role="presentation"></i> 条件クリア
        </button>
    </form>
</div>
@endif