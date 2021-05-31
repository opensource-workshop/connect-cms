{{--
 * 一括削除画面テンプレート
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

    @if (session('flash_message'))
        <div class="alert alert-success">
            {{ session('flash_message') }}
        </div>
    @endif

    <div class="alert alert-info" role="alert">
        <i class="fas fa-exclamation-circle"></i> 状態が「仮削除」のユーザを一括削除します。<br />
        <i class="fas fa-exclamation-circle"></i> 削除対象ユーザは、[ <a href="{{url('/manage/user')}}">ユーザ一覧</a> ] の絞り込み条件で状態「仮削除」で絞り込む事で確認できます。<br />
        <i class="fas fa-exclamation-circle"></i> ユーザを「仮削除」に一括更新したい場合は、[ <a href="{{url('/manage/user/import')}}">CSVインポート</a> ] で状態を <code>3</code> (仮削除) に更新してください。<br />
        １人づつ変更するのであれば、ユーザ変更画面から状態を「仮削除」に変更できます。
    </div>

    <div class="form-group form-row">
        <label class="col-md text-md-right">削除対象ユーザ数</label>
        <div class="col-md">{{  $users->count()  }}人</div>
    </div>

    {{-- 既存ユーザの場合は削除処理のボタンも表示(自分自身の場合は表示しない) --}}
    <div class="form-group text-center">
        <a data-toggle="collapse" href="#collapse{{$id}}">
            <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> <span class="hidden-xs">一括削除</span></span>
        </a>
    </div>

    <div id="collapse{{$id}}" class="collapse">
        <div class="card border-danger">
            <div class="card-body">
                <span class="text-danger">仮削除ユーザを一括削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/manage/user/bulkDestroy/')}}/{{$id}}" method="POST">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('仮削除ユーザを一括削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

</div>

@endsection
