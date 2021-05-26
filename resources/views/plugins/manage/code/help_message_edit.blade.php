{{--
 * 注釈登録・更新画面のテンプレート
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

    <div class="alert alert-info" role="alert">
        コード登録画面の注釈を登録します。<br>
        登録した注釈は、コード登録画面の注釈名に表示され、選択すると各項目下部に注釈が表示されます。
    </div>

    @include('common.errors_form_line')

    <form name="form_code" action="" method="POST" class="form-horizontal">
        {{ csrf_field() }}
        <input name="page" value="{{$paginate_page}}" type="hidden">
        <input name="search_words" value="{{$search_words}}" type="hidden">

        <!-- Code form  -->
        @if ($codes_help_message->id)
        <div class="form-group form-row">
            <label class="col-md-3 col-form-label text-md-right">コピーして登録画面へ</label>
            <div class="col-md-9 d-sm-flex align-items-center">
                <button type="button" class="btn btn-outline-primary form-horizontal" onclick="submitAction('{{url('/')}}/manage/code/helpMessageRegist')">
                    <i class="fas fa-copy "></i> コピー
                </button>
            </div>
        </div>
        @endif

        <div class="form-group form-row">
            <label for="name" class="col-md-3 col-form-label text-md-right">注釈名 <label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <input type="text" name="name" id="name" value="{{old('name', $codes_help_message->name)}}" class="form-control">
                @if ($errors && $errors->has('name')) <div class="text-danger">{{$errors->first('name')}}</div> @endif
            </div>
        </div>
        <div class="form-group form-row">
            <label for="alias_key" class="col-md-3 col-form-label text-md-right">注釈キー <label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <input type="text" name="alias_key" id="alias_key" value="{{old('alias_key', $codes_help_message->alias_key)}}" class="form-control">
                @if ($errors && $errors->has('alias_key')) <div class="text-danger">{{$errors->first('alias_key')}}</div> @endif
            </div>
        </div>

        @php
        $colums = [
            'codes_help_messages_alias_key_help_message' => '注釈設定注釈',
            'plugin_name_help_message' => 'プラグイン注釈',
            'buckets_id_help_message' => 'buckets_id注釈',
            'prefix_help_message' => 'prefix注釈',
            'type_name_help_message' => 'type_name注釈',
            'type_code1_help_message' => 'type_code1注釈',
            'type_code2_help_message' => 'type_code2注釈',
            'type_code3_help_message' => 'type_code3注釈',
            'type_code4_help_message' => 'type_code4注釈',
            'type_code5_help_message' => 'type_code5注釈',
            'code_help_message' => 'コード注釈',
            'value_help_message' => '値注釈',
            'additional1_help_message' => 'additional1注釈',
            'additional2_help_message' => 'additional2注釈',
            'additional3_help_message' => 'additional3注釈',
            'additional4_help_message' => 'additional4注釈',
            'additional5_help_message' => 'additional5注釈',
            'additional6_help_message' => 'additional6注釈',
            'additional7_help_message' => 'additional7注釈',
            'additional8_help_message' => 'additional8注釈',
            'additional9_help_message' => 'additional9注釈',
            'additional10_help_message' => 'additional10注釈',
            'display_sequence_help_message' => '表示順注釈',
            'display_sequence' => '表示順',
        ];
        @endphp
        @foreach($colums as $colum_key => $colum_value)
            {{-- 表示例
            <div class="form-group form-row">
                <label for="plugin_name_help_message" class="col-md-3 col-form-label text-md-right">plugin_name_help_message</label>
                <div class="col-md-9">
                    <input type="text" name="plugin_name_help_message" id="plugin_name_help_message" value="old('plugin_name_help_message', $codes_help_message->plugin_name_help_message)" class="form-control">
                </div>
            </div>
            --}}
            <div class="form-group form-row">
                <label for="{{$colum_key}}" class="col-md-3 col-form-label text-md-right">{{$colum_value}}</label>
                <div class="col-md-9">
                    <input type="text" name="{{$colum_key}}" id="{{$colum_key}}" value="{{old($colum_key, $codes_help_message->$colum_key)}}" class="form-control">
                </div>
            </div>
        @endforeach

        <!-- Add or Update code Button -->
        <div class="form-group text-center">
            <div class="form-row">
                <div class="offset-xl-3 col-9 col-xl-6">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/manage/code/helpMessages?page={{$paginate_page}}'"><i class="fas fa-times"></i> キャンセル</button>
                    @if ($codes_help_message->id)
                    <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="submitAction('{{url('/')}}/manage/code/helpMessageUpdate/{{$codes_help_message->id}}')">
                        <i class="fas fa-check"></i> 更新
                    </button>
                    @else
                    <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="submitAction('{{url('/')}}/manage/code/helpMessageStore')">
                        <i class="fas fa-check"></i> 登録
                    </button>
                    @endif
                </div>
                @if ($codes_help_message->id)
                    <div class="col-3 col-xl-3 text-right">
                        <a data-toggle="collapse" href="#collapse{{$codes_help_message->id}}">
                            <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> 削除</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </form>

    <div id="collapse{{$codes_help_message->id}}" class="collapse">
        <div class="card border-danger">
            <div class="card-body">
                <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/')}}/manage/code/helpMessageDestroy/{{$codes_help_message->id}}" method="POST">
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
