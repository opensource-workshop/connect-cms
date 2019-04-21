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
{!! nl2br($contents->content_text) !!}
    @auth

    <p class="text-right">
        {{-- 変更画面へのリンク --}}
        <a href="{{$page->permanent_link}}?action=edit&frame_id={!!$frame_id!!}&id={!!$contents->id!!}#{!!$frame_id!!}"><span class="glyphicon glyphicon-edit"></a>
    </p>
    @endauth
@else
    @auth
    <p class="text-right">
        {{-- 追加画面へのリンク --}}
        <a href="{{$page->permanent_link}}?action=edit&frame_id={!!$frame_id!!}#{!!$frame_id!!}"><span class="glyphicon glyphicon-edit"></a>
    </p>
    @endauth
@endif
