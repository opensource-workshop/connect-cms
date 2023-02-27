{{--
 * 項目の追加行テンプレート（２行）
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
--}}
<tr>
    {{-- 余白 --}}
    <td></td>
    {{-- 項目名 --}}
    <td colspan="6">
        {{-- WYSIWYG 呼び出し --}}
        @include('plugins.common.wysiwyg', ['target_class' => 'wysiwyg' . $frame->id, 'use_br' => true])
        <textarea name="column_name" class="wysiwyg{{$frame->id}} @if ($errors && $errors->has('column_name')) border-danger @endif" >{{ old('column_name')}}</textarea>
    </td>
</tr>
<tr>
    {{-- 余白 --}}
    <td></td>
    {{-- 型 --}}
    <td>
        <select class="form-control" name="column_type">
            <option value="" disabled>型を指定</option>
            @foreach (FormColumnType::getMembers() as $key=>$value)
                <option value="{{$key}}"
                    {{-- validation用 --}}
                    @if($key == old('column_type'))
                        selected="selected"
                    @endif
                >{{ $value }}</option>
            @endforeach
        </select>
    </td>
    {{-- 必須 --}}
    <td class="align-middle text-center">
        <input type="checkbox" name="required" value="1" data-toggle="tooltip" title="必須項目として指定します。" @if (old("required") == Required::on) checked="checked" @endif>
    </td>
    {{-- 余白 --}}
    <td></td>
    {{-- 余白 --}}
    <td></td>
    {{-- ＋ボタン --}}
    <td class="text-center">
        <button class="btn btn-primary cc-font-90 text-nowrap" onclick="javascript:submit_add_column();" id="button_submit_add_column"><i class="fas fa-plus"></i> <span class="d-sm-none">追加</span></button>
    </td>
    {{-- 余白 --}}
    <td></td>
</tr>
