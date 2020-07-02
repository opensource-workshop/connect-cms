{{--
 * 詳細表示画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@php
// 行グループ・列グループの配列に置き換えたcolumns
$group_rows_cols_columns = [];
foreach ($columns as $column) {

    // 詳細に表示する (detail_hide_flag=0)
    if ($column->detail_hide_flag == 0) {

        if (is_null($column->row_group) && is_null($column->column_group)) {
            // 行グループ・列グループどっちも設定なし
            //
            // row_group = null & column_group = nullは1行として扱うため、
            // $group_rows_cols_columns[row_group = 連番][column_group = ''で固定][columns_key = 0 で固定] とする
            // ※ arrayの配列keyにnullをセットすると、keyは''になるため、''をkeyに使用してます。
            $group_cols_columns = null;                         // 初期化
            $group_cols_columns[''][0] = $column;               // column_group = ''としてセット
            $group_rows_cols_columns[] = $group_cols_columns;   // row_groupは連番にするため、[]を使用
        } else {
            // 行グループ・列グループどっちか設定あり
            $group_rows_cols_columns[$column->row_group][$column->column_group][] = $column;
        }
    }
}
@endphp

<div class="container">
    {{-- 行グループ ループ --}}
    @foreach($group_rows_cols_columns as $group_row_cols_columns)
        <div class="row border-left border-right border-bottom @if($loop->first) border-top @endif">
        {{-- 列グループ ループ --}}
        @foreach($group_row_cols_columns as $group_col_columns)
            <div class="col-sm">
            {{-- カラム ループ --}}
            @foreach($group_col_columns as $column)
                <div class="row pt-2 pb-2">
                    <div class="col">
                        <small><b>{{$column->column_name}}</b></small><br>
                        @include('plugins.user.databases.default.databases_include_detail_value')
                    </div>
                </div>
            @endforeach
            </div>
        @endforeach
        </div>
    @endforeach
</div>

@can("role_article")
<div class="row mt-2">
    <div class="col-12 text-right mb-1">
        <button type="button" class="btn btn-success btn-sm" onclick="location.href='{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}/{{$inputs->id}}'">
            <i class="far fa-edit"></i> 編集
        </button>
    </div>
</div>
@endcan

{{-- 一覧へ戻る --}}
<div class="row">
    <div class="col-12 text-center mt-3">
        @if(Session::has('page_no.'.$frame_id))
        <a href="{{url('/')}}{{$page->getLinkUrl()}}?page={{Session::get('page_no.'.$frame_id)}}">
        @else
        <a href="{{url('/')}}{{$page->getLinkUrl()}}">
        @endif
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">{{__('messages.to_list')}}</span></span>
        </a>
    </div>
</div>

@endsection
