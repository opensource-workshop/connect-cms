{{--
 * 項目設定画面のメインテンプレート
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設管理
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<script type="text/javascript">
    /**
     * 予約項目の追加
     */
    function submit_add_column(btn) {
        form_columns.action = "{{url('/')}}/manage/reservation/addColumn";
        btn.disabled = true;
        form_columns.submit();
    }

    /**
     * 予約項目の更新
     */
    function submit_update_column(column_id) {
        form_columns.action = "{{url('/')}}/manage/reservation/updateColumn";
        form_columns.column_id.value = column_id;
        form_columns.submit();
    }

    /**
     * 予約項目の表示順操作
     */
    function submit_display_sequence(column_id, display_sequence, display_sequence_operation) {
        form_columns.action = "{{url('/')}}/manage/reservation/updateColumnSequence";
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
            form_columns.action = "{{url('/')}}/manage/reservation/deleteColumn";
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
        @include('plugins.manage.reservation.reservation_manage_tab')
    </div>
    <div class="card-body">

        {{-- 一覧フォーム --}}
        <form action="{{url('/')}}/manage/reservation/addColumn" id="form_columns" name="form_columns" method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="columns_set_id" value="{{$columns_set->id}}">
            <input type="hidden" name="column_id" value="">
            <input type="hidden" name="display_sequence" value="">
            <input type="hidden" name="display_sequence_operation" value="">

            {{-- 登録後メッセージ表示 --}}
            @include('plugins.common.flash_message')

            {{-- メッセージエリア --}}
            <div class="alert alert-info">
                <i class="fas fa-exclamation-circle"></i> 予約項目セット【 {{$columns_set->name}} 】の項目を追加・変更します。<br />
                　予約項目セットは施設の予約登録時に使います。
            </div>

            {{-- ワーニングメッセージエリア --}}
            @if (! $title_flag)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle"></i> 新着情報等でタイトル表示する項目が未設定です。いずれかの項目の「詳細」よりタイトル設定をしてください。
                </div>
            @endif

            {{-- エラーメッセージエリア --}}
            @if ($errors && $errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <i class="fas fa-exclamation-circle"></i> {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <div class="table-responsive">

                {{-- 予約項目の一覧 --}}
                <table class="table table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            @if (count($columns) > 0)
                                <th class="text-center" nowrap>表示順</th>
                                <th class="text-center" nowrap>項目名</th>
                                <th class="text-center" nowrap>型</th>
                                <th class="text-center" nowrap>必須 <span class="fas fa-info-circle" data-toggle="tooltip" title="必須項目として指定します。"></th>
                                <th class="text-center" nowrap>非表示 <span class="fas fa-info-circle" data-toggle="tooltip" title="チェックした項目を非表示にします。"></th>
                                <th class="text-center" nowrap>詳細</th>
                                <th class="text-center" nowrap>更新</th>
                                <th class="text-center" nowrap>削除</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        {{-- 更新用の行 --}}
                        @foreach($columns as $column)
                            @include('plugins.manage.reservation.include_edit_column_row')
                        @endforeach
                        {{-- 新規登録用の行 --}}
                        <tr class="thead-light">
                            <th colspan="8">【予約項目の追加行】</th>
                        </tr>
                        @include('plugins.manage.reservation.include_edit_column_row_add')
                    </tbody>
                </table>
            </div>

            {{-- ボタンエリア --}}
            <div class="text-center">
                <a href="{{url('/')}}/manage/reservation/columnSets" class="btn btn-secondary">
                    <i class="fas fa-chevron-left"></i> 項目セット一覧へ
                </a>
            </div>
        </form>

    </div>
</div>

@endsection
