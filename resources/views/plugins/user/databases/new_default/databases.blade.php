{{--
 * 登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if (empty($setting_error_messages))

    {{-- 新規登録 --}}
    @can("role_article")
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

        {{-- 絞り込み --}}
        @php
            $select_columns = $columns->where('select_flag', 1);
            $select_column_count = $select_columns->count();

            // 並べ替えが有効なら、選択項目のカウントに +1 して、並べ替え用セレクトボックスの位置を確保する。
            if ($databases_frames && $databases_frames->isUseSortFlag()) {
                $sort_count = $columns->whereIn('sort_flag', [1, 2, 3])->count();
                if ($sort_count > 0) {
                    $select_column_count++;
                }
            }
            else {
                $sort_count = 0;
            }

            $col_no = ($select_column_count == 0) ? 0 : intdiv(12, $select_column_count);
        @endphp
        @if($select_columns || $databases_frames->isBasicUseSortFlag())
            <div class="form-group row mb-3">
            @foreach($select_columns as $select_column)
                @php
                    $session_column_name = "search_column." . $frame->id . '.' . $loop->index . ".value";
                @endphp
                <div class="col-sm-{{$col_no}}">
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
                            @if($columns_select->value == Session::get($session_column_name))
                            <option value="{{$columns_select->value}}" selected>{{$columns_select->value}}</option>
                            @else
                            <option value="{{$columns_select->value}}">{{$columns_select->value}}</option>
                            @endif
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

                <div class="col-sm-{{$col_no}}">
                    <select class="form-control" name="sort_column" onChange="javascript:submit(this.form);">

                        {{-- 基本部分 --}}
                        <option value="">並べ替え</option>
                        <optgroup label="基本設定">
                            @foreach($databases_frames->getBasicUseSortFlag() as $sort_basic)
                                @if($sort_basic == ($sort_column_id . '_' . $sort_column_order))
                                    <option value="{{$sort_basic}}" selected>{{DatabaseColumnType::getDescription($sort_basic)}}</option>
                                @else
                                    <option value="{{$sort_basic}}">{{DatabaseColumnType::getDescription($sort_basic)}}</option>
                                @endif
                            @endforeach
                        </optgroup>

                        {{-- 各カラム --}}
                        @if($sort_count > 0 && $databases_frames->isUseSortFlag('column'))
                        <optgroup label="各カラム設定">
                            {{-- 1:昇順＆降順、2:昇順のみ、3:降順のみ --}}
                            @foreach($columns->whereIn('sort_flag', [1, 2, 3]) as $sort_column)

                                @if($sort_column->sort_flag == 1 || $sort_column->sort_flag == 2)
                                    @if($sort_column->id == $sort_column_id && $sort_column_order == 'asc')
                                    <option value="{{$sort_column->id}}_asc" selected>{{$sort_column->column_name}}(昇順)</option>
                                    @else
                                    <option value="{{$sort_column->id}}_asc">{{$sort_column->column_name}}(昇順)</option>
                                    @endif
                                @endif

                                @if($sort_column->sort_flag == 1 || $sort_column->sort_flag == 3)
                                    @if($sort_column->id == $sort_column_id && $sort_column_order == 'desc')
                                    <option value="{{$sort_column->id}}_desc" selected>{{$sort_column->column_name}}(降順)</option>
                                    @else
                                    <option value="{{$sort_column->id}}_desc">{{$sort_column->column_name}}(降順)</option>
                                    @endif
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

    @if ($default_hide_list)
    @else
    {{-- データのループ --}}
    <table class="table table-bordered">
        <thead class="thead-light">
        <tr>
        @foreach($columns as $column)
            @if($column->list_hide_flag == 0)
            <th>{{$column->column_name}}</th>
            @endif
        @endforeach
        </tr>
        </thead>

        <tbody>
        @foreach($inputs as $input)
        <tr>
            @foreach($columns as $column)
                @if($column->list_hide_flag == 0)
                    @if($loop->first)
                    <td>
                        <a href="{{url('/')}}/plugin/databases/detail/{{$page->id}}/{{$frame_id}}/{{$input->id}}">
                            @include('plugins.user.databases.default.databases_include_value')
                        </a>
                    </td>
                    @else
                    <td>
                        @include('plugins.user.databases.default.databases_include_value')
                    </td>
                    @endif
                @endif
            @endforeach
        </tr>
        @endforeach
        </tbody>
    </table>

    {{-- ページング処理 --}}
    <div class="text-center">
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
