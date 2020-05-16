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

{{-- 検索エリア --}}
<form action="{{url('/')}}/manage/code/index/{{$config->id}}" method="GET" class="form-horizontal">
    <div class="input-group">
        <input type="text" name="search_words" value="{{$search_words}}" class="form-control">
        <button type="button" class="btn text-muted" style="margin-left: -37px; z-index: 100;" onclick="location.href='{{url('/')}}/manage/code/index/?page=1'">
            <i class="fa fa-times"></i>
        </button>
        <div class="input-group-append">
            <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i> 検索</button>
        </div>
        <div class="ml-2">
            <a data-toggle="collapse" href="#collapse-search-help">
                <span class="btn btn-light"><i class="fas fa-question-circle"></i></span>
            </a>
        </div>
    </div>
</form>

{{-- 検索条件の補足 --}}
@include('plugins.manage.code.search_help')

{{-- ラベル検索エリア --}}
<div class="mt-3">
    {{--
    <button type="button" class="btn btn-secondary btn-sm" onclick="location.href='{{url('/')}}/manage/code/index/?page=1&search_words=type_code1=location'">
        場所マスタ <span class="badge badge-light">3</span>
    </button>
    --}}
    @foreach($codes_searches as $codes_search)
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="location.href='{{url('/')}}/manage/code/index/?page=1&search_words={{$codes_search->search_words}}'">
        <i class="fas fa-search"></i> {{$codes_search->name}}
    </button>
    @endforeach
</div>

{{-- 一覧エリア --}}
<div class="text-right mt-3"><span class="badge badge-pill badge-light">{{ $codes->total() }} 件</span></div>
<table class="table table-bordered table_border_radius table-hover cc-font-90">
<tbody>
    <tr class="bg-light d-none d-sm-table-row">
        <th class="d-block d-sm-table-cell text-break">プラグイン</th>
        @php
        $colums = [
            'codes_help_messages_name' => '注釈名',
            'buckets_name' => 'buckets_name',
            'buckets_id' => 'buckets_id',
            'prefix' => 'prefix',
            'type_name' => 'type_name',
            'type_code1' => 'type_code1',
            'type_code2' => 'type_code2',
            'type_code3' => 'type_code3',
            'type_code4' => 'type_code4',
            'type_code5' => 'type_code5',
            'code' => 'コード',
            'value' => '値',
            'additional1' => 'additional1',
            'additional2' => 'additional2',
            'additional3' => 'additional3',
            'additional4' => 'additional4',
            'additional5' => 'additional5',
            'display_sequence' => '表示順',
        ];
        @endphp
        @foreach($colums as $colum_key => $colum_value)
            @if(in_array($colum_key, $config->value_array) == $colum_key)
                <th class="d-block d-sm-table-cell text-break">{{$colum_value}}</th>
            @endif
        @endforeach
    </tr>

    @foreach($codes as $code)
    <tr>
        <th class="d-block d-sm-table-cell bg-light">
            <a href="{{url('/')}}/manage/code/edit/{{$code->id}}?page={{$paginate_page}}&search_words={{$search_words}}"><i class="far fa-edit"></i></a>
            <span class="d-sm-none">プラグイン：</span>{{$code->plugin_name_full}}
        </th>
        @foreach($colums as $colum_key => $colum_value)
            @if(in_array($colum_key, $config->value_array) == $colum_key)
            {{-- 表示例
            <td class="d-block d-sm-table-cell"><span class="d-sm-none">buckets_id：</span>$code->buckets_id</td>
            --}}
            <td class="d-block d-sm-table-cell"><span class="d-sm-none">{{$colum_value}}：</span>{{$code->$colum_key}}</td>
            @endif
        @endforeach
    </tr>
    @endforeach
</tbody>
</table>

{{ $codes->appends(['search_words' => $search_words])->links() }}

</div>
</div>

@endsection
