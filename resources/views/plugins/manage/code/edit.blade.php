{{--
 * コード登録・更新画面のテンプレート
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

{{-- ボタンによってアクション切替 --}}
<script type="text/javascript">
    function submitAction(url) {
        form_code.action = url;
        form_code.submit();
    }
</script>

</div>
<div class="card-body">

    @include('plugins.common.errors_form_line')

    <form name="form_code" action="" method="POST" class="form-horizontal">
        {{ csrf_field() }}
        <input name="page" value="{{$paginate_page}}" type="hidden">
        <input name="search_words" value="{{$search_words}}" type="hidden">

        <!-- Code form  -->
        @if ($code->id)
        <div class="form-group form-row">
            <label class="col-md-3 col-form-label text-md-right">コピーして登録画面へ</label>
            <div class="col-md-9 d-sm-flex align-items-center">
                <button type="button" class="btn btn-outline-primary form-horizontal" onclick="submitAction('{{url('/')}}/manage/code/regist')">
                    <i class="fas fa-copy "></i> コピー
                </button>
            </div>
        </div>
        @endif

        @php
        if ($code->id) {
            // 更新画面 再表示
            $view_action_url = url('/') . '/manage/code/edit/' . $code->id;
        } else {
            // 登録画面 再表示
            $view_action_url = url('/') . '/manage/code/regist';
        }
        @endphp
        <div class="form-group form-row">
            <label for="codes_help_messages_alias_key" class="col-md-3 col-form-label text-md-right">注釈名</label>
            <div class="col-md-9">
                <select name="codes_help_messages_alias_key" id="codes_help_messages_alias_key" class="form-control" onchange="submitAction('{{$view_action_url}}')">
                    <option value=""@if($code->codes_help_messages_alias_key == "") selected @endif>設定なし</option>
                    @foreach ($codes_help_messages_all as $message)
                        <option value="{{$message->alias_key}}"@if(old('codes_help_messages_alias_key', $code->codes_help_messages_alias_key) == $message->alias_key) selected @endif>{{$message->name}}</option>
                    @endforeach
                </select>
                <div class="text-muted">{{$codes_help_message->codes_help_messages_alias_key_help_message}}</div>
            </div>
        </div>

        <div class="form-group form-row">
            <label for="plugin_name" class="col-md-3 col-form-label text-md-right">プラグイン</label>
            <div class="col-md-9">
                <select name="plugin_name" id="plugin_name" class="form-control">
                    <option value=""@if($code->plugin_name == "") selected @endif>設定なし</option>
                    @foreach ($plugins as $plugin)
                        <option value="{{strtolower($plugin->plugin_name)}}"@if(old('plugin_name', $code->plugin_name) == strtolower($plugin->plugin_name)) selected @endif>{{$plugin->plugin_name_full}}</option>
                    @endforeach
                </select>
                <div class="text-muted">{{$codes_help_message->plugin_name_help_message}}</div>
            </div>
        </div>

        @php
        $colums = [
            'buckets_id' => 'buckets_id_help_message',
            'prefix' => 'prefix_help_message',
            'type_name' => 'type_name_help_message',
            'type_code1' => 'type_code1_help_message',
            'type_code2' => 'type_code2_help_message',
            'type_code3' => 'type_code3_help_message',
            'type_code4' => 'type_code4_help_message',
            'type_code5' => 'type_code5_help_message',
        ];
        @endphp
        @foreach($colums as $colum_name => $colum_help_message_name)
            <div class="form-group form-row">
                <label for="{{$colum_name}}" class="col-md-3 col-form-label text-md-right">{{$colum_name}}</label>
                <div class="col-md-9">
                    <input type="text" name="{{$colum_name}}" id="{{$colum_name}}" value="{{old($colum_name, $code->$colum_name)}}" class="form-control">
                    <div class="text-muted">{{$codes_help_message->$colum_help_message_name}}</div>
                    @if ($errors && $errors->has($colum_name)) <div class="text-danger">{{$errors->first($colum_name)}}</div> @endif
                </div>
            </div>
        @endforeach

        <div class="form-group form-row">
            <label for="code" class="col-md-3 col-form-label text-md-right">コード <label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <input type="text" name="code" id="code" value="{{{old('code', $code->code)}}}" class="form-control">
                <div class="text-muted">{{$codes_help_message->code_help_message}}</div>
                @if ($errors && $errors->has('code')) <div class="text-danger">{{$errors->first('code')}}</div> @endif
            </div>
        </div>
        <div class="form-group form-row">
            <label for="value" class="col-md-3 col-form-label text-md-right">値 <label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <input type="text" name="value" id="value" value="{{old('value', $code->value)}}" class="form-control">
                <div class="text-muted">{{$codes_help_message->value_help_message}}</div>
                @if ($errors && $errors->has('value')) <div class="text-danger">{{$errors->first('value')}}</div> @endif
            </div>
        </div>

        {{-- additional1～10 --}}
        @php
        $colums = [
            'additional1' => 'additional1_help_message',
            'additional2' => 'additional2_help_message',
            'additional3' => 'additional3_help_message',
            'additional4' => 'additional4_help_message',
            'additional5' => 'additional5_help_message',
            'additional6' => 'additional6_help_message',
            'additional7' => 'additional7_help_message',
            'additional8' => 'additional8_help_message',
            'additional9' => 'additional9_help_message',
            'additional10' => 'additional10_help_message',
        ];
        @endphp
        @foreach($colums as $colum_name => $colum_help_message_name)
            <div class="form-group form-row">
                <label for="{{$colum_name}}" class="col-md-3 col-form-label text-md-right">{{$colum_name}}</label>
                <div class="col-md-9">
                    <input type="text" name="{{$colum_name}}" id="{{$colum_name}}" value="{{old($colum_name, $code->$colum_name)}}" class="form-control">
                    <div class="text-muted">{{$codes_help_message->$colum_help_message_name}}</div>
                    @if ($errors && $errors->has($colum_name)) <div class="text-danger">{{$errors->first($colum_name)}}</div> @endif
                </div>
            </div>
        @endforeach

        <div class="form-group form-row">
            <label for="display_sequence" class="col-md-3 col-form-label text-md-right">表示順</label>
            <div class="col-md-9">
                <input type="text" name="display_sequence" id="display_sequence" value="{{old('display_sequence', $code->display_sequence)}}" class="form-control">
                <div class="text-muted">{{$codes_help_message->display_sequence_help_message}}</div>
            </div>
        </div>

        <!-- Add or Update code Button -->
        <div class="form-group text-center">
            <div class="form-row">
                <div class="offset-xl-3 col-9 col-xl-6">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/manage/code?page={{$paginate_page}}&search_words={{$search_words}}'"><i class="fas fa-times"></i> キャンセル</button>
                    @if ($code->id)
                    <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="submitAction('{{url('/')}}/manage/code/update/{{$code->id}}')">
                        <i class="fas fa-check"></i> 更新
                    </button>
                    @else
                    <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="submitAction('{{url('/')}}/manage/code/store')">
                        <i class="fas fa-check"></i> 登録
                    </button>
                    @endif
                </div>

                @if ($code->id)
                    <div class="col-3 col-xl-3 text-right">
                        <a data-toggle="collapse" href="#collapse{{$code->id}}">
                            <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> 削除</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </form>

    <div id="collapse{{$code->id}}" class="collapse">
        <div class="card border-danger">
            <div class="card-body">
                <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/')}}/manage/code/destroy/{{$code->id}}" method="POST">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

</div>
</div>

@endsection
