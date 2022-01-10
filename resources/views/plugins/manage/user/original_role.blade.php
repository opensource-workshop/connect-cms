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
        @include('plugins.common.errors_all')

        {{-- 削除ボタンのアクション --}}
        <script type="text/javascript">
            function form_delete(id) {
                if (confirm('役割を削除します。\nよろしいですか？')) {
                    form_delete_original_role.action = "{{url('/manage/user/deleteOriginalRole')}}/" + id;
                    form_delete_original_role.submit();
                }
            }
        </script>

        <form action="" method="POST" name="form_delete_original_role">
            {{ csrf_field() }}
        </form>

        <form action="{{url('/')}}/manage/user/saveOriginalRoles" method="POST">
            {{ csrf_field() }}

            <div class="form-group table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th nowrap>表示順</th>
                            <th nowrap>定義名（半角英数字）</th>
                            <th nowrap>表示名</th>
                            <th nowrap><i class="fas fa-trash-alt"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($configs as $config)
                            <tr>
                                <td nowrap>
                                    <input type="hidden" value="{{$config->id}}" name="configs_id[{{$config->id}}]">
                                    <input type="text" value="{{old('additional1.'.$config->id, $config->additional1)}}" name="additional1[{{$config->id}}]" class="form-control @if ($errors && $errors->has('additional1.'.$config->id)) border-danger @endif">
                                </td>
                                <td nowrap>
                                    <input type="text" value="{{old('name.'.$config->id, $config->name)}}" name="name[{{$config->id}}]" class="form-control @if ($errors && $errors->has('name.'.$config->id)) border-danger @endif">
                                </td>
                                <td nowrap>
                                    <input type="text" value="{{old('value.'.$config->id, $config->value)}}" name="value[{{$config->id}}]" class="form-control @if ($errors && $errors->has('value.'.$config->id)) border-danger @endif">
                                </td>
                                <td nowrap>
                                    <a href="javascript:form_delete('{{$config->id}}');"><span class="btn btn-danger"><i class="fas fa-trash-alt"></i></span></a>
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td nowrap>
                                <input type="text" value="{{old('add_additional1')}}" name="add_additional1" class="form-control @if ($errors && $errors->has('add_additional1')) border-danger @endif">
                            </td>
                            <td nowrap>
                                <input type="text" value="{{old('add_name')}}" name="add_name" class="form-control @if ($errors && $errors->has('add_name')) border-danger @endif">
                            </td>
                            <td nowrap>
                                <input type="text" value="{{old('add_value')}}" name="add_value" class="form-control @if ($errors && $errors->has('add_value')) border-danger @endif">
                            </td>
                            <td nowrap>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card card-body bg-light p-2 mb-3">
                <ul>
                    <li>
                        課題管理プラグインで利用できる「定義名」は、
                        @foreach (RoleName::getMembers() as $key => $name)
                            @if ($loop->last)
                                <code>{{$key}}</code>
                            @else
                                <code>{{$key}}</code>,
                            @endif
                        @endforeach
                        です。
                    </li>
                </ul>
            </div>

            <div class="form-group text-center">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/user/originalRole')}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 変更</span></button>
            </div>
        </form>

    </div>
</div>

@endsection
