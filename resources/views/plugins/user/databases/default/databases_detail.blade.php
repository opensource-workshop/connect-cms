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

@can('posts.update', [[$inputs, $frame->plugin_name, $buckets]])
<div class="row mt-2">
    <div class="col-12 text-right mb-1">
        @if ($inputs->status == 2)
            @can('role_update_or_approval',[[$inputs, $frame->plugin_name, $buckets]])
                <span class="badge badge-warning align-bottom">承認待ち</span>
            @endcan
            @can('posts.approval',[[$inputs, $frame->plugin_name, $buckets]])
                <form action="{{url('/')}}/plugin/databases/approval/{{$page->id}}/{{$frame_id}}/{{$inputs->id}}#frame-{{$frame_id}}" method="post" name="form_approval" class="d-inline">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                        <i class="fas fa-check"></i> <span class="hidden-xs">承認</span>
                    </button>
                </form>
            @endcan
        @endif
        @can('posts.update',[[$inputs, $frame->plugin_name, $buckets]])
            @if ($inputs->status == 1)
                <span class="badge badge-warning align-bottom">一時保存</span>
            @endif
        @endcan

        <button type="button" class="btn btn-success btn-sm ml-2" onclick="location.href='{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}/{{$inputs->id}}#frame-{{$frame_id}}'">
            <i class="far fa-edit"></i> 編集
        </button>
    </div>
</div>
@endcan

{{-- 一覧へ戻る --}}
<div class="row">
    <div class="col-12 text-center mt-3">
        @if(Session::has('page_no.'.$frame_id))
        <a href="{{url('/')}}{{$page->getLinkUrl()}}?frame_{{$frame_id}}_page={{Session::get('page_no.'.$frame_id)}}#frame-{{$frame_id}}">
        @else
        <a href="{{url('/')}}{{$page->getLinkUrl()}}#frame-{{$frame_id}}">
        @endif
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">{{__('messages.to_list')}}</span></span>
        </a>
    </div>
</div>

@endsection
