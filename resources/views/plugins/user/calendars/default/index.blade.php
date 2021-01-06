{{--
 * カレンダー画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category カレンダープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

カレンダー

<table class="table table-bordered">
    <thead>
    <tr class="thead d-none d-md-table-row">
        <th nowrap="">日</th>
        <th nowrap="">月</th>
        <th nowrap="">火</th>
        <th nowrap="">水</th>
        <th nowrap="">木</th>
        <th nowrap="">金</th>
        <th nowrap="">土</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">1</span>
            <span class="d-md-none">(日)</span>
        </td>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">2</span>
            <span class="d-md-none">(月)</span>
        </td>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">3</span>
            <span class="d-md-none">(火)</span>
        </td>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">4</span>
            <span class="d-md-none">(水)</span>
        </td>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">5</span>
            <span class="d-md-none">(木)</span>
        </td>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">6</span>
            <span class="d-md-none">(金)</span>
        </td>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">7</span>
            <span class="d-md-none">(土)</span>
        </td>
    </tr>
    <tr>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">8</span>
            <span class="d-md-none">(日)</span>
        </td>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">9</span>
            <span class="d-md-none">(月)</span>
        </td>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">10</span>
            <span class="d-md-none">(火)</span>
        </td>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">11</span>
            <span class="d-md-none">(水)</span>
        </td>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">12</span>
            <span class="d-md-none">(木)</span>
        </td>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">13</span>
            <span class="d-md-none">(金)</span>
        </td>
        <td nowrap="" class="d-block d-md-table-cell">
            <span class="">14</span>
            <span class="d-md-none">(土)</span>
        </td>
    </tr>
    </tbody>
</table>

@endsection
