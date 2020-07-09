{{--
 * パターンテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 開館カレンダープラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.openingcalendars.openingcalendars_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if (!$openingcalendar->id)
    <div class="alert alert-warning mt-3">
        <i class="fas fa-exclamation-circle"></i>
        設定画面から、使用する開館カレンダーを選択するか、作成してください。
    </div>
@else

{{-- エラーメッセージ --}}
@if ($errors)
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">
                @foreach($errors->all() as $error)
                <i class="fas fa-exclamation-triangle"></i> {{$error}}<br />
                @endforeach
            </span>
            <span class="text-secondary">
                @if ($errors->has('add_display_sequence') || $errors->has('add_pattern') || $errors->has('color'))
                <i class="fas fa-exclamation-circle"></i> 追加行を入力する場合は、すべての項目を入力してください。
                @endif
            </span>
        </div>
    </div>
@endif

{{-- 削除ボタンのアクション --}}
<script type="text/javascript">
    function form_delete(id) {
        if (confirm('時間設定を削除します。\nよろしいですか？')) {
            form_delete_pattern.action = "{{url('/')}}/plugin/openingcalendars/deletePatterns/{{$page->id}}/{{$frame_id}}/" + id;
            form_delete_pattern.submit();
        }
    }
</script>

<form action="" method="POST" name="form_delete_pattern" class="">
    {{ csrf_field() }}
</form>

<form action="{{url('/')}}/plugin/openingcalendars/savePatterns/{{$page->id}}/{{$frame_id}}/{{$openingcalendar->id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group table-responsive">
        <table class="table table-hover mb-sm-0" style="min-width: 500px;">
        <thead>
            <tr>
                <th nowrap>表示順</th>
                <th nowrap>予定名</th>
                <th nowrap>開館時間</th>
                <th nowrap>色</th>
                <th nowrap><i class="fas fa-trash-alt"></i></th>
            </tr>
        </thead>
        <tbody>
        @foreach($patterns as $pattern)
            <tr>
                <td nowrap>
                    <input type="hidden" value="{{$pattern->openingcalendars_patterns_id}}" name="openingcalendars_patterns_id[{{$pattern->openingcalendars_patterns_id}}]">
                    <input type="text" value="{{old('display_sequence.'.$pattern->openingcalendars_patterns_id, $pattern->display_sequence)}}" name="display_sequence[{{$pattern->openingcalendars_patterns_id}}]" class="form-control">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('caption.'.$pattern->openingcalendars_patterns_id, $pattern->caption)}}" name="caption[{{$pattern->openingcalendars_patterns_id}}]" class="form-control">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('pattern.'.$pattern->openingcalendars_patterns_id, $pattern->pattern)}}" name="pattern[{{$pattern->openingcalendars_patterns_id}}]" class="form-control">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('color.'.$pattern->openingcalendars_patterns_id, $pattern->color)}}" name="color[{{$pattern->openingcalendars_patterns_id}}]" class="form-control">
                </td>
                <td nowrap>
                    <a href="javascript:form_delete('{{$pattern->openingcalendars_patterns_id}}');"><span class="btn btn-danger"><i class="fas fa-trash-alt"></i></span></a>
                </td>
            </tr>
        @endforeach
        @if ($create_flag)
            <tr>
                <td nowrap>
                    <input type="text" value="" name="add_display_sequence" class="form-control">
                </td>
                <td nowrap>
                    <input type="text" value="" name="add_caption" class="form-control">
                </td>
                <td nowrap>
                    <input type="text" value="" name="add_pattern" class="form-control">
                </td>
                <td nowrap>
                    <input type="text" value="" name="add_color" class="form-control">
                </td>
                <td nowrap>
                </td>
            </tr>
        @else
            <tr>
                <td nowrap>
                    <input type="text" value="{{old('add_display_sequence', '')}}" name="add_display_sequence" class="form-control">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('add_caption', '')}}" name="add_caption" class="form-control">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('add_pattern', '')}}" name="add_pattern" class="form-control">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('add_color', '')}}" name="add_color" class="form-control">
                </td>
                <td nowrap>
                </td>
            </tr>
        @endif
        </tbody>
        </table>
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 変更</span></button>
    </div>
</form>

@endif
@endsection
