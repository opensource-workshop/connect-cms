{{--
 * データベース ヘッダー テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}

{{-- 新規登録 --}}
@can('posts.create', [[null, $frame->plugin_name, $buckets]])
    <div class="row">
        <p class="text-right col-12">
            {{-- 新規登録ボタン --}}
            <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}'">
                <i class="far fa-edit"></i> 新規登録
            </button>
        </p>
    </div>
@endcan

<form action="{{url('/')}}/plugin/databases/search/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- 検索 --}}
    @if($database_frame && $database_frame->use_search_flag == 1)
    <div class="input-group mb-3">
        <input type="text" name="search_keyword" class="form-control" value="{{Session::get('search_keyword.'.$frame_id)}}" placeholder="検索はキーワードを入力してください。">
        <div class="input-group-append">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
    @endif

    @if($select_columns || $databases_frames->isBasicUseSortFlag())
        <div class="form-group row mb-3">

        {{-- 絞り込み --}}
        @foreach($select_columns as $select_column)
            @php
                $session_column_name = "search_column." . $frame->id . '.' . $loop->index . ".value";
            @endphp
            <div class="col-sm">
                <input name="search_column[{{$loop->index}}][name]" type="hidden" value="{{$select_column->column_name}}">
                <input name="search_column[{{$loop->index}}][columns_id]" type="hidden" value="{{$select_column->id}}">
                @if($select_column->column_type == 'checkbox')
                <input name="search_column[{{$loop->index}}][where]" type="hidden" value="PART">
                @else
                <input name="search_column[{{$loop->index}}][where]" type="hidden" value="ALL">
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

            <div class="col-sm">
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
                        @foreach($sort_columns as $sort_column)

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
        </div>
    @endif
</form>
