{{--
 * 表示件数リスト テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}
@if (isset($blog_frame->use_view_count_spectator) && $blog_frame->use_view_count_spectator == 1)
    <form action="{{url('/')}}/redirect/plugin/blogs/index_count/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="get" name="view_count_spectator_down{{$frame_id}}">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/blogs/index/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
        <div class="float-right">
            {{-- 表示件数リスト --}}
            <select class="form-control form-control-sm" name="view_count_spectator" onchange="document.forms.view_count_spectator_down{{$frame_id}}.submit();">
                @php
                    $view_count_spectator = session('view_count_spectator_'. $frame_id, $count);
                @endphp
                <option value="1"  @if($view_count_spectator == 1)    selected @endif>1件</option>
                <option value="5"  @if($view_count_spectator == 5)    selected @endif>5件</option>
                <option value="10" @if($view_count_spectator == 10)   selected @endif>10件</option>
                <option value="20" @if($view_count_spectator == 20)   selected @endif>20件</option>
                {{-- 表示条件の表示件数がリストに含まれて無かったら選択肢追加 --}}
                @if(!in_array($count, [1,5,10,20]))
                <option value="{{$count}}"  @if($view_count_spectator == $count)    selected @endif>{{$count}}件</option>
                @endif
            </select>
        </div>
        {{-- floatの回り込み解除 --}}
        <div class="clearfix"></div>
    </form>
@endif
