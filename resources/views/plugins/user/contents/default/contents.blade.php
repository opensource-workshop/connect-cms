{{--
 * 表示画面テンプレート。データのみ。HTMLは解釈する。
 *
 * フレームが作られた直後の状態では、$contents が存在せずにnull の場合があるので、チェックして切り替えている。
 * 
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@if ($contents)
{!! $contents->content_text !!}
    @auth

    <p class="text-right">
        {{-- 変更画面へのリンク --}}
        @if ($frame->page_id == $page->id)
        <a href="{{url('/')}}/plugin/contents/edit/{{$page->id}}/{{$frame_id}}/{{$contents->id}}#{{$frame_id}}">
            <span class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-edit"></span> <span class="hidden-xs">編集</span></span>
        </a>
        @endif
    </p>
    @endauth
@else
    @auth
    <p class="text-right">
        {{-- 追加画面へのリンク --}}
        <a href="{{url('/')}}/plugin/contents/edit/{{$page->id}}/{{$frame_id}}#{{$frame_id}}">
            <span class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-edit"></span> <span class="hidden-xs">編集</span></span>
        </a>
    </p>
    @endauth
@endif
