{{--
 * データベース ヘッダー テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author よたか <info@hanamachi.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @copyright Hanamachi All Rights Reserved
 * @category データベース・プラグイン
--}}

<form action="{{url('/')}}/plugin/databases/search/{{$page->id}}/{{$frame_id}}" method="POST">
    <div class="db-list-header form-group row no-gutters mb-3">
        {{ csrf_field() }}

        {{-- 絞り込みカウント --}}
        @php
            // change: コントローラ側で取得するよう見直し
            // $select_columns = $columns->where('select_flag', 1);

            $select_column_count = $select_columns->count();

            // 並べ替えが有効なら、選択項目のカウントに +1 して、並べ替え用セレクトボックスの位置を確保する。
            if ($databases_frames && $databases_frames->isUseSortFlag()) {
                $sort_count = $columns->whereIn('sort_flag', [1, 2, 3])->count();
                if ($sort_count > 0) {
                    $select_column_count++;
                }
            } else {
                $sort_count = 0;
            }
            $slect_full_width = ($select_column_count < 3) ? 6 : 12;
            $col_no = ($select_column_count == 0) ? 0 : intdiv($slect_full_width, $select_column_count);

            $add_button_width = 0; // 新規ボタンの col width
        @endphp

        {{-- 新規登録 --}}
        @can('posts.create', [[null, $frame->plugin_name, $buckets]])
            @php
                $add_button_width = 1; // 新規ボタンの幅を確保する。
            @endphp
            <div class="py-1
                col-{{$add_button_width}}
                order-2
                order-sm-10
                text-right"
            >
                <button type="button" class="btn btn-success db-btn" onclick="location.href='{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}'">
                    <i class="fas fa-plus"></i>
                    <span class="d-none d-md-inline">新規</span>
                </button>
            </div>
        @endcan

        {{-- 検索 --}}
        @if($database_frame && $database_frame->use_search_flag == 1)
            <div class="p-1
                input-group 
                col-{{12 - $add_button_width}} 
                col-sm-{{$slect_full_width - $add_button_width}} 
                order-1
                order-sm-6"
            >
                <input type="text" name="search_keyword" class="form-control" value="{{Session::get('search_keyword.'.$frame_id)}}" placeholder="キーワード入力">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        @endif

        @if($select_columns || $databases_frames->isBasicUseSortFlag())
            {{-- 絞り込み --}}
            @foreach($select_columns as $select_column)
                @php
                    $session_column_name = "search_column." . $frame->id . '.' . $loop->index . ".value";
                @endphp
                <div class="p-1
                    col-{{$col_no * 2}}
                    col-sm-{{$col_no}}
                    order-3"
                >
                    <input name="search_column[{{$loop->index}}][name]" type="hidden" value="{{$select_column->column_name}}" />
                    <input name="search_column[{{$loop->index}}][columns_id]" type="hidden" value="{{$select_column->id}}" />
                    @if($select_column->column_type == 'checkbox')
                        <input name="search_column[{{$loop->index}}][where]" type="hidden" value="PART" />
                    @else
                        <input name="search_column[{{$loop->index}}][where]" type="hidden" value="ALL" />
                    @endif
                    <select class="form-control" name="search_column[{{$loop->index}}][value]" onChange="javascript:submit(this.form);">
                        <option value="">{{$select_column->column_name}}</option>
                        @foreach($columns_selects->where('databases_columns_id', $select_column->id) as $columns_select)
                            <option value="{{$columns_select->value}}" @if($columns_select->value == Session::get($session_column_name)) selected @endif>{{  $columns_select->value  }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach

            {{-- 並び順 --}}
            @if($sort_count > 0 || $databases_frames->isBasicUseSortFlag())
                @php
                    $sort_column_id = '';
                    $sort_column_order = '';

                    // 並べ替え項目をセッション優先、次に初期値で変数に整理（選択肢のselected のため）
                    if (Session::get('sort_column_id.'.$frame_id) && Session::get('sort_column_order.'.$frame_id)) {
                        $sort_column_id = Session::get('sort_column_id.'.$frame_id);
                        $sort_column_order = Session::get('sort_column_order.'.$frame_id);
                    }
                    else if ($databases_frames && $databases_frames->default_sort_flag) {
                        $default_sort_flag_part = explode('_', $databases_frames->default_sort_flag);
                        if (count($default_sort_flag_part) == 2) {
                            $sort_column_id = $default_sort_flag_part[0];
                            $sort_column_order = $default_sort_flag_part[1];
                        }
                    }
                @endphp
                <div class="p-1
                    col-{{$col_no * 2}}
                    col-sm-{{$col_no}}
                    order-4"
                >
                    <select class="form-control" name="sort_column" onChange="javascript:submit(this.form);">
                        {{-- 基本部分 --}}
                        <option value="">並べ替え</option>
                        <optgroup label="基本設定">
                            @foreach($databases_frames->getBasicUseSortFlag() as $sort_basic)
                                <option value="{{$sort_basic}}" @if($sort_basic == ($sort_column_id . '_' . $sort_column_order)) selected @endif>{{  DatabaseSortFlag::getDescription($sort_basic)  }}</option>
                            @endforeach
                        </optgroup>

                        {{-- 各カラム --}}
                        @if($sort_count > 0 && $databases_frames->isUseSortFlag('column'))
                            <optgroup label="各カラム設定">
                                {{-- 1:昇順＆降順、2:昇順のみ、3:降順のみ --}}
                                @foreach($columns->whereIn('sort_flag', [1, 2, 3]) as $sort_column)

                                    @if($sort_column->sort_flag == 1 || $sort_column->sort_flag == 2)
                                        <option value="{{$sort_column->id}}_asc" @if($sort_column->id == $sort_column_id && $sort_column_order == 'asc') selected @endif>{{  $sort_column->column_name  }}(昇順)</option>
                                    @endif

                                    @if($sort_column->sort_flag == 1 || $sort_column->sort_flag == 3)
                                        <option value="{{$sort_column->id}}_desc" @if($sort_column->id == $sort_column_id && $sort_column_order == 'desc') selected @endif>{{  $sort_column->column_name  }}(降順)</option>
                                    @endif

                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
            @endif
        @endif
    </div>
</form>
