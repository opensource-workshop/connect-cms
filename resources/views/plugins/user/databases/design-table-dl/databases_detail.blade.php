{{--
 * 詳細表示画面テンプレート（defaultテンプレートをベース）
 * ・項目の部分をdl,dt,ddタグに置き換え
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<div class="container">
    {{-- 行グループ ループ --}}
    @php
        $row_group_count = 1;
        $column_group_count = 1;
    @endphp
    @foreach($group_rows_cols_columns as $group_row_cols_columns)
        <div class="row border-left border-right border-bottom @if($loop->first) border-top @endif {{ "row-group-" . $row_group_count++ }}">
        {{-- 列グループ ループ --}}
        @foreach($group_row_cols_columns as $group_col_columns)
            <div class="col-sm {{ "column-group-" . $column_group_count++ }}">
            {{-- カラム ループ --}}
                <dl>
                @foreach($group_col_columns as $column)
                    @if ($column_group_count == 2 && $loop->first)
                        {{-- 1列目、且つ、一番最初の項目はタイトル項目とする為、強調表示＆表頭の表示なし --}}
                        <h2>
                            @include('plugins.user.databases.default.databases_include_detail_value')
                        </h2>
                    @else
                        {{-- 上記以外は通常表示 --}}
                        @if ($column->label_hide_flag == '0')
                            <dt>{{$column->column_name}}</dt>
                        @endif
                        <dd>
                            @include('plugins.user.databases.default.databases_include_detail_value')
                        </dd>
                    @endif
                @endforeach
                </dl>
            </div>
        @endforeach
        </div>
    @endforeach
</div>

@can('role_update_or_approval', [[$inputs, $frame->plugin_name, $buckets]])
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

            <button type="button" class="btn btn-success btn-sm ml-2" onclick="location.href='{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}/{{$inputs->id}}#frame-{{$frame_id}}'">
                <i class="far fa-edit"></i> 編集
            </button>
        @endcan
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
