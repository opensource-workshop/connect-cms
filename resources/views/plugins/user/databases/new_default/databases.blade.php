{{--
 * 登録画面テンプレート。
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
        @foreach($inputs as $input)
            @php
            // 行グループ・列グループの配列に置き換えたcolumns
            $group_rows_cols_columns = [];
            foreach ($columns as $column) {

                // 一覧に表示する (list_hide_flag=0)
                if ($column->list_hide_flag == 0) {

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

            <div class="container @if(! $loop->first) mt-4 @endif">
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
                                    @include('plugins.user.databases.default.databases_include_value')
                                </div>
                            </div>
                        @endforeach
                        </div>
                    @endforeach
                    </div>
                @endforeach
            </div>

            {{-- 詳細 --}}
            <div class="row mt-2">
                <div class="col">
                    <div class="text-right">
                        <a href="{{url('/')}}/plugin/databases/detail/{{$page->id}}/{{$frame_id}}/{{$input->id}}">
                            <span class="btn btn-success btn-sm">詳細 <i class="fas fa-angle-right"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- ページング処理 --}}
        <div class="text-center mt-2">
            {{ $inputs->links() }}
        </div>
    @endif

@else
    {{-- フレームに紐づくコンテンツがない場合等、表示に支障がある場合は、データ登録を促す等のメッセージを表示 --}}
    <div class="card border-danger">
        <div class="card-body">
            @foreach ($setting_error_messages as $setting_error_message)
                <p class="text-center cc_margin_bottom_0">{{ $setting_error_message }}</p>
            @endforeach
        </div>
    </div>
@endif
@endsection
