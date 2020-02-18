{{--
 * コード登録画面のテンプレート
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
    function submitActionConfirm(url, message = '削除します。\nよろしいですか？') {
        if (confirm(message)) {
            form_code.action = url;
            form_code.submit();
        }
    }
</script>

</div>
<div class="card-body">

    <form name="form_code" action="" method="POST" class="form-horizontal">
        {{ csrf_field() }}
        <input name="page" value="{{$paginate_page}}" type="hidden">

        <!-- Code form  -->
        @if ($code->id)
        <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-right">コピーして登録画面へ</label>
            <div class="col-md-9 d-sm-flex align-items-center">
                <button type="button" class="btn btn-outline-primary form-horizontal" onclick="submitAction('{{url('/manage/code/regist')}}')">
                    <i class="fas fa-copy "></i> コピー
                </button>
            </div>
        </div>
        @endif

        <div class="form-group row">
            <label for="plugin_name" class="col-md-3 col-form-label text-md-right">plugin_name</label>
            <div class="col-md-9">
                <select name="plugin_name" id="plugin_name" class="form-control">
                    <option value=""@if($code->plugin_name == "") selected @endif>設定なし</option>
                    @foreach ($plugins as $plugin)
                        <option value="{{$plugin->plugin_name}}"@if(old('plugin_name', $code->plugin_name) == $plugin->plugin_name) selected @endif>{{$plugin->plugin_name_full}}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label for="buckets_id" class="col-md-3 col-form-label text-md-right">buckets_id</label>
            <div class="col-md-9">
                <input type="text" name="buckets_id" id="buckets_id" value="{{old('buckets_id', $code->buckets_id)}}" class="form-control">
            </div>
        </div>
        <div class="form-group row">
            <label for="prefix" class="col-md-3 col-form-label text-md-right">prefix</label>
            <div class="col-md-9">
                <input type="text" name="prefix" id="prefix" value="{{old('prefix', $code->prefix)}}" class="form-control">
            </div>
        </div>

        <div class="form-group row">
            <label for="type_name" class="col-md-3 col-form-label text-md-right">type_name</label>
            <div class="col-md-9">
                <input type="text" name="type_name" id="type_name" value="{{old('type_name', $code->type_name)}}" class="form-control">
            </div>
        </div>
        <div class="form-group row">
            <label for="type_code1" class="col-md-3 col-form-label text-md-right">type_code1</label>
            <div class="col-md-9">
                <input type="text" name="type_code1" id="type_code1" value="{{old('type_code1', $code->type_code1)}}" class="form-control">
            </div>
        </div>
        <div class="form-group row">
            <label for="type_code2" class="col-md-3 col-form-label text-md-right">type_code2</label>
            <div class="col-md-9">
                <input type="text" name="type_code2" id="type_code2" value="{{old('type_code2', $code->type_code2)}}" class="form-control">
            </div>
        </div>
        <div class="form-group row">
            <label for="type_code3" class="col-md-3 col-form-label text-md-right">type_code3</label>
            <div class="col-md-9">
                <input type="text" name="type_code3" id="type_code3" value="{{old('type_code3', $code->type_code3)}}" class="form-control">
            </div>
        </div>
        <div class="form-group row">
            <label for="type_code4" class="col-md-3 col-form-label text-md-right">type_code4</label>
            <div class="col-md-9">
                <input type="text" name="type_code4" id="type_code4" value="{{old('type_code4', $code->type_code4)}}" class="form-control">
            </div>
        </div>
        <div class="form-group row">
            <label for="type_code5" class="col-md-3 col-form-label text-md-right">type_code5</label>
            <div class="col-md-9">
                <input type="text" name="type_code5" id="type_code5" value="{{old('type_code5', $code->type_code5)}}" class="form-control">
            </div>
        </div>

        <div class="form-group row">
            <label for="code" class="col-md-3 col-form-label text-md-right">コード <label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <input type="text" name="code" id="code" value="{{{old('code', $code->code)}}}" class="form-control">
                @if ($errors && $errors->has('code')) <div class="text-danger">{{$errors->first('code')}}</div> @endif
            </div>
        </div>
        <div class="form-group row">
            <label for="value" class="col-md-3 col-form-label text-md-right">値 <label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <input type="text" name="value" id="value" value="{{old('value', $code->value)}}" class="form-control">
                @if ($errors && $errors->has('value')) <div class="text-danger">{{$errors->first('value')}}</div> @endif
            </div>
        </div>
        <div class="form-group row">
            <label for="additional1" class="col-md-3 col-form-label text-md-right">additional1</label>
            <div class="col-md-9">
                <input type="text" name="additional1" id="additional1" value="{{old('additional1', $code->additional1)}}" class="form-control">
                @if ($errors && $errors->has('additional1')) <div class="text-danger">{{$errors->first('additional1')}}</div> @endif
            </div>
        </div>
        <div class="form-group row">
            <label for="additional2" class="col-md-3 col-form-label text-md-right">additional2</label>
            <div class="col-md-9">
                <input type="text" name="additional2" id="additional2" value="{{old('additional2', $code->additional2)}}" class="form-control">
                @if ($errors && $errors->has('additional2')) <div class="text-danger">{{$errors->first('additional2')}}</div> @endif
            </div>
        </div>
        <div class="form-group row">
            <label for="additional3" class="col-md-3 col-form-label text-md-right">additional3</label>
            <div class="col-md-9">
                <input type="text" name="additional3" id="additional3" value="{{old('additional3', $code->additional3)}}" class="form-control">
                @if ($errors && $errors->has('additional3')) <div class="text-danger">{{$errors->first('additional3')}}</div> @endif
            </div>
        </div>
        <div class="form-group row">
            <label for="additional4" class="col-md-3 col-form-label text-md-right">additional4</label>
            <div class="col-md-9">
                <input type="text" name="additional4" id="additional4" value="{{old('additional4', $code->additional4)}}" class="form-control">
                @if ($errors && $errors->has('additional4')) <div class="text-danger">{{$errors->first('additional4')}}</div> @endif
            </div>
        </div>
        <div class="form-group row">
            <label for="additional5" class="col-md-3 col-form-label text-md-right">additional5</label>
            <div class="col-md-9">
                <input type="text" name="additional5" id="additional5" value="{{old('additional5', $code->additional5)}}" class="form-control">
                @if ($errors && $errors->has('additional5')) <div class="text-danger">{{$errors->first('additional5')}}</div> @endif
            </div>
        </div>
        <div class="form-group row">
            <label for="display_sequence" class="col-md-3 col-form-label text-md-right">並び順</label>
            <div class="col-md-9">
                <input type="text" name="display_sequence" id="display_sequence" value="{{old('display_sequence', $code->display_sequence)}}" class="form-control">
            </div>
        </div>

        <!-- Add or Update code Button -->
        <div class="form-group row">
            <div class="offset-sm-3 col-sm-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/code')}}?page={{$paginate_page}}'"><i class="fas fa-times"></i> キャンセル</button>
                @if ($code->id)
                <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="submitAction('{{url('/manage/code/update')}}/{{$code->id}}')">
                    <i class="fas fa-check"></i> 更新
                </button>
                @else
                <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="submitAction('{{url('/manage/code/store')}}')">
                    <i class="fas fa-check"></i> 登録
                </button>
                @endif
            </div>
            @if ($code->id)
            <div class="col-sm-3 pull-right text-right">
                <button type="button" class="btn btn-danger form-horizontal" onclick="submitActionConfirm('{{url('/manage/code/destroy')}}/{{$code->id}}')">
                    <i class="fas fa-trash-alt"></i> 削除
                </button>
            </div>
            @endif
        </div>
    </form>

</div>
</div>

@endsection
