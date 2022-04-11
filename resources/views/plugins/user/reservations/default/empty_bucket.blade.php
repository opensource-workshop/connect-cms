{{--
 * 施設予約・バケツなし画面テンプレート。
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@can('frames.edit',[[null, null, null, $frame]])
    {{-- フレームに紐づくコンテンツがない場合、データ登録を促すメッセージを表示 --}}
    <div class="card border-danger">
        <div class="card-body">
            {{-- フレームに紐づく親データがない場合 --}}
            @if (!(isset($frame) && $frame->bucket_id))
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用する施設予約を選択するか、作成してください。</p>
            @endif
            {{-- 施設データがない場合 --}}
            @if ($facilities->isEmpty())
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、表示する施設を設定してください。</p>
            @endif
            {{-- 予約項目データがない場合 --}}
            {{-- @if ($columns->isEmpty())
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、予約項目データを作成してください。</p>
            @endif --}}
            {{-- 予約項目で選択肢のデータ型が指定されていた時に選択肢データがない場合 --}}
            {{-- @if (!$isExistSelect)
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、予約項目の選択肢データを作成してください。</p>
            @endif --}}
        </div>
    </div>
@endcan

@endsection
