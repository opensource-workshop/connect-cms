{{--
 * コード管理のメインテンプレート
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

<table class="table table-bordered table_border_radius table-hover">
<tbody>
    <tr class="bg-light d-none d-sm-table-row">
        <th class="d-block d-sm-table-cell">プラグイン</th>
{{--
        <th class="d-block d-sm-table-cell">データ名</th>
        <th class="d-block d-sm-table-cell">buckets_id</th>
        <th class="d-block d-sm-table-cell">prefix</th>
--}}
        <th class="d-block d-sm-table-cell">type_name</th>
        <th class="d-block d-sm-table-cell">type_code1</th>
        <th class="d-block d-sm-table-cell">type_code2</th>
{{--
        <th class="d-block d-sm-table-cell">type_code3</th>
        <th class="d-block d-sm-table-cell">type_code4</th>
        <th class="d-block d-sm-table-cell">type_code5</th>
--}}
        <th class="d-block d-sm-table-cell">コード</th>
        <th class="d-block d-sm-table-cell">値</th>
        <th class="d-block d-sm-table-cell">並び順</th>
    </tr>
    @foreach($codes as $code)
    <tr>
        @if (isset($code->plugin_name))
        <th class="d-block d-sm-table-cell bg-light">
            <a href="{{url('/')}}/manage/code/edit/{{$code->id}}"><i class="far fa-edit"></i></a>
            <span class="d-sm-none">プラグイン：</span>{{$code->plugin_name_full}}
        </th>
        @else
        <th class="d-block d-sm-table-cell bg-light">
            <a href="{{url('/')}}/manage/code/edit/{{$code->id}}"><i class="far fa-edit"></i></a>
            <span class="d-sm-none">プラグイン：</span>
        </th>
        @endif

{{--
        @if (!empty($code->bucket_name))
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">データ名：</span>{{$code->bucket_name}}</td>
        @else
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">データ名：</span></td>
        @endif

        @if (!empty($code->buckets_id))
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">buckets_id：</span>{{$code->buckets_id}}</td>
        @else
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">buckets_id：</span></td>
        @endif

        @if (!empty($code->prefix))
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">prefix：</span>{{$code->prefix}}</td>
        @else
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">prefix：</span></td>
        @endif
--}}
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">type_name：</span>{{$code->type_name}}</td>
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">type_code1：</span>{{$code->type_code1}}</td>
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">type_code2：</span>{{$code->type_code2}}</td>

{{--
        @if (!empty($code->type_code3))
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">type_code3：</span>{{$code->type_code3}}</td>
        @else
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">type_code3：</span></td>
        @endif

        @if (!empty($code->type_code4))
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">type_code4：</span>{{$code->type_code4}}</td>
        @else
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">type_code4：</span></td>
        @endif

        @if (!empty($code->type_code5))
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">type_code5：</span>{{$code->type_code5}}</td>
        @else
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">type_code5：</span></td>
        @endif
--}}

        <td class="d-block d-sm-table-cell"><span class="d-sm-none">コード：</span>{{$code->code}}</td>
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">値：</span>{{$code->value}}</td>
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">並び順：</span>{{$code->display_sequence}}</td>
    </tr>
    @endforeach
</tbody>
</table>

{{ $codes->links() }}

</div>
</div>

@endsection
