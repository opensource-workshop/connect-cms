{{--
 * 登録画面テンプレート。カードタイプ（2列）。defaultをベースにしている。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if (empty($setting_error_messages))

    {{-- ヘッダー部分 --}}
    @include('plugins.user.databases.default.databases_include_ctrl_head')

    @if ($default_hide_list)
    @else

<div class="row" >

    @forelse($inputs as $input)
        @php
            $first_column_flag = true;
        @endphp
        <div class="col-12 col-sm-12 col-md-6 col-lg-6 pb-3">
            <div class="database_data border p-3">
                <a href="{{url('/')}}/plugin/databases/detail/{{$page->id}}/{{$frame_id}}/{{$input->id}}#frame-{{$frame_id}}" @if ($input->title) title="{{$input->title}}の詳細" @endif  style="text-decoration: none; color: initial;">
                    <div class="container">
                        {{-- 行グループ ループ --}}
                        @foreach($group_rows_cols_columns as $group_row_cols_columns)
                            <div class="row database_list_index_row_{{$loop->index}}">

                            {{-- 列グループ ループ --}}
                            @foreach($group_row_cols_columns as $group_col_columns)
                                <div class="col-sm database_list_index_col_{{$loop->index}}">
                                {{-- カラム ループ --}}
                                @foreach($group_col_columns as $column)
                                    @if ($first_column_flag == true)
                                        {{-- 1列目、且つ、一番最初の項目はタイトル項目とする為、強調表示＆表頭の表示なし --}}
                                        <div class="database_list_col_{{$column->id}}">
                                            <h2>
                                                @include('plugins.user.databases.default.databases_include_value')
                                            </h2>
                                        </div>
                                        @php $first_column_flag = false; @endphp
                                    @else
                                        {{-- 上記以外は通常表示 --}}
                                        <dl class="database_list_col_{{$column->id}}">
                                            @if ($column->label_hide_flag == '0')
                                                <dt>{{$column->column_name}}</dt>
                                            @endif
                                            <dd>
                                                @include('plugins.user.databases.default.databases_include_value')
                                            </dd>
                                        </dl>
                                    @endif
                                @endforeach
                                </div>
                            @endforeach
                            </div>
                        @endforeach
                    </div>
                </a>

                {{-- 編集 --}}
                <div class="row mt-2">
                    <div class="col">
                        <div class="text-right">
                            @if ($input->status == 2)
                                @can('role_update_or_approval',[[$input, $frame->plugin_name, $buckets]])
                                    <span class="badge badge-warning align-bottom">承認待ち</span>
                                @endcan
                                @can('posts.approval',[[$input, $frame->plugin_name, $buckets]])
                                    <form action="{{url('/')}}/plugin/databases/approval/{{$page->id}}/{{$frame_id}}/{{$input->id}}#frame-{{$frame_id}}" method="post" name="form_approval" class="d-inline">
                                        {{ csrf_field() }}
                                        <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                                            <i class="fas fa-check"></i> <span class="hidden-xs">承認</span>
                                        </button>
                                    </form>
                                @endcan
                            @endif
                            @can('posts.update',[[$input, $frame->plugin_name, $buckets]])
                                @if ($input->status == 1)
                                    <span class="badge badge-warning align-bottom">一時保存</span>
                                @endif

                                <button type="button" class="btn btn-success btn-sm ml-2" onclick="location.href='{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}/{{$input->id}}#frame-{{$frame_id}}'">
                                    <i class="far fa-edit"></i> 編集
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>

            </div>
        </div>

    @empty
        {{-- 検索結果0件 --}}
        @if (session('is_search.'.$frame_id))
            @if ($database_frame->search_results_empty_message)
                {{$database_frame->search_results_empty_message}}
            @else
                {{ __('messages.search_results_empty') }}
            @endif
        @endif
    @endforelse

    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $inputs, 'frame' => $frame, 'aria_label_name' => $database_frame->databases_name])

</div>

    @endif

@else
    @can('frames.edit',[[null, null, null, $frame]])
    {{-- フレームに紐づくコンテンツがない場合等、表示に支障がある場合は、データ登録を促す等のメッセージを表示 --}}
    <div class="card border-danger">
        <div class="card-body">
            @foreach ($setting_error_messages as $setting_error_message)
                <p class="text-center cc_margin_bottom_0">{{ $setting_error_message }}</p>
            @endforeach
        </div>
    </div>
    @endcan
@endif
@endsection
