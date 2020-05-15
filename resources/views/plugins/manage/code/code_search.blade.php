{{--
 * 検索条件一覧画面のメインテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コード管理
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
<div class="card-header p-0">

{{-- 機能選択タブ --}}
@include('plugins.manage.code.code_manage_tab')

</div>
<div class="card-body">

<div class="alert alert-info" role="alert">
    検索条件を記録できます。<br>
    記録した検索条件は、コード一覧に検索ボタンとして表示され、押すとその条件で検索します。
</div>

{{-- 一覧エリア --}}
<div class="text-right mt-3"><span class="badge badge-pill badge-light">{{ $codes_searches->total() }} 件</span></div>
<table class="table table-bordered table_border_radius table-hover cc-font-90">
<tbody>
    <tr class="bg-light d-none d-sm-table-row">
        <th class="d-block d-sm-table-cell text-break">検索ラベル名</th>
        <th class="d-block d-sm-table-cell text-break">検索条件</th>
        <th class="d-block d-sm-table-cell text-break">表示順</th>
    </tr>

    @foreach($codes_searches as $codes_search)
    <tr>
        <th class="d-block d-sm-table-cell bg-light">
            <a href="{{url('/')}}/manage/code/searchEdit/{{$codes_search->id}}?page={{$paginate_page}}"><i class="far fa-edit"></i></a>
            <span class="d-sm-none">検索ラベル名：</span>{{$codes_search->name}}
        </th>
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">検索条件：</span>{{$codes_search->search_words}}</td>
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">表示順：</span>{{$codes_search->display_sequence}}</td>
    </tr>
    @endforeach
</tbody>
</table>

{{ $codes_searches->links() }}

</div>
</div>

@endsection
