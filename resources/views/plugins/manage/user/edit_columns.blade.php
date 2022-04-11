{{--
 * 項目設定画面のメインテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ管理
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<script type="text/javascript">
    /**
     * 項目の追加
     */
    function submit_add_column(btn) {
        form_columns.action = "{{url('/')}}/manage/user/addColumn";
        btn.disabled = true;
        form_columns.submit();
    }

    /**
     * 項目の更新
     */
    function submit_update_column(column_id) {
        form_columns.action = "{{url('/')}}/manage/user/updateColumn";
        form_columns.column_id.value = column_id;
        form_columns.submit();
    }

    /**
     * 項目の表示順操作
     */
    function submit_display_sequence(column_id, display_sequence, display_sequence_operation) {
        form_columns.action = "{{url('/')}}/manage/user/updateColumnSequence";
        form_columns.column_id.value = column_id;
        form_columns.display_sequence.value = display_sequence;
        form_columns.display_sequence_operation.value = display_sequence_operation;
        form_columns.submit();
    }

    /**
     * 項目の削除ボタン押下
     */
     function submit_delete_column(column_id) {
        if (confirm('項目を削除します。\nよろしいですか？')){
            form_columns.action = "{{url('/')}}/manage/user/deleteColumn";
            form_columns.column_id.value = column_id;
            form_columns.submit();
        }
        return false;
    }

    // ツールチップ
    $(function () {
        // 有効化
        $('[data-toggle="tooltip"]').tooltip()
        // 常時表示 ※表示の判定は項目側で実施
        $('[id^=detail-button-tip]').tooltip('show');
    })
</script>

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.user.user_manage_tab')
    </div>
    <div class="card-body">

        {{-- 一覧フォーム --}}
        <form action="{{url('/')}}/manage/user/addColumn" id="form_columns" name="form_columns" method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="column_id" value="">
            <input type="hidden" name="display_sequence" value="">
            <input type="hidden" name="display_sequence_operation" value="">

            {{-- 登録後メッセージ表示 --}}
            @include('plugins.common.flash_message')

            {{-- メッセージエリア --}}
            <div class="alert alert-info">
                <i class="fas fa-exclamation-circle"></i> ユーザ項目を追加・変更します。
            </div>

            {{-- エラーメッセージエリア --}}
            @if ($errors && $errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <i class="fas fa-exclamation-circle"></i> {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <div class="table-responsive">

                {{-- 項目の一覧 --}}
                <table class="table table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            @if (count($columns) > 0)
                                <th class="text-center" nowrap>表示順</th>
                                <th class="text-center" style="min-width: 150px" nowrap>項目名</th>
                                <th class="text-center" nowrap>型</th>
                                <th class="text-center" nowrap>必須 <span class="fas fa-info-circle" data-toggle="tooltip" title="必須項目として指定します。"></th>
                                <th class="text-center" nowrap>詳細</th>
                                <th class="text-center" nowrap>更新</th>
                                <th class="text-center" nowrap>削除</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        {{-- 更新用の行 --}}
                        @foreach($columns as $column)
                            @include('plugins.manage.user.include_edit_column_row')
                        @endforeach
                        {{-- 新規登録用の行 --}}
                        <tr class="thead-light">
                            <th colspan="7">【項目の追加行】</th>
                        </tr>
                        @include('plugins.manage.user.include_edit_column_row_add')
                    </tbody>
                </table>
            </div>

            {{-- ボタンエリア --}}
            <div class="text-center">
                <a href="{{url('/')}}/manage/user/editColumns" class="btn btn-secondary">
                    <i class="fas fa-times"></i> キャンセル
                </a>
            </div>
        </form>

    </div>
</div>

@endsection
