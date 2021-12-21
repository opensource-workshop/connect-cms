{{--
 * ユーザファイル管理の編集テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category アップロードファイル管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.uploadfile.uploadfile_manage_tab')
    </div>

    <form action="{{url('/manage/uploadfile/saveUserdir')}}" method="POST" class="form-horizontal">
        {{ csrf_field() }}

        <div class="card-body table-responsive">

            @if (session('info_message'))
                <div class="alert alert-info">
                    {{session('info_message')}}
                </div>
            @endif

            <table class="table text-nowrap">
            <thead>
                <tr>
                    <th nowrap="">ディレクトリ名</th>
                    <th nowrap="">閲覧設定</th>
                </tr>
            </thead>
            <tbody>
                @foreach($user_directories as $user_directory)

                    @php
                        $value = "0";
                        $userdir_allow = $userdir_allows->where('name', $user_directory)->first();
                        if ($userdir_allow) {
                            $value = $userdir_allow->value;
                        }
                    @endphp
                <tr>
                    <td nowrap="">{{$user_directory}}</td>
                    <td nowrap="">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                type="radio" value="" class="custom-control-input" id="{{$user_directory}}_0"
                                name="userdir[{{$user_directory}}]" @if(old('{{$user_directory}}', $value) == "") checked @endif>
                            <label class="custom-control-label" for="{{$user_directory}}_0" id="{{$user_directory}}_0">
                                閲覧させない。
                            </label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                type="radio" value="allow_all" class="custom-control-input" id="{{$user_directory}}_1"
                                name="userdir[{{$user_directory}}]" @if(old('{{$user_directory}}', $value) == "allow_all") checked @endif>
                            <label class="custom-control-label" for="{{$user_directory}}_1" id="{{$user_directory}}_1">
                                誰でも閲覧許可
                            </label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                type="radio" value="allow_login" class="custom-control-input" id="{{$user_directory}}_2"
                                name="userdir[{{$user_directory}}]" @if(old('{{$user_directory}}', $value) == "allow_login") checked @endif>
                            <label class="custom-control-label" for="{{$user_directory}}_2" id="{{$user_directory}}_2">
                                ログインユーザのみ閲覧許可
                            </label>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>

        <div class="form-group text-center">
            <button type="reset" class="btn btn-secondary mr-2"><i class="fas fa-undo-alt"></i><span class="d-none d-md-inline"> キャンセル</span></button>
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
        </div>
    </form>
</div>
@endsection
