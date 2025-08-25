{{--
 * 年月絞り込み部
 *
 * @author 井上 雅人 <inoue@opensource-workshop.co.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}
@if ($blog_frame->narrowing_down_type_for_posted_month === BlogNarrowingDownTypeForPostedMonth::dropdown)
    <form action="{{url('/')}}/plugin/blogs/search/{{$page->id}}/{{$frame_id}}/#frame-{{$frame_id}}" method="GET" name="narrowing_down_posted_month{{$frame_id}}">
        <select class="form-control form-control-sm" name="posted_month" id="posted_month_{{$frame_id}}" onchange="document.forms.narrowing_down_posted_month{{$frame_id}}.submit();">
            <option value="">年月</option>
            @foreach($posted_months as $month)
                <option value="{{$month->posted_month}}" @if(session('posted_month_'. $frame_id) === $month->posted_month) selected @endif>{{$month->posted_month_label}}</option>
            @endforeach
        </select>
    </form>
@endif