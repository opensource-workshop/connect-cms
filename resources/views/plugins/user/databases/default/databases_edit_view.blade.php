{{--
 * 表示設定画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベースプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.databases.databases_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

@include('plugins.common.errors_form_line')

@if ($errors->any())
    {{--
    <div class="alert alert-danger mt-2">
        @foreach ($errors->all() as $error)
            <i class="fas fa-exclamation-circle"></i>
            {{$error}}
        @endforeach
    </div>
    --}}
@else
    @if (!$database->id)
        <div class="alert alert-warning mt-2">
            <i class="fas fa-exclamation-circle"></i>
            データベース選択画面から選択するか、データベース新規作成で作成してください。
        </div>
    @else
        <div class="alert alert-info mt-2">
            <i class="fas fa-exclamation-circle"></i>
            表示設定を変更します。
        </div>
    @endif
@endif

@if (!$database->id)
@else
<div id="app_{{ $frame->id }}">
<form action="{{url('/')}}/plugin/databases/saveView/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- 表示件数 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">表示件数 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="view_count" value="{{old('view_count', $view_frame->view_count)}}" class="form-control">
            @if ($errors && $errors->has('view_count')) <div class="text-danger">{{$errors->first('view_count')}}</div> @endif
        </div>
    </div>

    {{-- 検索機能の表示 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">検索機能の表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="0" id="use_search_flag_0" name="use_search_flag" class="custom-control-input" @if(old('use_search_flag', $view_frame->use_search_flag) == 0) checked="checked" @endif>
                <label class="custom-control-label" for="use_search_flag_0" id="label_use_search_flag_0">表示しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="1" id="use_search_flag_1" name="use_search_flag" class="custom-control-input" @if(old('use_search_flag', $view_frame->use_search_flag) == 1) checked="checked" @endif>
                <label class="custom-control-label" for="use_search_flag_1" id="label_use_search_flag_1">表示する</label>
            </div>
        </div>
    </div>

    {{-- 検索窓のプレースホルダ --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">検索項目の<br>プレースホルダ</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="placeholder_search" value="{{old('placeholder_search', $view_frame->placeholder_search)}}" class="form-control">
            @if ($errors && $errors->has('placeholder_search')) <div class="text-danger">{{$errors->first('placeholder_search')}}</div> @endif
            <small class="text-muted">
                ※ 検索テキストボックスに初期表示される補足テキストを設定できます。<br>
                ※ 未設定時は「検索はキーワードを入力してください。」を表示します。
            </small>
        </div>
    </div>

    {{-- 急上昇ワード --}}
    @php
        $database_show_trend_words = FrameConfig::getConfigValueAndOld($frame_configs, DatabaseFrameConfig::database_show_trend_words, ShowType::not_show);
    @endphp
    <script>
        function hideTrendWords() {
            $('#collapse_trend_words_{{$frame_id}}').collapse('hide');
        }

        function showTrendWords() {
            $('#collapse_trend_words_{{$frame_id}}').collapse('show');
        }
    </script>
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">{{DatabaseFrameConfig::getDescription('database_show_trend_words')}}</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            @foreach (ShowType::getMembers() as $key => $type)
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="{{$key}}" id="database_show_trend_words_{{$key}}" name="database_show_trend_words"
                     class="custom-control-input" @if ($database_show_trend_words == $key) checked="checked" @endif
                     @if ($key === ShowType::show)
                        onclick="showTrendWords();"
                     @else
                        onclick="hideTrendWords();"
                     @endif
                     >
                <label class="custom-control-label" for="database_show_trend_words_{{$key}}">{{$type}}</label>
            </div>
            @endforeach
        </div>
    </div>

    {{-- 急上昇ワードの選択 --}}
    @php
        // 急上昇ワード
        $database_trend_words = [];
        $registered_trend_words = array_filter(explode('|', FrameConfig::getConfigValue($frame_configs, DatabaseFrameConfig::database_trend_words)));
        if (old('database_trend_words')) {
            $database_trend_words = old('database_trend_words');
        } else {
            $database_trend_words = $registered_trend_words;
        }

        // 表示項目名
        $database_trend_words_caption = FrameConfig::getConfigValue($frame_configs, DatabaseFrameConfig::database_trend_words_caption);
    @endphp

    <div class="form-group row collapse @if ($database_show_trend_words == ShowType::show) show @endif" id="collapse_trend_words_{{$frame_id}}">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass(false, true)}}">
            <div class="card mb-1">
                <div class="card-body">
                    <h6 class="card-title">設定済みの値</h6>
                    <div class="mb-2">
                        @foreach ($registered_trend_words as $trend_word)<span class="badge badge-pill badge-secondary mr-2">{{$trend_word}}</span>@endforeach
                    </div>
                </div>
            </div>
            <div class="card mb-1">
                <div class="card-body">
                    <h6 class="card-title">更新する値</h6>
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-primary" v-on:click="fetchTrendWordsDaily">最新化（日間）</button>
                        <button type="button" class="btn btn-sm btn-primary" v-on:click="fetchTrendWordsWeekly">最新化（週間）</button>
                        <button type="button" class="btn btn-sm btn-primary" v-on:click="fetchTrendWordsMonthly">最新化（月間）</button>
                    </div>
                    <div class="mb-2">
                        <span class="badge badge-pill badge-info mr-2" v-for="(trend_word, index) in trend_words" v-bind:key="trend_word.word">
                            @{{trend_word.word}}<a href="javascript:void(0)" class="text-white ml-2" v-on:click="deleteTrendWord(index)"><i class="fas fa-minus-circle"></i></a>
                            <input type="hidden" name="database_trend_words[]" v-model="trend_word.word">
                        </span>
                    </div>
                </div>
            </div>
            <div class="card mb-1">
                <div class="card-body">
                    <h6 class="card-title">表示項目名</h6>
                    <input type="text" name="database_trend_words_caption" value="{{$database_trend_words_caption}}" class="form-control">
                </div>
            </div>
            <small class="form-text text-muted">
                ※ 検索されたキーワードを、急上昇ワード(再検索できるキーワード)として手動で設定できます。<br>
                ※ 設定するには事前に下記を設定します。<br>
                　※ <a href="{{ url("/plugin/databases/editBuckets/{$page->id}/{$frame_id}#frame-{$frame_id}") }}" target="_blank">DB設定</a>の「検索キーワードを記録」を「使用する」に設定する。<br>
                　※ 当表示設定の「検索機能の表示」を「表示する」に設定する。<br>
                　※ 上記を設定後にデータベースが検索されると、当表示設定の「急上昇ワードの表示＞更新する値」の「最新化」ボタン押下で、急上昇ワードを設定できます。<br>
            </small>
        </div>
    </div>

    {{-- 検索後の遷移先 --}}
    @php
        $database_destination_frame = FrameConfig::getConfigValueAndOld($frame_configs, DatabaseFrameConfig::database_destination_frame, $frame->id);
    @endphp
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">{{DatabaseFrameConfig::getDescription('database_destination_frame')}}<br><small class="text-muted">ページ - フレーム</small></label>
        <div class="{{$frame->getSettingInputClass()}}">
            @foreach ($same_database_frames as $database_frame)
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" value="{{$database_frame->id}}" id="database_destination_frame{{$loop->index}}" name="database_destination_frame"
                        class="custom-control-input" @if(old('database_destination_frame', $database_destination_frame) ==  $database_frame->id) checked="checked" @endif>
                    <label class="custom-control-label" for="database_destination_frame{{$loop->index}}">
                        @if ($database_frame->id == $frame->id)
                            <span class="badge bg-info text-dark">初期設定</span>
                        @endif
                        {{$database_frame->page_name}} - {{$database_frame->frame_title}}
                    </label>
                </div>
            @endforeach
            <div class="text-muted">
                <small>
                    ※ トップページなどに検索窓を設けて遷移先は別のページにしたいときに初期設定から変更してください。
                </small>
            </div>
        </div>
    </div>

    {{-- 絞り込み機能の表示 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">絞り込み機能の表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="0" id="use_select_flag_0" name="use_select_flag" class="custom-control-input" @if(old('use_select_flag', $view_frame->use_select_flag) == 0) checked="checked" @endif>
                <label class="custom-control-label" for="use_select_flag_0" id="label_use_select_flag_0">表示しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="1" id="use_select_flag_1" name="use_select_flag" class="custom-control-input" @if(old('use_select_flag', $view_frame->use_select_flag) == 1) checked="checked" @endif>
                <label class="custom-control-label" for="use_select_flag_1" id="label_use_select_flag_1">表示する</label>
            </div>
        </div>
    </div>

    {{-- 絞り込み機能の表示（複数選択） --}}
    @php
        $use_select_multiple_flag = FrameConfig::getConfigValueAndOld($frame_configs, DatabaseFrameConfig::database_use_select_multiple_flag, ShowType::not_show);
    @endphp
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">{{DatabaseFrameConfig::getDescription('database_use_select_multiple_flag')}}</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            @foreach (ShowType::getMembers() as $key => $type)
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="{{$key}}" id="use_select_multiple_flag_{{$key}}" name="database_use_select_multiple_flag" class="custom-control-input" @if ($use_select_multiple_flag == $key) checked="checked" @endif>
                <label class="custom-control-label" for="use_select_multiple_flag_{{$key}}" id="label_use_select_multiple_flag_{{$key}}">{{$type}}</label>
            </div>
            @endforeach
        </div>
    </div>

    {{-- 並べ替え項目の表示 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">並べ替え項目の表示</label>
        <div class="{{$frame->getSettingInputClass(false)}}">
            @foreach (DatabaseSortFlag::getDisplaySortFlags() as $sort_key => $sort_view)
                {{--
                <div class="custom-control custom-checkbox custom-control-inline">
                    <input type="checkbox" name="use_sort_flag[created_asc]" value="created_asc" class="custom-control-input" id="use_sort_flag_created_asc" @ if(old('use_sort_flag.created_asc', $ view_frame->isUseSortFlag('created_asc'))) checked=checked @ endif>
                    <label class="custom-control-label" for="use_sort_flag_created_asc">登録日（古い順）</label>
                </div>
                --}}
                <div class="custom-control custom-checkbox custom-control-inline">
                    <input type="checkbox" name="use_sort_flag[{{$sort_key}}]" value="{{$sort_key}}" class="custom-control-input" id="use_sort_flag_{{$sort_key}}" @if(old('use_sort_flag.' . $sort_key, $view_frame->isUseSortFlag($sort_key))) checked=checked @endif>
                    <label class="custom-control-label" for="use_sort_flag_{{$sort_key}}" id="label_use_sort_flag_{{$sort_key}}">{{  $sort_view  }}</label>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 初期表示での並び順 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">初期表示での並び順</label>

        <div class="{{$frame->getSettingInputClass(true)}}">
            <select class="form-control" name="default_sort_flag">

            @php
            $default_sort_flag = old('default_sort_flag', $view_frame->default_sort_flag);
            @endphp

            <optgroup label="基本設定">
                <option value="">指定なし</option>
                @foreach (DatabaseSortFlag::getSortFlags() as $sort_key => $sort_view)
                    {{-- <option value="created_asc" @ if($default_sort_flag == 'created_asc') selected @ endif>登録日（古い順）</option> --}}
                    <option value="{{$sort_key}}" @if($default_sort_flag == $sort_key) selected @endif>{{  $sort_view  }}</option>
                @endforeach
            </optgroup>
            <optgroup label="各カラム設定">
                {{-- 1:昇順＆降順、2:昇順のみ、3:降順のみ --}}
                @foreach($columns->whereIn('sort_flag', [1, 2, 3]) as $sort_column)

                    @php
                        $sort_option = $sort_column->sort_download_count ? '_downloadcount' : null;
                        $sort_option_name = $sort_column->sort_download_count ? 'ダウンロード数' : null;
                    @endphp

                    @if($sort_column->sort_flag == 1 || $sort_column->sort_flag == 2)
                        <option value="{{$sort_column->id}}_asc{{$sort_option}}" @if(($sort_column->id . '_asc' . $sort_option) == $default_sort_flag) selected @endif>{{$sort_column->column_name}}{{$sort_option_name}}(昇順)</option>
                    @endif

                    @if($sort_column->sort_flag == 1 || $sort_column->sort_flag == 3)
                        <option value="{{$sort_column->id}}_desc{{$sort_option}}" @if(($sort_column->id . '_desc' . $sort_option) == $default_sort_flag) selected @endif>{{$sort_column->column_name}}{{$sort_option_name}}(降順)</option>
                    @endif
                @endforeach
            </optgroup>
            </select>
        </div>
    </div>

    {{-- 初期表示で一覧表示しない --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">初期表示での一覧表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="1" id="default_hide_1" name="default_hide" class="custom-control-input" @if(old('default_hide', $view_frame->default_hide) == 1) checked="checked" @endif>
                <label class="custom-control-label" for="default_hide_1">表示しない</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="0" id="default_hide_0" name="default_hide" class="custom-control-input" @if(old('default_hide', $view_frame->default_hide) == 0) checked="checked" @endif>
                <label class="custom-control-label" for="default_hide_0">表示する</label>
            </div>
        </div>
    </div>

    {{-- 絞り込み制御 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}} pt-0">絞り込み表示</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="col pl-0">

                <div class="row">
                    <div class="col-md">
                        <label>絞り込み制御</label><br>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="0" id="use_filter_flag_0" name="use_filter_flag" class="custom-control-input" @if(old('use_filter_flag', $view_frame->use_filter_flag) == 0) checked="checked" @endif>
                            <label class="custom-control-label" for="use_filter_flag_0">制御しない</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="1" id="use_filter_flag_1" name="use_filter_flag" class="custom-control-input" @if(old('use_filter_flag', $view_frame->use_filter_flag) == 1) checked="checked" @endif>
                            <label class="custom-control-label" for="use_filter_flag_1">制御する</label>
                        </div>
                        <div>
                            <small class="text-muted">
                                ※ 表示データの絞り込む条件を制御できます。表側で検索しても、絞り込んだデータ以外は表示されません。
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md">
                        <label>キーワード</label>
                        <input type="text" name="filter_search_keyword" class="form-control" value="{{old('filter_search_keyword', $view_frame->filter_search_keyword)}}">
                    </div>
                </div>

                {{-- 絞り込み --}}
                @foreach($select_columns as $select_column)
                    @php
                        $filter_search_columns = json_decode($view_frame->filter_search_columns, true);
                        $filter_columns_select_value = $filter_search_columns[$loop->index]['value'] ?? null;
                        //var_dump($filter_search_columns[$loop->index]);
                        $filter_columns_select_value = old("filter_search_columns." . $loop->index . ".value", $filter_columns_select_value)
                    @endphp
                    <div class="row mt-3">
                        <div class="col-md">
                            <label>{{$select_column->column_name}}</label>

                            <input name="filter_search_columns[{{$loop->index}}][name]" type="hidden" value="{{$select_column->column_name}}">
                            <input name="filter_search_columns[{{$loop->index}}][columns_id]" type="hidden" value="{{$select_column->id}}">
                            @if($select_column->column_type == DatabaseColumnType::checkbox)
                            <input name="filter_search_columns[{{$loop->index}}][where]" type="hidden" value="PART">
                            @else
                            <input name="filter_search_columns[{{$loop->index}}][where]" type="hidden" value="ALL">
                            @endif
                            <select class="form-control" name="filter_search_columns[{{$loop->index}}][value]">
                                <option value="">{{$select_column->column_name}}</option>
                                @foreach($columns_selects->where('databases_columns_id', $select_column->id) as $columns_select)
                                    <option value="{{$columns_select->value}}" @if($columns_select->value == $filter_columns_select_value) selected @endif>{{  $columns_select->value  }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 表示件数リストの表示 --}}
    @php
        $view_count_spectator = FrameConfig::getConfigValueAndOld($frame_configs, DatabaseFrameConfig::database_view_count_spectator, ShowType::not_show);
    @endphp
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">{{DatabaseFrameConfig::getDescription('database_view_count_spectator')}}</label>
        <div class="{{$frame->getSettingInputClass()}}">
            @foreach (ShowType::getMembers() as $key => $type)
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="{{$key}}" id="view_count_spectator{{$key}}" name="database_view_count_spectator" class="custom-control-input" @if ($view_count_spectator == $key) checked="checked" @endif>
                <label class="custom-control-label" for="view_count_spectator{{$key}}" id="view_count_spectator{{$key}}">{{$type}}</label>
            </div>
            @endforeach
            <small class="form-text text-muted">
                表示する場合、閲覧者が表示件数を変更できます。
            </small>
        </div>
    </div>

    {{-- 表示件数の表示 --}}
    @php
        $page_total_views = FrameConfig::getConfigValueAndOld($frame_configs, DatabaseFrameConfig::database_page_total_views, ShowType::not_show);
    @endphp
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">{{DatabaseFrameConfig::getDescription('database_page_total_views')}}</label>
        <div class="{{$frame->getSettingInputClass()}}">
            @foreach (ShowType::getMembers() as $key => $type)
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" value="{{$key}}" id="page_total_views{{$key}}" name="database_page_total_views" class="custom-control-input" @if ($page_total_views == $key) checked="checked" @endif>
                <label class="custom-control-label" for="page_total_views{{$key}}" id="page_total_views{{$key}}">{{$type}}</label>
            </div>
            @endforeach
            <small class="form-text text-muted">
                表示している一覧の件数と総件数を表示します。
            </small>
        </div>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                </button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i>
                    <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    @if (empty($view_frame->id))
                        登録確定
                    @else
                        変更確定
                    @endif
                    </span>
                </button>
            </div>
        </div>
    </div>
</form>
</div>

<script>
    const app_{{ $frame->id }} = new Vue({
        el: "#app_{{ $frame->id }}",
        data: function() {
            return {
                trend_words: [],
            }
        },
        methods: {
            fetchTrendWords: function (period, old) {
                let self = this;
                axios.get("{{url('/')}}/json/databases/trendWords/{{$page->id}}/{{$frame_id}}/{{$database->id}}/?period=" + period + "&old=" + old)
                    .then(function(res){
                        self.trend_words = res.data;
                    })
                    .catch(function (error) {
                        console.log(error)
                    });
            },
            fetchTrendWordsDaily: function () {
                this.fetchTrendWords("daily", false);
            },
            fetchTrendWordsWeekly: function () {
                this.fetchTrendWords("weekly", false);
            },
            fetchTrendWordsMonthly: function () {
                this.fetchTrendWords("monthly", false);
            },
            fetchTrendWordsOld: function () {
                this.fetchTrendWords("", true);
            },
            deleteTrendWord: function (index) {
                this.trend_words.splice(index, 1)
            },
        },
        mounted: function () {
            this.fetchTrendWordsOld();
        }
    });
</script>

@endif
@endsection
