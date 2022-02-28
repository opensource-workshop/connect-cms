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
        <div class="col-12 col-sm-12 col-md-6 col-lg-6 pb-3">
            <div class="database_data border p-3">
                <a href="{{url('/')}}/plugin/databases/detail/{{$page->id}}/{{$frame_id}}/{{$input->id}}#frame-{{$frame_id}}" @if ($input->title) title="{{$input->title}}の詳細" @endif  style="text-decoration: none; color: initial;">
                    <div class="container">
                        {{-- 行グループ ループ --}}
                        @foreach($group_rows_cols_columns as $group_row_cols_columns)
                            <div class="row database_list_index_row_{{$loop->index}}">
                            {{-- 列グループ ループ --}}
                            @foreach($group_row_cols_columns as $group_col_columns)

                                @if (isset($is_template_default_left_col_3))
                                    {{-- default-left-col-3テンプレート --}}
                                    @if ($loop->first)
                                        <div class="col-sm-3 database_list_index_col_{{$loop->index}}">
                                    @else
                                        <div class="col-sm database_list_index_col_{{$loop->index}}">
                                    @endif

                                @else
                                    {{-- defaultテンプレート --}}
                                    <div class="col-sm database_list_index_col_{{$loop->index}}">
                                @endif

                                {{-- カラム ループ --}}
                                @foreach($group_col_columns as $column)
                                    <div class="row pt-2 pb-2 database_list_col_{{$column->id}}">
                                        <div class="col">
                                            @if ($column->label_hide_flag == '0')
                                                <small><b>{{$column->column_name}}</b></small><br>
                                            @endif

                                            <div class="{{$column->classname}}">
                                                @include('plugins.user.databases.default.databases_include_value')
                                            </div>
                                            <div class="small {{ $column->caption_list_detail_color }}">{!! nl2br($column->caption_list_detail) !!}</div>
                                        </div>
                                    </div>
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
