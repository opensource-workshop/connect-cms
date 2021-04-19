{{--
 * コード管理のメインテンプレート
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.code.code_manage_tab')
    </div>
    <div class="card-body">

        {{-- 警告メッセージエリア --}}
        @if (! $config)
            <div class="alert alert-warning" role="alert">
                表示設定が未設定です。<a href="{{url('/')}}/manage/code/display" class="alert-link">表示設定</a>から設定してください。
            </div>
        @endif

        {{-- 検索エリア --}}
        <form action="{{url('/')}}/manage/code/index" method="GET" class="form-horizontal">
            <div class="input-group">
                <input type="text" name="search_words" value="{{$search_words}}" class="form-control">
                <button type="button" class="btn text-muted" style="margin-left: -37px; z-index: 100;" onclick="location.href='{{url('/')}}/manage/code/index?page=1'">
                    <i class="fa fa-times"></i>
                </button>
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search" aria-label="検索" role="presentation"></i></button>
                </div>
                <div class="ml-2">
                    <a href="https://connect-cms.jp/manual/manager/code#collapse-search-help" target="_brank">
                        <span class="btn btn-link"><i class="fas fa-question-circle" data-toggle="tooltip" title="オンラインマニュアルはこちら"></i></span>
                    </a>
                </div>
            </div>
        </form>

        {{-- ラベル検索エリア --}}
        <div class="mt-3">
            {{--
            <button type="button" class="btn btn-secondary btn-sm" onclick="location.href='{{url('/')}}/manage/code/index?page=1&search_words=type_code1=location'">
                場所マスタ <span class="badge badge-light">3</span>
            </button>
            --}}
            @foreach($codes_searches as $codes_search)
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="location.href='{{url('/')}}/manage/code/index?page=1&search_words={{$codes_search->search_words}}'">
                <i class="fas fa-search" aria-label="検索" role="presentation"></i> {{$codes_search->name}}
            </button>
            @endforeach
        </div>

        {{-- 一覧エリア --}}
        <div class="mt-3"><span class="badge badge-pill badge-light">{{ $codes->total() }} 件</span></div>
        <table class="table table-bordered table_border_radius table-hover cc-font-90">
            <tbody>
                <tr class="bg-light d-none d-sm-table-row">
                    <th class="d-block d-sm-table-cell text-break">プラグイン</th>
                    @foreach(CodeColumn::getIndexColumn() as $column_key => $column_value)
                        @if(in_array($column_key, $config->value_array) == $column_key)
                            <th class="d-block d-sm-table-cell text-break">{{$column_value}}</th>
                        @endif
                    @endforeach
                </tr>

                @foreach($codes as $code)
                <tr>
                    <th class="d-block d-sm-table-cell bg-light">
                        <a href="{{url('/')}}/manage/code/edit/{{$code->id}}?page={{$paginate_page}}&search_words={{$search_words}}"><i class="far fa-edit"></i></a>
                        <span class="d-sm-none">プラグイン：</span>{{$code->plugin_name_full}}
                    </th>
                    @foreach(CodeColumn::getIndexColumn() as $column_key => $column_value)
                        @if(in_array($column_key, $config->value_array) == $column_key)
                        {{-- 表示例
                        <td class="d-block d-sm-table-cell"><span class="d-sm-none">buckets_id：</span>$code->buckets_id</td>
                        --}}
                        <td class="d-block d-sm-table-cell"><span class="d-sm-none">{{$column_value}}：</span>{{$code->$column_key}}</td>
                        @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $codes->appends(['search_words' => $search_words])->links() }}

    </div>
</div>

@endsection
