{{--
 * (コード一覧)表示設定 画面のテンプレート
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

        <div class="alert alert-info" role="alert">
            コード一覧に表示する項目を設定します。
        </div>

        <form action="" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <div class="form-group row">
                <div class="col-md-3 text-md-right">プラグイン</div>
                <div class="col-md-9">
                    表示する
                    <input type="hidden" name="code_list_display_colums[plugin_name]" value="plugin_name">
                    <div class="text-muted">
                        プラグインは編集マークの表示に必要なため、必ず表示します。
                    </div>
                </div>
            </div>

            {{--
            <div class="form-group row">
                <div class="col-md-3 text-md-right">注釈名</div>
                <div class="col-md-9">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" name="code_list_display_colums[codes_help_messages_name]" value="codes_help_messages_name"@if(in_array('codes_help_messages_name', $config->value_array)) == 'codes_help_messages_name') checked @endif>表示する
                        </label>
                    </div>
                </div>
            </div>
            --}}

            @foreach(CodeColumn::getIndexColumn() as $column_key => $column_value)
                <div class="form-group row">
                    <div class="col-md-3 text-md-right">{{$column_value}}</div>
                    <div class="col-md-9">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="code_list_display_colums[{{$column_key}}]" value="{{$column_key}}"@if(in_array($column_key, $config->value_array)) == $column_key') checked @endif>表示する
                            </label>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Update code Button -->
            <div class="form-group row">
                <div class="offset-xl-3 col">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/manage/code?page={{$paginate_page}}'"><i class="fas fa-times"></i> キャンセル</button>
                    @if ($config->id)
                    <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="this.form.action='{{url('/')}}/manage/code/displayUpdate/{{$config->id}}'; this.form.submit();">
                        <i class="fas fa-check"></i> 更新
                    </button>
                    @else
                    <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="this.form.action='{{url('/')}}/manage/code/displayStore'; this.form.submit();">
                        <i class="fas fa-check"></i> 登録
                    </button>
                    @endif
                </div>
            </div>
        </form>

    </div>
</div>

@endsection
