{{--
 * 一覧表示設定 画面のテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コード管理
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

    <form action="/manage/code/displayUpdate/{{$config->id}}" method="POST" class="form-horizontal">
        {{ csrf_field() }}

        <!-- Code form  -->

        <div class="form-group row">
            <div class="col-md-3 text-md-right">プラグイン</div>
            <div class="col-md-9">
                表示する
                <input type="hidden" name="code_list_display_colums[plugin_name]" value="plugin_name">
                <div class="text-muted">
                    plugin_nameの表示は編集マークの表示に必要なため、必ず表示します。
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-3 text-md-right">buckets_name</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[buckets_name]" value="buckets_name"@if(in_array('buckets_name', $config->value_array)) == 'buckets_name') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-3 text-md-right">buckets_id</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[buckets_id]" value="buckets_id"@if(in_array('buckets_id', $config->value_array)) == 'buckets_id') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-3 text-md-right">prefix</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[prefix]" value="prefix"@if(in_array('prefix', $config->value_array)) == 'prefix') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-3 text-md-right">type_name</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[type_name]" value="type_name"@if(in_array('type_name', $config->value_array)) == 'type_name') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3 text-md-right">type_code1</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[type_code1]" value="type_code1"@if(in_array('type_code1', $config->value_array)) == 'type_code1') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3 text-md-right">type_code2</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[type_code2]" value="type_code2"@if(in_array('type_code2', $config->value_array)) == 'type_code2') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3 text-md-right">type_code3</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[type_code3]" value="type_code3"@if(in_array('type_code3', $config->value_array)) == 'type_code3') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3 text-md-right">type_code4</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[type_code4]" value="type_code4"@if(in_array('type_code4', $config->value_array)) == 'type_code4') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3 text-md-right">type_code5</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[type_code5]" value="type_code5"@if(in_array('type_code5', $config->value_array)) == 'type_code5') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-3 text-md-right">コード</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[code]" value="code"@if(in_array('code', $config->value_array)) == 'code') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3 text-md-right">値</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[value]" value="value"@if(in_array('value', $config->value_array)) == 'value') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-3 text-md-right">additional1</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[additional1]" value="additional1"@if(in_array('additional1', $config->value_array)) == 'additional1') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3 text-md-right">additional2</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[additional2]" value="additional2"@if(in_array('additional2', $config->value_array)) == 'additional2') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3 text-md-right">additional3</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[additional3]" value="additional3"@if(in_array('additional3', $config->value_array)) == 'additional3') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3 text-md-right">additional4</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[additional4]" value="additional4"@if(in_array('additional4', $config->value_array)) == 'additional4') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3 text-md-right">additional5</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[additional5]" value="additional5"@if(in_array('additional5', $config->value_array)) == 'additional5') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-3 text-md-right">並び順</div>
            <div class="col-md-9">
                <div class="form-check">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" name="code_list_display_colums[display_sequence]" value="display_sequence"@if(in_array('display_sequence', $config->value_array)) == 'display_sequence') checked @endif>表示する
                    </label>
                </div>
            </div>
        </div>

        <!-- Update code Button -->
        <div class="form-group row">
            <div class="offset-sm-3 col-sm-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/code')}}?page={{$paginate_page}}'"><i class="fas fa-times"></i> キャンセル</button>
                <button type="submit" class="btn btn-primary form-horizontal mr-2">
                    <i class="fas fa-check"></i> 更新
                </button>
            </div>
        </div>
    </form>

</div>
</div>

@endsection
