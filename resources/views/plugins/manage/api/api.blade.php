{{--
 * API管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category API追加
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
<div class="card-header p-0">
{{-- 機能選択タブ --}}
@include('plugins.manage.api.api_tab')
</div>

{{-- 削除ボタンのアクション --}}
<script type="text/javascript">
    function form_delete(id) {
        if (confirm('秘密コードを削除します。\nよろしいですか？')) {
            form_delete_secret.action = "{{url('/manage/api/delete')}}/" + id;
            form_delete_secret.submit();
        }
    }
</script>

<form action="" method="POST" name="form_delete_secret" class="">
    {{ csrf_field() }}
</form>

<form name="form_apis" id="form_apis" class="form-horizontal" method="post" action="{{url('/')}}/manage/api/update">
    {{ csrf_field() }}
    <div class="table-responsive">
        <table class="card-body table">
        <thead>
            <th nowrap>利用名</th>
            <th nowrap>秘密コード</th>
            <th nowrap>使用API</th>
            <th nowrap><i class="fas fa-trash-alt"></i></th>
        </thead>
        <tbody>
            @foreach($api_secrets as $api_secret)
            <input type="hidden" name="api_secrets[{{$loop->iteration}}][id]" value="{{$api_secret->id}}">
            <tr>
                <td class="table-text col-1 p-1 w-auto">
                    <div class="form-group mb-0">
                        <input type="text" name="api_secrets[{{$loop->iteration}}][secret_name]" value="{{$api_secret->secret_name}}" class="form-control">
                    </div>
                </td>
                <td class="table-text col-1 p-1 w-25">
                    <div class="form-group mb-0">
                        <input type="text" name="api_secrets[{{$loop->iteration}}][secret_code]" value="{{$api_secret->secret_code}}" class="form-control">
                    </div>
                </td>
                <td class="table-text p-1 w-auto">
                    <div class="container-fluid row">
                        @foreach ($api_secret->getApiCheckbpoxs($api_inis) as $api_name => $api_check)
                            <div class="custom-control custom-checkbox custom-control-inline">
                                @if($api_check['check'])
                                    <input type="checkbox" name="api_secrets[{{$loop->parent->iteration}}][apis][{{$api_name}}]" value="{{$api_name}}" id="apis_{{$loop->parent->iteration}}_{{$api_name}}" class="custom-control-input" checked="checked">
                                @else
                                    <input type="checkbox" name="api_secrets[{{$loop->parent->iteration}}][apis][{{$api_name}}]" value="{{$api_name}}" id="apis_{{$loop->parent->iteration}}_{{$api_name}}" class="custom-control-input">
                                @endif
                                <label class="custom-control-label" for="apis_{{$loop->parent->iteration}}_{{$api_name}}">{{$api_check['plugin_name_full']}}</label>
                            </div>
                        @endforeach
                    </div>
                </td>
                <td class="table-text p-1 w-auto">
                    <a href="javascript:form_delete('{{$api_secret->id}}');"><span class="btn btn-danger"><i class="fas fa-trash-alt"></i></span></a>
                </td>
            </tr>
            @endforeach
            <tr>
                <td class="table-text col-1 p-1 w-auto">
                    <div class="form-group mb-0">
                        <input type="text" name="secret_name" value="{{old('secret_name')}}" class="form-control">
                    </div>
                </td>
                <td class="table-text col-1 p-1 w-auto">
                    <div class="form-group mb-0">
                        <input type="text" name="secret_code" value="{{old('secret_code')}}" class="form-control">
                    </div>
                </td>
                <td class="table-text p-1 w-auto" nowrap>
                    <div class="container-fluid row">
                    @foreach ($api_inis as $api_name => $api_check)
                        <div class="custom-control custom-checkbox custom-control-inline">
                            @if (old('apis')[$api_name])
                                <input type="checkbox" name="apis[{{$api_name}}]" value="{{$api_name}}" id="apis_{{$api_name}}" class="custom-control-input" checked="checked">
                            @else
                                <input type="checkbox" name="apis[{{$api_name}}]" value="{{$api_name}}" id="apis_{{$api_name}}" class="custom-control-input">
                            @endif
                            <label class="custom-control-label" for="apis_{{$api_name}}">{{$api_check['plugin_name_full']}}</label>
                        </div>
                    @endforeach
                    </div>
                </td>
            </tr>
        </tbody>
        </table>
        @if ($errors && $errors->has('secret_name')) <div class="text-danger">{{$errors->first('secret_name')}}</div> @endif
        @if ($errors && $errors->has('secret_code')) <div class="text-danger">{{$errors->first('secret_code')}}</div> @endif

        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
        </div>
    </div>
</form>
</div>

@endsection
