{{--
 * 投稿者絞り込み部
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}
@if ($blog_frame->narrowing_down_type_for_created_id === BlogNarrowingDownTypeForCreatedId::dropdown)
    <form action="{{url('/')}}/plugin/blogs/search/{{$page->id}}/{{$frame_id}}/#frame-{{$frame_id}}" method="GET" name="narrowing_down_type_for_created_id{{$frame_id}}">
        <select class="form-control form-control-sm" name="created_id" class="form-control" id="created_id_{{$frame_id}}" onchange="document.forms.narrowing_down_type_for_created_id{{$frame_id}}.submit();">
            <option value="">投稿者</option>
            @foreach($created_users as $created_user)
                <option value="{{$created_user->id}}" @if(session('created_id_'. $frame_id) === $created_user->id) selected @endif>{{$created_user->name}}</option>
            @endforeach
        </select>
    </form>
@endif
