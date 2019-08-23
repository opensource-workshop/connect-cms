{{--
 * パンくずメニュー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}
@if ($ancestors)

    @foreach($ancestors as $ancestor)
        {{$ancestor->page_name}}@if (!$loop->last) <span class="glyphicon glyphicon-chevron-right" style="color: rgb(192, 192, 192);"></span>@endif
    @endforeach
@endif
