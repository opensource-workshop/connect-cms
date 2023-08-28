{{--
 * 項目セット一覧画面のメインテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.user.user_manage_tab')
    </div>
    <div class="card-body">

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        {{-- 一覧エリア --}}
        <div class="text-left"><span class="badge badge-pill badge-light">{{ $columns_sets->total() }} 件</span></div>
        <table class="table table-hover cc-font-90">
            <thead>
                <tr class="d-none d-sm-table-row">
                    <th class="d-block d-sm-table-cell text-break">項目セット名</th>
                    <th class="d-block d-sm-table-cell text-break">表示順</th>
                    <th class="d-block d-sm-table-cell text-break">項目</th>
                </tr>
            </thead>
            <tbody>
                @foreach($columns_sets as $columns_set)
                <tr>
                    <td class="d-block d-sm-table-cell">
                        <a href="{{url('/')}}/manage/user/editColumnSet/{{$columns_set->id}}"><i class="far fa-edit"></i></a>
                        <span class="d-sm-none">項目セット名：</span>{{$columns_set->name}}@if($columns_set->use_variable) ({{$columns_set->variable_name}}) @endif
                    </td>
                    <td class="d-block d-sm-table-cell"><span class="d-sm-none">表示順：</span>{{$columns_set->display_sequence}}</td>
                    <td class="d-block d-sm-table-cell">
                        <span class="d-sm-none">項目：</span>
                        <a href="{{url('/')}}/manage/user/editColumns/{{$columns_set->id}}"><i class="far fa-edit"></i></a>
                        {{$columns_set->column_name}}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $columns_sets->links() }}

    </div>
</div>

@endsection
