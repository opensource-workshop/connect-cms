{{--
 * グループ登録画面のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category グループ管理
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

        @if (isset($id) && $id)
        <form class="form-horizontal" method="POST" action="{{url('/manage/group/update/')}}/{{$id}}">
        @else
        <form class="form-horizontal" method="POST" action="{{url('/manage/group/update/')}}">
        @endif
            {{ csrf_field() }}

            <div class="form-group row{{ $errors->has('name') ? ' has-error' : '' }}">
                <label for="name" class="col-md-4 col-form-label text-md-right">グループ名</label>

                <div class="col-md-8">
                    <input id="name" type="text" class="form-control" name="name" value="{{ old('name', $group->name) }}" placeholder="グループ名を入力します。" required autofocus>
                    @if ($errors->has('name')) <div class="text-danger">{{$errors->first('name')}}</div> @endif
                </div>
            </div>

            <div class="form-group row text-center">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/group')}}'">
                        <i class="fas fa-times"></i> キャンセル
                    </button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 
                        @if (isset($function) && $function == 'edit')
                            グループ変更
                        @else
                            グループ登録
                        @endif
                    </button>
                </div>
                {{-- 既存グループの場合は削除処理のボタンも表示 --}}
                @if (isset($id) && $id)
                    <div class="col-sm-3 pull-right text-right">
                        <a data-toggle="collapse" href="#collapse{{$id}}">
                            <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> <span class="hidden-xs">削除</span></span>
                        </a>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

@if (isset($id) && $id)
<div id="collapse{{$id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">グループを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/manage/group/delete/')}}/{{$id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('グループを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif

@if (isset($id) && $id)
<div class="card mt-3">
    <div class="card-header">【{{$group->name}}】グループ参加ユーザ一覧</div>
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover cc-font-90">
            <thead>
                <tr>
                    <th nowrap>ユーザ名</th>
                    <th nowrap>グループ権限</th>
                    <th nowrap>作成日</th>
                    <th nowrap>更新日</th>
                </tr>
            </thead>
            <tbody>
            @foreach($group_users as $group_user)
                <tr>
                    <td>{{$group_user->user_name}}</td>
                    <td>{{GroupType::getDescription($group_user->group_role)}}</td>
                    <td>{{$group_user->created_at->format('Y/m/d')}}</td>
                    <td>{{$group_user->updated_at->format('Y/m/d')}}</td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>

        {{-- ページング処理 --}}
        @if($group_users)
        <div class="text-center">
            {{$group_users->links()}}
        </div>
        @endif
    </div>
</div>
@endif
@endsection
