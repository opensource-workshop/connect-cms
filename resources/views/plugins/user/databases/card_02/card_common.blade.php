@php
    // 列数に応じてブートストラップのカラム幅を計算
    $card_columns = $card_columns ?? 2;
    $col_size = intval(12 / max(1, $card_columns));
    $col_class = "col-12 col-sm-12 col-md-{$col_size} col-lg-{$col_size} pb-3";
@endphp

@if (empty($setting_error_messages))

    {{-- ヘッダー部分 --}}
    @include('plugins.user.databases.default.databases_include_ctrl_head')

    @if ($default_hide_list)
    @else

        {{-- データベースの表示件数変更セレクトボックス --}}
        @include('plugins.user.databases.default.databases_include_view_count')
        {{-- 現在表示している件数テキスト --}}
        @include('plugins.user.databases.default.databases_include_page_total_views')

<div class="row">

    @php
        $database_show_like_list = FrameConfig::getConfigValueAndOld($frame_configs, DatabaseFrameConfig::database_show_like_list, ShowType::show);
    @endphp

    @forelse($inputs as $input)
        @php
            $first_column_flag = true;
        @endphp
        <div class="{{$col_class}}">
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
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="mb-1">
                                {{-- いいねボタン --}}
                                @include('plugins.common.like', [
                                    'use_like' => ($database_frame->use_like && $database_show_like_list),
                                    'like_button_name' => $database_frame->like_button_name,
                                    'contents_id' => $input->id,
                                    'like_id' => $input->like_id,
                                    'like_count' => $input->like_count,
                                    'like_users_id' => $input->like_users_id,
                                ])
                            </div>
                            <div class="text-right mb-1">
                                @if ($input->status == 2)
                                    @can('role_update_or_approval',[[$input, $frame->plugin_name, $buckets]])
                                        <span class="badge badge-warning align-bottom">承認待ち</span>
                                    @endcan
                                    @can('posts.approval',[[$input, $frame->plugin_name, $buckets]])
                                        <form action="{{url('/')}}/plugin/databases/approval/{{$page->id}}/{{$frame_id}}/{{$input->id}}#frame-{{$frame_id}}" method="post" name="form_approval" class="d-inline">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                                                <i class="fas fa-check"></i> <span class="d-none d-sm-inline">承認</span>
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                                @can('role_update_or_approval',[[$input, $frame->plugin_name, $buckets]])
                                    @if (!empty($input->expires_at) && $input->expires_at <= Carbon::now())
                                        <span class="badge badge-secondary align-bottom">公開終了</span>
                                    @endif

                                    @if ($input->posted_at > Carbon::now())
                                        <span class="badge badge-info align-bottom">公開前</span>
                                    @endif
                                @endcan
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
