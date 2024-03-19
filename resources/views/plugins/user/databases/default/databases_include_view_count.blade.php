{{--
 * データベースの表示件数変更セレクトボックス
 *
 * @author 石垣 佑樹 <ishigaki@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
{{-- 件数変更 --}}
@php
    $view_count_spectator = FrameConfig::getConfigValueAndOld($frame_configs, DatabaseFrameConfig::database_view_count_spectator, ShowType::not_show);
@endphp
@if ($view_count_spectator == ShowType::show && $inputs->isNotEmpty())
    <div class="float-right mb-2 database-view-count-select">
        <form action="{{url('/')}}/redirect/plugin/databases/indexCount/{{$dest_frame->page->id}}/{{$dest_frame->id}}#frame-{{$dest_frame->id}}" method="POST" role="indexCount" aria-label="{{$database_frame->databases_name}}" name="view_count_spectator_{{$frame_id}}">
            {{ csrf_field() }}
            <input type="hidden" name="redirect_path" value="{{$dest_frame->page->getLinkUrl()}}?frame_{{$dest_frame->id}}_page=1#frame-{{$dest_frame->id}}">
            {{-- 表示件数リスト --}}
            @php
                // 1,5,10,20+表示件数でリストを作成する
                $view_count_options = [1, 5, 10, 20];
                if ($databases_frames->view_count) {
                    $view_count_options[] = $databases_frames->view_count;
                }
                $view_count_options = collect($view_count_options)->unique()->sort();
            @endphp

            <select class="form-control form-control-sm" name="view_count_spectator" onchange="document.forms.view_count_spectator_{{$frame_id}}.submit();">
                @foreach ($view_count_options as $num)
                    <option value="{{$num}}"  @if($view_count == $num) selected @endif>{{$num}}件</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="clearfix"></div>
@endif
