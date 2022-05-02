{{--
 * 確認画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
<script type="text/javascript">
    {{-- 保存のsubmit JavaScript --}}
    function submit_forms_store() {
        forms_store{{$frame_id}}.action = "{{URL::to('/')}}/plugin/forms/publicStore/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        forms_store{{$frame_id}}.submit();
    }
    {{-- 保存のキャンセル JavaScript --}}
    function submit_forms_cancel() {
        forms_store{{$frame_id}}.action = "{{URL::to('/')}}/plugin/forms/index/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        forms_store{{$frame_id}}.submit();
    }
</script>

<div class="alert alert-secondary" role="alert">
    <i class="fas fa-exclamation-circle"></i> 以下の内容でよろしいですか？
</div>

<form action="" name="forms_store{{$frame_id}}" method="POST">
    {{ csrf_field() }}
    @foreach($forms_columns as $form_column)
    <div class="form-group container-fluid row">

        {{-- ラベル --}}
        @if (isset($is_template_label_sm_4))
            {{-- label-sm-4テンプレート --}}
            <label class="col-sm-4 control-label">{{$form_column->column_name}}</label>

        @elseif (isset($is_template_label_sm_6))
            {{-- label-sm-6テンプレート --}}
            <label class="col-sm-6 control-label">{{$form_column->column_name}}</label>

        @else
            {{-- defaultテンプレート --}}
            <label class="col-sm-2 control-label">{{$form_column->column_name}}</label>
        @endif

        {{-- 項目 --}}
        <div class="col-sm">

        @switch($form_column->column_type)

        @case(FormColumnType::group)
            <div class="form-inline">
                @foreach($form_column->group as $group_row)
                    <label class="control-label" style="vertical-align: top; margin-right: 10px;@if (!$loop->first) margin-left: 30px;@endif">{{$group_row->column_name}}</label>
                    {{-- bugfix: グループ行が各カラムタイプを考慮してなかったため対応 --}}
                    @include('plugins.user.forms.default.forms_confirm_column_' . $group_row->column_type, ['form_obj' => $group_row])
                @endforeach
            </div>
            @break
        @default
            @include('plugins.user.forms.default.forms_confirm_column_' . $form_column->column_type, ['form_obj' => $form_column])
        @endswitch
        </div>
    </div>
    @endforeach
    {{-- ボタンエリア --}}
    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="submit_forms_cancel();"><i class="fas fa-times"></i> {{__('messages.cancel')}}</button>
        @if ($form->use_temporary_regist_mail_flag)
            <button type="submit" class="btn btn-info" onclick="submit_forms_store();"><i class="fas fa-check"></i> {{__('messages.temporary_regist')}}</button>
        @else
            <button type="submit" class="btn btn-primary" onclick="submit_forms_store();"><i class="fas fa-check"></i> {{__('messages.submit')}}</button>
        @endif
    </div>
</form>
@endsection
