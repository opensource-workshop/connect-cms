{{--
 * 配送希望設定画面テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.opacs.opacs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

{{-- 登録後メッセージ表示 --}}
@include('plugins.common.flash_message_for_frame')

<script type="text/javascript">
    /**
     * 選択肢の追加ボタン押下
     */
    function submit_add_select(btn) {
        opac_delivery_request.action = "{{url('/')}}/redirect/plugin/opacs/addSelect/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        btn.disabled = true;
        opac_delivery_request.submit();
    }

    /**
     * 選択肢の表示順操作ボタン押下
     */
    function submit_display_sequence(select_id, display_sequence, display_sequence_operation) {
        opac_delivery_request.action = "{{url('/')}}/redirect/plugin/opacs/updateSelectSequence/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        opac_delivery_request.select_id.value = select_id;
        opac_delivery_request.display_sequence.value = display_sequence;
        opac_delivery_request.display_sequence_operation.value = display_sequence_operation;
        opac_delivery_request.submit();
    }

    /**
     * 選択肢の更新ボタン押下
     */
    function submit_update_select(select_id) {
        opac_delivery_request.action = "{{url('/')}}/redirect/plugin/opacs/updateSelect/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        opac_delivery_request.select_id.value = select_id;
        opac_delivery_request.submit();
    }

    /**
     * 選択肢の削除ボタン押下
     */
    function submit_delete_select(select_id) {
        if(confirm('選択肢を削除します。\nよろしいですか？')){
            opac_delivery_request.action = "{{url('/')}}/redirect/plugin/opacs/deleteSelect/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
            opac_delivery_request.select_id.value = select_id;
            opac_delivery_request.submit();
        }
        return false;
    }

    /**
     * その他の設定の更新ボタン押下
     */
    function submit_save_delivery_request() {
        opac_delivery_request.action = "{{url('/')}}/redirect/plugin/opacs/saveDeliveryRequest/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        opac_delivery_request.submit();
    }
</script>

<form action="" id="opac_delivery_request" name="opac_delivery_request" method="POST" class="form-horizontal">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/opacs/editDeliveryRequest/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
    <input type="hidden" name="opacs_id" value="{{ $opac->id }}">
    <input type="hidden" name="select_id" value="">
    <input type="hidden" name="display_sequence" value="">
    <input type="hidden" name="display_sequence_operation" value="">

    {{-- メッセージエリア --}}
    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i> {{ 'OPAC名【' . $opac->opac_name . '】の配送希望設定を行います。' }}
    </div>

    {{-- チェック処理の設定 --}}
    <div class="card form-group">
        <h5 class="card-header">配送希望日：チェック処理の設定</h5>
        <div class="card-body">

            {{-- 指定日以降（指定日含む）の入力を許容 --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">指定日数以降を許容（From）</label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input type="text" name="opacs_configs[rule_date_after_equal]" value="{{old('opacs_configs.rule_date_after_equal', $opacs_configs['rule_date_after_equal'])}}" class="form-control @if ($errors->has("opacs_configs.rule_date_after_equal")) border-danger @endif">
                    @include('plugins.common.errors_inline', ['name' => 'opacs_configs.rule_date_after_equal'])
                    <small class="text-muted">
                        ※ 整数（･･･,-1,0,1,･･･）で入力します。<br />
                        ※ 当日を含みます。<br />
                        　(例) 設定値「2」で 2020/2/16 に入力した場合、「2020/2/18」以降の日付を入力できます。
                    </small>
                </div>
            </div>

            {{-- 指定日まで（指定日含む）の入力を許容 --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">指定日数までを許容（To）</label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input type="text" name="opacs_configs[rule_date_before_equal]" value="{{old('opacs_configs.rule_date_before_equal', $opacs_configs['rule_date_before_equal'])}}" class="form-control @if ($errors->has("opacs_configs.rule_date_before_equal")) border-danger @endif">
                    @include('plugins.common.errors_inline', ['name' => 'opacs_configs.rule_date_before_equal'])
                    <small class="text-muted">
                        ※ 整数（･･･,-1,0,1,･･･）で入力します。<br />
                        ※ 当日を含みます。<br />
                        　(例)&nbsp;設定値「7」で 2020/2/16 に入力の場合、「2020/2/23」までの日付を入力できます。
                    </small>
                </div>
            </div>

            {{-- ボタンエリア --}}
            <div class="text-center">
                <button onclick="javascript:submit_save_delivery_request();" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>
        </div>
    </div>

    {{-- キャプション設定 --}}
    <div class="card form-group">
        <h5 class="card-header">配送希望日：キャプション設定</h5>
        <div class="card-body">

            {{-- キャプション内容 --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">内容</label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <textarea name="opacs_configs[delivery_request_date_caption]" class="form-control @if ($errors->has("opacs_configs.delivery_request_date_caption")) border-danger @endif" rows="3">{{old('opacs_configs.delivery_request_date_caption', $opacs_configs['delivery_request_date_caption'])}}</textarea>
                    @include('plugins.common.errors_inline', ['name' => 'opacs_configs.delivery_request_date_caption'])
                </div>
            </div>

            {{-- ボタンエリア --}}
            <div class="text-center">
                <button onclick="javascript:submit_save_delivery_request();" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>
        </div>
    </div>

    {{-- 選択肢の設定 --}}
    <div class="card form-group">
        <h5 class="card-header">配送希望時間：選択肢の設定</h5>
        <div class="card-body">
            {{-- エラーメッセージエリア --}}
            @if ($errors && $errors->has('select_name'))
                <div class="alert alert-danger mt-2">
                    <i class="fas fa-exclamation-triangle"></i> {{ $errors->first('select_name') }}
                </div>
            @endif

            <div class="table-responsive">

                {{-- 選択項目の一覧 --}}
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            @if (count($opacs_configs_selects) > 0)
                                <th class="text-center" nowrap>表示順</th>
                                <th class="text-center" nowrap>選択肢名</th>
                                <th class="text-center" nowrap>更新</th>
                                <th class="text-center" nowrap>削除</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        {{-- 更新用の行 --}}
                        @foreach($opacs_configs_selects as $select)
                            <tr>
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
                                    <input class="form-control" type="text" name="select_name_{{ $select->id }}" value="{{ old('select_name_'.$select->id, $select->value)}}">
                                </td>

                                {{-- 更新ボタン --}}
                                <td class="align-middle text-center">
                                    <button
                                        class="btn btn-primary cc-font-90 text-nowrap"
                                        onclick="javascript:submit_update_select({{ $select->id }});"
                                    >
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                                {{-- 削除ボタン --}}
                                <td class="text-center">
                                    <button
                                        class="btn btn-danger cc-font-90 text-nowrap"
                                        onclick="javascript:return submit_delete_select({{ $select->id }});"
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        <tr class="thead-light">
                            <th colspan="7">【選択肢の追加行】</th>
                        </tr>

                        {{-- 新規登録用の行 --}}
                        <tr>
                            <td>{{-- 余白 --}}</td>
                            <td>
                                {{-- 選択肢名 --}}
                                <input class="form-control" type="text" name="select_name" value="{{ old('select_name') }}" placeholder="選択肢名">
                                <small class="text-muted">
                                    ※ 選択肢が１つの場合、選択状態で初期表示します。<br>
                                    ※ 選択肢名に | を含める事はできません<br>
                                </small>
                            </td>
                            <td class="text-center">
                                {{-- ＋ボタン --}}
                                <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_add_select(this);" id="button_add_select"><i class="fas fa-plus"></i></button>
                            </td>
                            <td>{{-- 余白 --}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ボタンエリア --}}
    <div class="text-center">
        <a href="{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}" class="btn btn-secondary">
            <i class="fas fa-times"></i> キャンセル
        </a>
    </div>
</form>
@endsection
