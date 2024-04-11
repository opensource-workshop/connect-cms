{{--
 * 縦並び 確認画面テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
<script type="text/javascript">
    {{-- 保存のsubmit JavaScript --}}
    function submit_forms_store() {
        forms_store{{$frame_id}}.action = "{{url('/')}}/redirect/plugin/forms/publicStore/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        forms_store{{$frame_id}}.redirect_path.value = "{{url('/')}}/plugin/forms/publicConfirm/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        forms_store{{$frame_id}}.submit();
    }
    {{-- 保存のキャンセル JavaScript --}}
    function submit_forms_cancel() {
        forms_store{{$frame_id}}.action = "{{url('/')}}/plugin/forms/index/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        forms_store{{$frame_id}}.submit();
    }
    {{-- 二重クリック防止 JavaScript --}}
    $(function () {
        $('form').submit(function () {
            $(this).find(':submit').prop('disabled', true);
        });
    });
</script>

<div class="alert alert-secondary" role="alert">
    <i class="fas fa-exclamation-circle"></i> 以下の内容でよろしいですか？
</div>

<form action="" name="forms_store{{$frame_id}}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="">

    @php $no = 1; @endphp
    @foreach($forms_columns as $form_column)
    <div class="form-group container-fluid row">

        @switch($form_column->column_type)

        @case(FormColumnType::group)
            {{-- defaultテンプレート --}}
            <label class="col-12 control-label">{!! $form_column->column_name !!}</label>

            @php
            // グループカラムの幅の計算
            $col_count = floor(12/count($form_column->group));
            if ($col_count < 3) {
                $col_count = 3;
            }
            @endphp

            {{-- 項目 --}}
            <div class="col-sm pr-0">
                <div class="row p-0">
                    @foreach($form_column->group as $group_row)
                        <div class="col-sm-{{$col_count}} pr-0">
                            <label class="control-label">Q{{$no}} {!! $group_row->column_name !!}</label><br />
                            @include('plugins.user.forms.default.forms_confirm_column_' . $group_row->column_type, ['form_obj' => $group_row])
                            @php $no++; @endphp
                        </div>
                    @endforeach
                </div>
            </div>
            @break

        @default
            <label class="col-12 control-label">Q{{$no}}  {!! $form_column->column_name !!}</label>

            {{-- 項目 --}}
            <div class="col-12">
                @include('plugins.user.forms.default.forms_confirm_column_' . $form_column->column_type, ['form_obj' => $form_column])
            </div>
            @php $no++; @endphp
        @endswitch
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
