{{--
 * 役割設定画面のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
<div class="card-header p-0">

{{-- 機能選択タブ --}}
@include('plugins.manage.user.user_manage_tab')

</div>
<div class="card-body">

    {{-- エラーメッセージ --}}
    @if ($errors)
        <div class="card border-danger">
            <div class="card-body">
                <span class="text-danger">
                    @foreach($errors->all() as $error)
                    <i class="fas fa-exclamation-triangle"></i> {{$error}}<br />
                    @endforeach
                </span>
                <span class="text-secondary">
                    @if ($errors->has('additional1') || $errors->has('name') || $errors->has('value'))
                    <i class="fas fa-exclamation-circle"></i> 追加行を入力する場合は、すべての項目を入力してください。
                    @endif
                </span>
            </div>
        </div>
    @endif

    {{-- 削除ボタンのアクション --}}
    <script type="text/javascript">
        function form_delete(id) {
            if (confirm('役割を削除します。\nよろしいですか？')) {
                form_delete_original_role.action = "{{url('/manage/user/deleteOriginalRole')}}/" + id;
                form_delete_original_role.submit();
            }
        }
    </script>

    <form action="" method="POST" name="form_delete_original_role" class="">
        {{ csrf_field() }}
    </form>

    <form action="/manage/user/saveOriginalRoles" method="POST" class="">
        {{ csrf_field() }}

        <div class="form-group table-responsive">
            <table class="table table-hover" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th nowrap class="col-1">表示順</th>
                    <th nowrap>定義名（半角英数字）</th>
                    <th nowrap>表示名</th>
                    <th nowrap><i class="fas fa-trash-alt"></i></th>
                </tr>
            </thead>
            <tbody>
            @foreach($configs as $config)
                <tr>
                    <td nowrap>
                        <input type="hidden" value="{{$config->id}}" name="configs_id[{{$config->id}}]"></input>
                        <input type="text" value="{{old('additional1.'.$config->id, $config->additional1)}}" name="additional1[{{$config->id}}]" class="form-control"></input>
                    </td>
                    <td nowrap>
                        <input type="text" value="{{old('name.'.$config->id, $config->name)}}" name="name[{{$config->id}}]" class="form-control"></input>
                    </td>
                    <td nowrap>
                        <input type="text" value="{{old('value.'.$config->id, $config->value)}}" name="value[{{$config->id}}]" class="form-control"></input>
                    </td>
                    <td nowrap>
                        <a href="javascript:form_delete('{{$config->id}}');"><span class="btn btn-danger"><i class="fas fa-trash-alt"></i></span></a>
                    </td>
                </tr>
            @endforeach
            @if ($create_flag)
                <tr>
                    <td nowrap>
                        <input type="text" value="" name="add_additional1" class="form-control"></input>
                    </td>
                    <td nowrap>
                        <input type="text" value="" name="add_name" class="form-control"></input>
                    </td>
                    <td nowrap>
                        <input type="text" value="" name="add_value" class="form-control"></input>
                    </td>
                    <td nowrap>
                    </td>
                </tr>
            @else
                <tr>
                    <td nowrap>
                        <input type="text" value="{{old('add_additional1', '')}}" name="add_additional1" class="form-control"></input>
                    </td>
                    <td nowrap>
                        <input type="text" value="{{old('add_name', '')}}" name="add_name" class="form-control"></input>
                    </td>
                    <td nowrap>
                        <input type="text" value="{{old('add_value', '')}}" name="add_value" class="form-control"></input>
                    </td>
                    <td nowrap>
                    </td>
                </tr>
            @endif
            </tbody>
            </table>
        </div>

        <div class="form-group text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/user/originalRole')}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 変更</span></button>
        </div>
    </form>

</div>
</div>


@endsection
