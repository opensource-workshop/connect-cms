{{--
 * 貸し出し中一覧画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<table class="table table-bordered cc_responsive_table">
<thead>
    <tr class="active">
        <th>ログインID</th>
        <th>タイトル</th>
        <th>返却予定日</th>
    </tr>
</thead>
<tbody>
    @foreach($books_lents as $books_lent)
    <tr>
        <td>{{$books_lent->student_no}}</td>
        <td>{{$books_lent->title}}</td>
        <td>{{$books_lent->return_scheduled}}</td>
    </tr>
    @endforeach
</tbody>
</table>

@endsection
