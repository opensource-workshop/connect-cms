{{--
 * 項目セット登録・更新画面のテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
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

        {{-- ボタンによってアクション切替 --}}
        <script type="text/javascript">
            function submitAction(url) {
                form_column_set.action = url;
                form_column_set.submit();
            }
        </script>
    </div>
    <div class="card-body">

        @include('plugins.common.errors_form_line')

        <div class="alert alert-info" role="alert">
            <i class="fas fa-exclamation-circle"></i> ユーザ項目をまとめたセットを追加・変更します。
        </div>

        <form name="form_column_set" action="" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <div class="form-group form-row">
                <label for="name" class="col-md-3 col-form-label text-md-right">項目セット名 <span class="badge badge-danger">必須</span></label>
                <div class="col-md-9">
                    <input type="text" name="name" id="name" value="{{old('name', $columns_set->name)}}" class="form-control @if ($errors->has('name')) border-danger @endif">
                    @include('plugins.common.errors_inline', ['name' => 'name'])
                </div>
            </div>

            <div class="form-group form-row">
                <label for="display_sequence" class="col-md-3 col-form-label text-md-right">表示順</label>
                <div class="col-md-9">
                    <input type="text" name="display_sequence" id="display_sequence" value="{{old('display_sequence', $columns_set->display_sequence)}}" class="form-control @if ($errors->has('display_sequence')) border-danger @endif">
                    @include('plugins.common.errors_inline', ['name' => 'display_sequence'])
                    <small class="text-muted">※ 未指定時は最後に表示されるように自動登録します。</small>
                </div>
            </div>

            <!-- Add or Update code Button -->
            <div class="form-group text-center">
                <div class="form-row">
                    <div class="offset-xl-3 col-9 col-xl-6">
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/manage/user/columnSets'"><i class="fas fa-times"></i> キャンセル</button>
                        @if ($columns_set->id)
                            <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="submitAction('{{url('/')}}/manage/user/updateColumnSet/{{$columns_set->id}}')">
                                <i class="fas fa-check"></i> 変更確定
                            </button>
                        @else
                            <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="submitAction('{{url('/')}}/manage/user/storeColumnSet')">
                                <i class="fas fa-check"></i> 登録確定
                            </button>
                        @endif
                    </div>

                    @if ($columns_set->id && $columns_set->id != 1)
                        <div class="col-3 col-xl-3 text-right">
                            <a data-toggle="collapse" href="#collapse{{$columns_set->id}}">
                                <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> 削除</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </form>

        <div id="collapse{{$columns_set->id}}" class="collapse">
            <div class="card border-danger">
                <div class="card-body">
                    <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

                    <div class="text-center">
                        {{-- 削除ボタン --}}
                        <form action="{{url('/')}}/manage/user/destroyColumnSet/{{$columns_set->id}}" method="POST">
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
