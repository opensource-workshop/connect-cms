{{--
 * 連番管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 連番管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
<div class="card-header p-0">

{{-- 機能選択タブ --}}
@include('plugins.manage.number.number_manage_tab')

{{-- クリアボタンのアクション --}}
<script type="text/javascript">
    function form_clear(id) {
        if (confirm('連番をクリアします。\nよろしいですか？')) {
            form_clear_no.action = "{{url('/manage/number/clearSerialNumber')}}/" + id;
            form_clear_no.submit();
        }
    }
</script>

<form action="" method="POST" name="form_clear_no" class="">
    {{ csrf_field() }}
</form>

</div>
<div class="card-body">

<table class="table table-bordered table_border_radius table-hover cc-font-90">
<tbody>
    <tr class="bg-light d-none d-sm-table-row">
        <th class="d-block d-sm-table-cell">プラグイン</th>
        <th class="d-block d-sm-table-cell">データ名</th>
        <th class="d-block d-sm-table-cell">buckets_id</th>
        <th class="d-block d-sm-table-cell">prefix</th>
        <th class="d-block d-sm-table-cell">連番</th>
        <th class="d-block d-sm-table-cell"><i class="fas fa-eraser"></i></th>
    </tr>
    @foreach($numbers as $number)
    <tr>
        @if (isset($number->plugin_name))
        <th class="d-block d-sm-table-cell bg-light"><span class="d-sm-none">プラグイン：</span>{{$number->plugin_name_full}}</th>
        @else
        <th class="d-block d-sm-table-cell bg-light"><span class="d-sm-none">プラグイン：</span>(指定なし)</th>
        @endif

        @if (!empty($number->bucket_name))
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">データ名：</span>{{$number->bucket_name}}</td>
        @else
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">データ名：</span>(指定なし)</td>
        @endif

        @if (!empty($number->buckets_id))
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">buckets_id：</span>{{$number->buckets_id}}</td>
        @else
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">buckets_id：</span>(指定なし)</td>
        @endif

        @if (!empty($number->prefix))
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">prefix：</span>{{$number->prefix}}</td>
        @else
        <td class="d-block d-sm-table-cell"><span class="d-sm-none">prefix：</span>(指定なし)</td>
        @endif

        <td class="d-block d-sm-table-cell"><span class="d-sm-none">連番：</span>{{$number->serial_number}}</td>

        <td class="d-block d-sm-table-cell pb-sm-0"><span class="d-sm-none">連番クリア：</span>
            <a href="javascript:form_clear('{{$number->id}}');"><span class="btn btn-danger btn-sm"><i class="fas fa-eraser"></i></span></a>
        </td>
    </tr>
    @endforeach
</tbody>
</table>


    <form action="/manage/number/update" method="POST">
        {{csrf_field()}}

        {{-- サイト名 --}}
{{--
        <div class="form-group">
            <label class="col-form-label">サイト名</label>
            <input type="text" name="base_site_name" value="{{$configs["base_site_name"]}}" class="form-control">
            <small class="form-text text-muted">サイト名（各ページで上書き可能 ※予定）</small>
        </div>
--}}

        {{-- Submitボタン --}}
{{--
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
        </div>
--}}
    </form>
</div>
</div>

@endsection
