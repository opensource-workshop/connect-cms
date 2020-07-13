{{--
 * データベース項目の詳細設定画面
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
	{{-- プラグイン側のフレームメニュー --}}
	@include('plugins.user.databases.databases_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
    <script type="text/javascript">
        /**
        * 選択肢の追加ボタン押下
        */
        function submit_add_select(btn) {
            database_column_detail.action = "{{url('/')}}/plugin/databases/addSelect/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
            btn.disabled = true;
            database_column_detail.submit();
        }

        /**
        * 選択肢の表示順操作ボタン押下
        */
        function submit_display_sequence(select_id, display_sequence, display_sequence_operation) {
            database_column_detail.action = "{{url('/')}}/plugin/databases/updateSelectSequence/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
            database_column_detail.select_id.value = select_id;
            database_column_detail.display_sequence.value = display_sequence;
            database_column_detail.display_sequence_operation.value = display_sequence_operation;
            database_column_detail.submit();
        }

        /**
        * 選択肢の更新ボタン押下
        */
        function submit_update_select(select_id) {
            database_column_detail.action = "{{url('/')}}/plugin/databases/updateSelect/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
            database_column_detail.select_id.value = select_id;
            database_column_detail.submit();
        }

        /**
        * 選択肢の削除ボタン押下
        */
        function submit_delete_select(select_id) {
            if(confirm('選択肢を削除します。\nよろしいですか？')){
                database_column_detail.action = "{{url('/')}}/plugin/databases/deleteSelect/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
                database_column_detail.select_id.value = select_id;
                database_column_detail.submit();
            }
            return false;
        }

        /**
        * 選択肢に都道府県追加ボタン押下
        */
        function submit_add_pref(btn) {
            database_column_detail.action = "{{url('/')}}/plugin/databases/addPref/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
            btn.disabled = true;
            database_column_detail.submit();
        }

        /**
        * その他の設定の更新ボタン押下
        */
        function submit_update_column_detail() {
            database_column_detail.action = "{{url('/')}}/plugin/databases/updateColumnDetail/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
            database_column_detail.submit();
        }
    </script>

    <form action="" id="database_column_detail" name="database_column_detail" method="POST" class="form-horizontal">

        {{ csrf_field() }}
        <input type="hidden" name="databases_id" value="{{ $databases_id }}">
        <input type="hidden" name="column_id" value="{{ $column->id }}">
        <input type="hidden" name="select_id" value="">
        <input type="hidden" name="display_sequence" value="">
        <input type="hidden" name="display_sequence_operation" value="">

        {{-- メッセージエリア --}}
        <div class="alert alert-info mt-2">
            <i class="fas fa-exclamation-circle"></i>
            {{ $message ? $message : '項目【' . $column->column_name . ' 】の詳細設定を行います。' }}
        </div>

        @if ($column->column_type == DatabaseColumnType::radio || $column->column_type == DatabaseColumnType::checkbox || $column->column_type == DatabaseColumnType::select)
            {{-- 選択肢の設定 --}}
            <div class="card mb-4">
                <h5 class="card-header">選択肢の設定</h5>
                <div class="card-body">

                    {{-- エラーメッセージエリア --}}
                    @if ($errors && $errors->has('select_name'))
                        <div class="alert alert-danger mt-2">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('select_name') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                                {{-- 選択項目の一覧 --}}
                                <tr>
                                    @if (count($selects) > 0)
                                        <th class="text-center" nowrap>表示順</th>
                                        <th class="text-center" nowrap>選択肢名</th>
                                        <th class="text-center" nowrap>更新</th>
                                        <th class="text-center" nowrap>削除</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                {{-- 更新用の行 --}}
                                @foreach($selects as $select)
                                    <tr  @if (isset($select->hide_flag)) class="table-secondary" @endif>
                                        <td class="text-center" nowrap>
                                            {{-- 表示順操作 --}}
                                            {{-- 上移動 --}}
                                            <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->first) disabled @endif onclick="javascript:submit_display_sequence({{ $select->id }}, {{ $select->display_sequence }}, 'up')">
                                                <i class="fas fa-arrow-up"></i>
                                            </button>

                                            {{-- 下移動 --}}
                                            <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->last) disabled @endif onclick="javascript:submit_display_sequence({{ $select->id }}, {{ $select->display_sequence }}, 'down')">
                                                <i class="fas fa-arrow-down"></i>
                                            </button>
                                        </td>

                                        <td>
                                            {{-- 選択肢名 --}}
                                            <input class="form-control" type="text" name="select_name_{{ $select->id }}" value="{{ old('select_name_'.$select->id, $select->value)}}">
                                        </td>

                                        <td class="align-middle text-center">
                                            {{-- 更新ボタン --}}
                                            <button
                                                class="btn btn-primary cc-font-90 text-nowrap"
                                                onclick="javascript:submit_update_select({{ $select->id }});"
                                                >
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </td>

                                        <td class="text-center">
                                            {{-- 削除ボタン --}}
                                            <button
                                                class="btn btn-danger cc-font-90 text-nowrap"
                                                onclick="javascript:return submit_delete_select({{ $select->id }});"
                                                >
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach

                                <tr class="thead-light">
                                    <th colspan="7">【選択肢の追加行】</th>
                                </tr>

                                {{-- 新規登録用の行 --}}
                                <tr>
                                    {{-- 余白 --}}
                                    <td>
                                    </td>

                                    {{-- 選択肢名 --}}
                                    <td>
                                        <input class="form-control" type="text" name="select_name" value="{{ old('select_name') }}" placeholder="選択肢名">
                                    </td>

                                    {{-- ＋ボタン --}}
                                    <td class="text-center">
                                        <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_add_select(this);">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </td>

                                    {{-- 余白 --}}
                                    <td>
                                    </td>
                                </tr>

                                <tr>
                                    {{-- ＋ボタン --}}
                                    <td colspan="4" class="text-center">
                                        <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_add_pref(this);">
                                            <i class="fas fa-plus"></i> 都道府県を追加
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if ($column->column_type == DatabaseColumnType::time)
            {{-- 項目毎の固有設定 --}}
            <div class="card mb-4">
                <h5 class="card-header">項目毎の固有設定</h5>
                <div class="card-body">
                    {{-- 分刻み指定 ※データ型が「時間型」のみ表示 --}}
                    @if ($column->column_type == DatabaseColumnType::time)
                        <div class="form-group row">
                            <label class="{{$frame->getSettingLabelClass()}}">分刻み指定 </label>
                            <div class="{{$frame->getSettingInputClass()}}">
                                <select class="form-control" name="minutes_increments">
                                    @foreach (MinutesIncrements::getMembers() as $key=>$value)
                                        <option value="{{$key}}"
                                            {{-- 初期表示用 --}}
                                            @if($key == $column->minutes_increments) selected="selected" @endif
                                            {{-- validation用 --}}
                                            @if($key == old('minutes_increments')) selected="selected" @endif
                                            >{{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    {{-- ボタンエリア --}}
                    <div class="form-group text-center">
                        <button onclick="javascript:submit_update_column_detail();" class="btn btn-primary database-horizontal">
                            <i class="fas fa-check"></i> 更新
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if ($column->column_type == DatabaseColumnType::text || $column->column_type == DatabaseColumnType::textarea || $column->column_type == DatabaseColumnType::date)
            {{-- チェック処理の設定 --}}
            <div class="card mb-4">
                <h5 class="card-header">チェック処理の設定</h5>
                <div class="card-body">
                    {{-- 1行文字列型／複数行文字列型のチェック群 --}}
                    @if ($column->column_type == DatabaseColumnType::text || $column->column_type == DatabaseColumnType::textarea)
                        {{-- 数値のみ許容 --}}
                        <div class="form-group row">
                            <label class="{{$frame->getSettingLabelClass()}}">入力制御</label>
                            <div class="{{$frame->getSettingInputClass(true)}}">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="rule_allowed_numeric" id="rule_allowed_numeric" value="1" class="custom-control-input"
                                        @if(old('rule_allowed_numeric', $column->rule_allowed_numeric)) checked @endif >
                                    <label class="custom-control-label" for="rule_allowed_numeric">半角数値のみ許容</label><br>
                                    <small class="text-muted">※ 全角数値を入力した場合は半角数値に補正します。</small>
                                </div>
                            </div>
                        </div>

                        {{-- 英数値のみ許容 --}}
                        <div class="form-group row">
                            <label class="{{$frame->getSettingLabelClass()}}"></label>
                            <div class="{{$frame->getSettingInputClass(true)}}">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="rule_allowed_alpha_numeric" id="rule_allowed_alpha_numeric" value="1" class="custom-control-input" 
                                        @if(old('rule_allowed_alpha_numeric', $column->rule_allowed_alpha_numeric)) checked @endif>
                                    <label class="custom-control-label" for="rule_allowed_alpha_numeric">半角英数値のみ許容</label>
                                </div>
                            </div>
                        </div>

                        {{-- 指定桁数（数値）以下を許容 --}}
                        <div class="form-group row">
                            <label class="{{$frame->getSettingLabelClass()}}">入力桁数</label>
                            <div class="{{$frame->getSettingInputClass()}}">
                                <input type="text" name="rule_digits_or_less" value="{{old('rule_digits_or_less', $column->rule_digits_or_less)}}" class="form-control">
                                <small class="text-muted">※ 入力桁数の指定時は「半角数値のみ許容」も適用されます。</small><br>
                                @if ($errors && $errors->has('rule_digits_or_less'))
                                    <div class="text-danger">{{$errors->first('rule_digits_or_less')}}</div>
                                @endif
                            </div>
                        </div>

                        {{-- 指定文字数以下を許容 --}}
                        <div class="form-group row">
                            <label class="{{$frame->getSettingLabelClass()}}">入力最大文字数</label>
                            <div class="{{$frame->getSettingInputClass()}}">
                                <input type="text" name="rule_word_count" value="{{old('rule_word_count', $column->rule_word_count)}}" class="form-control">
                                <small class="text-muted">※ 全角は2文字、半角は1文字として換算します。</small><br>
                                @if ($errors && $errors->has('rule_word_count'))
                                    <div class="text-danger">{{$errors->first('rule_word_count')}}</div>
                                @endif
                            </div>
                        </div>

                        {{-- 最大値設定 --}}
                        <div class="form-group row">
                            <label class="{{$frame->getSettingLabelClass()}}">最大値</label>
                            <div class="{{$frame->getSettingInputClass()}}">
                                <input type="text" name="rule_max" value="{{old('rule_max', $column->rule_max)}}" class="form-control">
                                @if ($errors && $errors->has('rule_max'))
                                    <div class="text-danger">{{$errors->first('rule_max')}}</div>
                                @endif
                            </div>
                        </div>

                        {{-- 最小値設定 --}}
                        <div class="form-group row">
                            <label class="{{$frame->getSettingLabelClass()}}">最小値</label>
                            <div class="{{$frame->getSettingInputClass()}}">
                                <input type="text" name="rule_min" value="{{old('rule_min', $column->rule_min)}}" class="form-control">
                                @if ($errors && $errors->has('rule_min'))
                                    <div class="text-danger">{{$errors->first('rule_min')}}</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- 日付型のチェック群 --}}
                    @if ($column->column_type == DatabaseColumnType::date)
                        {{-- 指定日以降（指定日含む）の入力を許容 --}}
                        <div class="form-group row">
                            <label class="{{$frame->getSettingLabelClass()}}">指定日数以降を許容</label>
                            <div class="{{$frame->getSettingInputClass()}}">
                                <input type="text" name="rule_date_after_equal" value="{{old('rule_date_after_equal', $column->rule_date_after_equal)}}" class="form-control">
                                <small class="text-muted">※ 整数（･･･,-1,0,1,･･･）で入力します。</small><br>
                                <small class="text-muted">※ 当日を含みます。</small><br>
                                <small class="text-muted">&nbsp;&nbsp;&nbsp;&nbsp;(例)&nbsp;設定値「2」、データベース入力日「2020/2/16」の場合、「2020/2/18」以降の日付を入力できます。</small>
                                @if ($errors && $errors->has('rule_date_after_equal'))
                                    <div class="text-danger">{{$errors->first('rule_date_after_equal')}}</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- ボタンエリア --}}
                    <div class="form-group text-center">
                        <button onclick="javascript:submit_update_column_detail();" class="btn btn-primary database-horizontal">
                            <i class="fas fa-check"></i> 更新
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- キャプション設定 --}}
        <div class="card mb-4">
            <h5 class="card-header">キャプションの設定</h5>
            <div class="card-body">
                {{-- キャプション内容 --}}
                <div class="form-group row">
                    <label class="{{$frame->getSettingLabelClass()}}">内容 </label>
                    <div class="{{$frame->getSettingInputClass()}}">
                        <textarea name="caption" class="form-control" rows="3">
                            {{old('caption', $column->caption)}}
                        </textarea>
                    </div>
                </div>

                {{-- キャプション文字色 --}}
                <div class="form-group row">
                    <label class="{{$frame->getSettingLabelClass()}}">文字色 </label>
                    <div class="{{$frame->getSettingInputClass()}}">
                        <select class="form-control" name="caption_color">
                            @foreach (Bs4TextColor::getMembers() as $key=>$value)
                                <option value="{{$key}}" class="{{ $key }}" @if($key == old('caption_color', $column->caption_color)) selected @endif>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ボタンエリア --}}
                <div class="form-group text-center">
                    <button onclick="javascript:submit_update_column_detail();" class="btn btn-primary database-horizontal">
                        <i class="fas fa-check"></i> 更新
                    </button>
                </div>
            </div>
        </div>

        {{-- DBカラム設定 --}}
        <div class="card mb-4">
            <h5 class="card-header">DBカラム設定</h5>
            <div class="card-body">

                {{-- 一覧から非表示にする指定 --}}
                <div class="form-group row">
                    <label class="{{$frame->getSettingLabelClass(true)}}">一覧への表示指定</label>
                    <div class="{{$frame->getSettingInputClass(true)}}">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="0" id="list_hide_flag_0" name="list_hide_flag" class="custom-control-input" @if(old('list_hide_flag', $column->list_hide_flag) == 0) checked="checked" @endif>
                            <label class="custom-control-label" for="list_hide_flag_0">一覧に表示する</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="1" id="list_hide_flag_1" name="list_hide_flag" class="custom-control-input" @if(old('list_hide_flag', $column->list_hide_flag) == 1) checked="checked" @endif>
                            <label class="custom-control-label" for="list_hide_flag_1">一覧に表示しない</label>
                        </div>
                    </div>
                </div>

                {{-- 詳細から非表示にする指定 --}}
                <div class="form-group row">
                    <label class="{{$frame->getSettingLabelClass(true)}}">詳細への表示指定</label>
                    <div class="{{$frame->getSettingInputClass(true)}}">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="0" id="detail_hide_flag_0" name="detail_hide_flag" class="custom-control-input" @if(old('detail_hide_flag', $column->detail_hide_flag) == 0) checked="checked" @endif>
                            <label class="custom-control-label" for="detail_hide_flag_0">詳細に表示する</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="1" id="detail_hide_flag_1" name="detail_hide_flag" class="custom-control-input" @if(old('detail_hide_flag', $column->detail_hide_flag) == 1) checked="checked" @endif>
                            <label class="custom-control-label" for="detail_hide_flag_1">詳細に表示しない</label>
                        </div>
                    </div>
                </div>

                {{-- 権限毎に一覧・詳細で非表示にする指定 --}}
                <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass(true)}} pt-0">一覧・詳細に表示しない権限指定</label>
                    <div class="{{$frame->getSettingInputClass(true)}}">
                        <div class="col pl-0">
                            @foreach (DatabaseColumnRoleName::getMembers() as $role_name => $display_value)
                                @php
                                $hide_flag = 0;
                                if (isset($columns_roles[$role_name])) {
                                    $hide_flag = $columns_roles[$role_name]->list_detail_hide_flag;
                                }
                                @endphp
                                {{--
                                <div class="custom-control custom-checkbox">
                                    <input name="role_article[list_detail_hide_flag]" value="1" type="checkbox" class="custom-control-input" id="list_detail_hide_role_article">
                                    <label class="custom-control-label" for="list_detail_hide_role_article">モデレータ</label>
                                </div>
                                --}}
                                <div class="custom-control custom-checkbox">
                                    <input name="{{$role_name}}[list_detail_hide_flag]" value="1" type="checkbox" class="custom-control-input" id="{{$role_name}}_list_detail_hide_flag" @if(old($role_name . '.list_detail_hide_flag', $hide_flag) == 1) checked="checked" @endif>
                                    <label class="custom-control-label" for="{{$role_name}}_list_detail_hide_flag">{{  $display_value  }}</label>
                                </div>
                            @endforeach
                            <small class="text-muted">※ 「権限なし」とは、コンテンツ管理者・プラグイン管理者・モデレータ・承認者・編集者のいずれの権限もない状態です。</small><br>
                            <small class="text-muted">※ 該当権限のユーザは、「並べ替え指定」「検索対象指定」「絞り込み対象指定」からも当カラムを取り除き、見れなくします。</small>
                        </div>
                    </div>
                </div>

                {{-- 権限毎に登録・編集で非表示にする指定 --}}
                <div class="form-group row">
                    <label class="{{$frame->getSettingLabelClass(true)}} pt-0">登録・編集に表示しない権限指定</label>
                    <div class="{{$frame->getSettingInputClass(true)}}">
                        <div class="col pl-0">
                            @foreach (DatabaseColumnRoleName::getRegistEditHideMembers() as $role_name => $display_value)
                                @php
                                $hide_flag = 0;
                                if (isset($columns_roles[$role_name])) {
                                    $hide_flag = $columns_roles[$role_name]->regist_edit_hide_flag;
                                }
                                @endphp
                                {{--
                                <div class="custom-control custom-checkbox">
                                    <input name="role_article[regist_edit_hide_flag]" value="1" type="checkbox" class="custom-control-input" id="regist_edit_hide_role_guest">
                                    <label class="custom-control-label" for="regist_edit_hide_role_guest">モデレータ</label>
                                </div>
                                --}}
                                <div class="custom-control custom-checkbox">
                                    <input name="{{$role_name}}[regist_edit_hide_flag]" value="1" type="checkbox" class="custom-control-input" id="{{$role_name}}_regist_edit_hide_flag" @if(old($role_name . '.regist_edit_hide_flag', $hide_flag) == 1) checked="checked" @endif>
                                    <label class="custom-control-label" for="{{$role_name}}_regist_edit_hide_flag">{{  $display_value  }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 並べ替え指定 --}}
                <div class="form-group row">
                    <label class="{{$frame->getSettingLabelClass(true)}}">並べ替え指定</label>
                    <div class="{{$frame->getSettingInputClass(true)}}">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="0" id="sort_flag_0" name="sort_flag" class="custom-control-input" @if(old('sort_flag', $column->sort_flag) == 0) checked="checked" @endif>
                            <label class="custom-control-label" for="sort_flag_0">使用しない</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="1" id="sort_flag_1" name="sort_flag" class="custom-control-input" @if(old('sort_flag', $column->sort_flag) == 1) checked="checked" @endif>
                            <label class="custom-control-label" for="sort_flag_1">昇順＆降順</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="2" id="sort_flag_2" name="sort_flag" class="custom-control-input" @if(old('sort_flag', $column->sort_flag) == 2) checked="checked" @endif>
                            <label class="custom-control-label" for="sort_flag_2">昇順のみ</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="3" id="sort_flag_3" name="sort_flag" class="custom-control-input" @if(old('sort_flag', $column->sort_flag) == 3) checked="checked" @endif>
                            <label class="custom-control-label" for="sort_flag_3">降順のみ</label>
                        </div>
                    </div>
                </div>

                {{-- 検索対象指定 --}}
                <div class="form-group row">
                    <label class="{{$frame->getSettingLabelClass(true)}}">検索対象指定</label>
                    <div class="{{$frame->getSettingInputClass(true)}}">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="0" id="search_flag_0" name="search_flag" class="custom-control-input" @if(old('search_flag', $column->search_flag) == 0) checked="checked" @endif>
                            <label class="custom-control-label" for="search_flag_0">検索対象にしない</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="1" id="search_flag_1" name="search_flag" class="custom-control-input" @if(old('search_flag', $column->search_flag) == 1) checked="checked" @endif>
                            <label class="custom-control-label" for="search_flag_1">検索対象とする</label>
                        </div>
                    </div>
                </div>

                {{-- 絞り込み対象指定 --}}
                @if ($column->column_type == DatabaseColumnType::radio || $column->column_type == DatabaseColumnType::checkbox || $column->column_type == DatabaseColumnType::select)
                    <div class="form-group row">
                        <label class="{{$frame->getSettingLabelClass(true)}}">絞り込み対象指定</label>
                        <div class="{{$frame->getSettingInputClass(true)}}">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" value="0" id="select_flag_0" name="select_flag" class="custom-control-input" @if(old('select_flag', $column->select_flag) == 0) checked="checked" @endif>
                                <label class="custom-control-label" for="select_flag_0">絞り込み対象にしない</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" value="1" id="select_flag_1" name="select_flag" class="custom-control-input" @if(old('select_flag', $column->select_flag) == 1) checked="checked" @endif>
                                <label class="custom-control-label" for="select_flag_1">絞り込み対象とする</label>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- グループ --}}
                <div class="form-group row">
                    <label class="{{$frame->getSettingLabelClass(true)}} pt-0">グループ</label>
                    <div class="{{$frame->getSettingInputClass(true)}}">
                        <div class="col pl-0">
                            <label>行グループ</label>
                            <input type="text" name="row_group" value="{{old('row_group', $column->row_group)}}" class="form-control" />
                            <small class="text-muted">※ 数値で入力します。</small>
                            @if ($errors && $errors->has('row_group'))
                                <div class="text-danger">{{$errors->first('row_group')}}</div>
                            @endif
                        </div>
                        <div class="col pl-0">
                            <label>列グループ</label>
                            <input type="text" name="column_group" value="{{old('column_group', $column->column_group)}}" class="form-control" />
                            <small class="text-muted">※ 数値で入力します。</small>
                            @if ($errors && $errors->has('column_group'))
                                <div class="text-danger">{{$errors->first('column_group')}}</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ボタンエリア --}}
                <div class="form-group text-center">
                    <button onclick="javascript:submit_update_column_detail();" class="btn btn-primary database-horizontal">
                        <i class="fas fa-check"></i> 更新
                    </button>
                </div>
            </div>
        </div>

        {{-- デザイン設定 --}}
        <div class="card mb-4">
            <h5 class="card-header">デザインの設定</h5>
            <div class="card-body">

                {{-- クラス名 --}}
                <div class="form-group row">
                    <label class="{{$frame->getSettingLabelClass()}}">クラス名</label>
                    <div class="{{$frame->getSettingInputClass()}}">
                        <input type="text" name="classname" value="{{old('classname', $column->classname)}}" class="form-control" />
                    </div>
                </div>

                {{-- ボタンエリア --}}
                <div class="form-group text-center">
                    <button onclick="javascript:submit_update_column_detail();" class="btn btn-primary database-horizontal">
                    <i class="fas fa-check"></i> 更新
                    </button>
                </div>
            </div>
        </div>

        {{-- ボタンエリア --}}
        <div class="form-group text-center">
            {{-- キャンセルボタン --}}
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/databases/editColumn/{{$page->id}}/{{$frame_id}}/#frame-{{$frame->id}}'">
                <i class="fas fa-times"></i> キャンセル
            </button>
        </div>
    </form>
@endsection
