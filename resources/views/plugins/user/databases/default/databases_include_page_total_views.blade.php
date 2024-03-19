{{--
 * 現在表示している件数テキスト
 *
 * @author 石垣 佑樹 <ishigaki@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
{{-- 現在表示している件数 --}}
@php
    $page_total_views = FrameConfig::getConfigValueAndOld($frame_configs, DatabaseFrameConfig::database_page_total_views, ShowType::not_show);
@endphp
@if ($page_total_views == ShowType::show && $inputs->isNotEmpty())
    <div class="text-right mb-2 database-page-total-views">
        <span class="database-page-total-views-text">{{$inputs->firstItem()}}～{{$inputs->lastItem()}} 件を表示 ／ 全 {{$inputs->total()}} 件</span>
    </div>
@endif
