{{--
 * グループ覧のテンプレート
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
        @include('plugins.manage.group.group_manage_tab')
    </div>
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover cc-font-90">
            <thead>
                <tr>
                    <th nowrap>グループ名</th>
                    {{-- <th nowrap>編集</th> --}}
                    <th nowrap><i class="fas fa-users" title="ユーザ数"></i></th>
                    <th nowrap>表示順</th>
                    <th nowrap>作成日</th>
                    <th nowrap>更新日</th>
                </tr>
            </thead>
            <tbody>
            @foreach($groups as $group)
                <tr>
                    <td>
                        <a href="{{url('/')}}/manage/group/edit/{{$group->id}}">
                            <i class="far fa-edit"></i>
                        </a>
                        {{$group->name}}
                    </td>
                    {{-- <th nowrap><a href="{{url('/')}}/manage/group/list/{{$group->id}}" class="badge badge-secondary">編集</a></th> --}}
                    <td><a href="{{url('/')}}/manage/group/edit/{{$group->id}}">{{$group->group_user->count()}}</a></td>
                    <td>{{$group->display_sequence}}</td>
                    <td>{{$group->created_at->format('Y/m/d')}}</td>
                    <td>{{$group->updated_at->format('Y/m/d')}}</td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>

        {{-- ページング処理 --}}
        @if($groups)
        <div class="text-center">
            {{$groups->links()}}
        </div>
        @endif
    </div>
</div>

@endsection
