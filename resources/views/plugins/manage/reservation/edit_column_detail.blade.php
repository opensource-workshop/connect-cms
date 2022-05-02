{{--
 * 予約項目の選択肢設定画面
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
     * 選択肢追加ボタン押下
     */
    function submit_add_select(btn) {
        form_selects.action = "{{url('/')}}/manage/reservation/addSelect";
        btn.disabled = true;
        form_selects.submit();
    }

    /**
     * 表示順操作ボタン押下
     */
    function submit_display_sequence(select_id, display_sequence, display_sequence_operation) {
        form_selects.action = "{{url('/')}}/manage/reservation/updateSelectSequence";
        form_selects.select_id.value = select_id;
        form_selects.display_sequence.value = display_sequence;
        form_selects.display_sequence_operation.value = display_sequence_operation;
        form_selects.submit();
    }

    /**
     * 選択肢の更新ボタン押下
     */
    function submit_update_select(select_id) {
        form_selects.action = "{{url('/')}}/manage/reservation/updateSelect";
        form_selects.select_id.value = select_id;
        form_selects.submit();
    }

    /**
     * その他の設定の更新ボタン押下
     */
    function submit_update_column_detail() {
        form_selects.action = "{{url('/')}}/manage/reservation/updateColumnDetail";
        form_selects.submit();
    }

    /**
     * 選択肢の削除ボタン押下
     */
     function submit_delete_select(select_id) {
        if (confirm('選択肢を削除します。\nよろしいですか？')){
            form_selects.action = "{{url('/')}}/manage/reservation/deleteSelect";
            form_selects.select_id.value = select_id;
            form_selects.submit();
        }
        return false;
    }

    // ツールチップ
    $(function () {
        // 有効化
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.reservation.reservation_manage_tab')
    </div>
    <div class="card-body">

        <form action="" id="form_selects" name="form_selects" method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="columns_set_id" value="{{$columns_set->id}}">
            <input type="hidden" name="column_id" value="{{ $column->id }}">
            <input type="hidden" name="select_id" value="">
            <input type="hidden" name="display_sequence" value="">
            <input type="hidden" name="display_sequence_operation" value="">

            {{-- 登録後メッセージ表示 --}}
            @include('plugins.common.flash_message')

            {{-- メッセージエリア --}}
            <div class="alert alert-info mt-2">
                <i class="fas fa-exclamation-circle"></i> {{ '予約項目【 ' . $column->column_name . ' 】の詳細設定を行います。' }}
            </div>

            {{-- エラーメッセージエリア --}}
            @if ($errors && $errors->any())
                <div class="alert alert-danger mt-2">
                    @foreach ($errors->all() as $error)
                        <i class="fas fa-exclamation-circle"></i> {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            {{-- タイトル設定 --}}
            <div class="card mb-4">
                <h5 class="card-header">タイトルの設定</h5>
                <div class="card-body">
                    {{-- タイトル指定 --}}
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label text-md-right pt-0">タイトル指定 </label>
                        <div class="col-md-9 align-items-center">
                            <div class="custom-control custom-checkbox">
                                <input name="title_flag" value="1" type="checkbox" class="custom-control-input" id="list_detail_hide_role_article"
                                            @if(old('title_flag', $column->title_flag)) checked @endif >

                                <label class="custom-control-label" for="list_detail_hide_role_article">新着情報等のタイトルに指定する</label>
                            </div>
                            <small class="text-muted">
                                ※ タイトル指定できる項目は、施設予約毎に１つです。既に他項目でタイトル指定している場合、自動的に解除されます。<br>
                            </small>
                        </div>
                    </div>

                    {{-- ボタンエリア --}}
                    <div class="form-group text-center">
                        <button onclick="javascript:submit_update_column_detail();" class="btn btn-primary database-horizontal">
                            <i class="fas fa-check"></i> 更新
                        </button>
                    </div>
                </div>
            </div>

            @if ($column->column_type == ReservationColumnType::radio)
                {{-- 選択肢の設定 --}}
                <div class="card mb-4">
                    <h5 class="card-header">選択肢の設定</h5>
                    <div class="card-body">

                        <div class="table-responsive">

                            {{-- 選択項目の一覧 --}}
                            <table class="table table-hover table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        @if (count($selects) > 0)
                                            <th class="text-center" nowrap>表示順</th>
                                            <th class="text-center" nowrap>選択肢名</th>
                                            <th class="text-center" nowrap>非表示 <span class="fas fa-info-circle" data-toggle="tooltip" title="チェックした選択肢を非表示にします。"></th>
                                            <th class="text-center" nowrap>更新</th>
                                            <th class="text-center" nowrap>削除</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- 更新用の行 --}}
                                    @foreach($selects as $select)
                                        <tr  @if ($select->hide_flag) class="table-secondary" @endif>
                                            {{-- 表示順操作 --}}
                                            <td class="text-center" nowrap>
                                                {{-- 上移動 --}}
                                                <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->first) disabled @endif onclick="javascript:submit_display_sequence({{ $select->id }}, {{ $select->display_sequence }}, 'up')">
                                                    <i class="fas fa-arrow-up"></i>
                                                </button>

                                                {{-- 下移動 --}}
                                                <button type="button" class="btn btn-default btn-xs p-1" @if ($loop->last) disabled @endif onclick="javascript:submit_display_sequence({{ $select->id }}, {{ $select->display_sequence }}, 'down')">
                                                    <i class="fas fa-arrow-down"></i>
                                                </button>
                                            </td>

                                            {{-- 選択肢名 --}}
                                            <td>
                                                <input class="form-control @if ($errors && $errors->has('select_name_'.$select->id)) border-danger @endif" type="text" name="select_name_{{ $select->id }}" value="{{ old('select_name_'.$select->id, $select->select_name)}}">
                                            </td>

                                            {{-- 非表示フラグ --}}
                                            <td class="align-middle text-center">
                                                <input name="hide_flag_{{ $select->id }}" id="hide_flag_{{ $select->id }}" value="1" type="checkbox" @if (old('hide_flag_'.$select->id, $select->hide_flag)) checked="checked" @endif>
                                            </td>

                                            {{-- 更新ボタン --}}
                                            <td class="align-middle text-center">
                                                <button
                                                    class="btn btn-primary cc-font-90 text-nowrap"
                                                    onclick="javascript:submit_update_select({{ $select->id }});"
                                                >
                                                    <i class="fas fa-check"></i> <span class="d-sm-none">更新</span>
                                                </button>
                                            </td>

                                            {{-- 削除ボタン --}}
                                            <td class="text-center">
                                                <button
                                                    class="btn btn-danger cc-font-90 text-nowrap"
                                                    onclick="javascript:return submit_delete_select({{ $select->id }});"
                                                >
                                                    <i class="fas fa-trash-alt"></i> <span class="d-sm-none">削除</span>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="thead-light">
                                        <th colspan="7">【選択肢の追加行】</th>
                                    </tr>

                                    {{-- 新規登録用の行 --}}
                                    <tr>
                                        <td></td>
                                        <td>
                                            {{-- 選択肢名 --}}
                                            <input class="form-control @if ($errors && $errors->has('select_name')) border-danger @endif" type="text" name="select_name" value="{{ old('select_name') }}" placeholder="選択肢名">
                                        </td>
                                        <td class="text-center">
                                            {{-- ＋ボタン --}}
                                            <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_add_select(this);"><i class="fas fa-plus"></i> <span class="d-sm-none">追加</span></button>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            @endif

            {{-- ボタンエリア --}}
            <div class="form-group text-center">
                <a href="{{url('/')}}/manage/reservation/editColumns/{{$columns_set->id}}" class="btn btn-secondary">
                    <i class="fas fa-chevron-left"></i> 項目設定へ
                </a>
            </div>
        </form>

    </div>
</div>

@endsection
