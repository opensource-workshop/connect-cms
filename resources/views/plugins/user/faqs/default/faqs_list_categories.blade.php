{{--
 * カテゴリテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Faqプラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.faqs.faqs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- エラーメッセージ --}}
@if (empty($faq_frame) || empty($faq_frame->faqs_id))
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        設定画面から、使用するFAQを選択するか、作成してください。
    </div>
@else
{{-- 最後にendif --}}

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
                @if ($errors->has('add_display_sequence') || $errors->has('add_classname') || $errors->has('add_category') || $errors->has('color') || $errors->has('background_color'))
                <i class="fas fa-exclamation-circle"></i> 追加行を入力する場合は、すべての項目を入力してください。
                @endif
            </span>
        </div>
    </div>
@endif

{{-- 削除ボタンのアクション --}}
<script type="text/javascript">
    function form_delete(id) {
        if (confirm('カテゴリを削除します。\nよろしいですか？')) {
            form_delete_category.action = "{{url('/')}}/plugin/faqs/deleteCategories/{{$page->id}}/{{$frame_id}}/" + id;
            form_delete_category.submit();
        }
    }
</script>

<form action="{{url('/')}}/plugin/faqs/saveCategories/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group table-responsive">
        <table class="table table-hover" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th nowrap>表示</th>
                <th nowrap>表示順</th>
                <th nowrap>クラス名</th>
                <th nowrap>カテゴリ</th>
                <th nowrap>文字色</th>
                <th nowrap>背景色</th>
                <th nowrap></th>
            </tr>
        </thead>
        <tbody>
        @foreach($general_categories as $category)
            <tr>
                <td nowrap>
                    <input type="hidden" value="{{$category->id}}" name="general_categories_id[{{$category->id}}]"></input>
                    <input type="hidden" value="{{$category->faqs_categories_id}}" name="general_faqs_categories_id[{{$category->id}}]"></input>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" value="1" name="general_view_flag[{{$category->id}}]" class="custom-control-input" id="general_view_flag[{{$category->id}}]"@if ($category->view_flag == 1) checked="checked"@endif>
                        <label class="custom-control-label" for="general_view_flag[{{$category->id}}]"></label>
                    </div>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('display_sequence.'.$category->id, $category->display_sequence)}}" name="general_display_sequence[{{$category->id}}]" class="form-control"></input>
                </td>
                <td nowrap>{{$category->classname}}</td>
                <td nowrap>{{$category->category}}</td>
                <td nowrap>{{$category->color}}</td>
                <td nowrap>{{$category->background_color}}</td>
                <td nowrap></td>
            </tr>
        @endforeach

            <tr>
                <th nowrap>表示</th>
                <th nowrap>表示順</th>
                <th nowrap>クラス名</th>
                <th nowrap>カテゴリ</th>
                <th nowrap>文字色</th>
                <th nowrap>背景色</th>
                <th nowrap><i class="fas fa-trash-alt"></i></th>
            </tr>

        @if ($plugin_categories)
        @foreach($plugin_categories as $category)
            <tr>
                <td nowrap>
                    <input type="hidden" value="{{$category->id}}" name="plugin_categories_id[{{$category->id}}]"></input>
                    <input type="hidden" value="{{$category->faqs_categories_id}}" name="plugin_faqs_categories_id[{{$category->id}}]"></input>

                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" value="1" name="plugin_view_flag[{{$category->id}}]" class="custom-control-input" id="plugin_view_flag[{{$category->id}}]"@if ($category->view_flag) checked="checked"@endif>
                        <label class="custom-control-label" for="plugin_view_flag[{{$category->id}}]"></label>
                    </div>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_display_sequence.'.$category->id, $category->display_sequence)}}" name="plugin_display_sequence[{{$category->id}}]" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_display_classname.'.$category->id, $category->classname)}}" name="plugin_classname[{{$category->id}}]" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_category.'.$category->id, $category->category)}}" name="plugin_category[{{$category->id}}]" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_color.'.$category->id, $category->color)}}" name="plugin_color[{{$category->id}}]" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_background_color.'.$category->id, $category->background_color)}}" name="plugin_background_color[{{$category->id}}]" class="form-control"></input>
                </td>
                <td nowrap>
                    <a href="javascript:form_delete('{{$category->id}}');"><span class="btn btn-danger"><i class="fas fa-trash-alt"></i></span></a>
                </td>
            </tr>
        @endforeach
        @endif
        @if ($create_flag)
            <tr>
                <td nowrap>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" value="1" name="add_view_flag" class="custom-control-input" id="add_view_flag">
                        <label class="custom-control-label" for="add_view_flag"></label>
                    </div>
                </td>
                <td nowrap>
                    <input type="text" value="" name="add_display_sequence" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="" name="add_classname" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="" name="add_category" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="" name="add_color" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="" name="add_background_color" class="form-control"></input>
                </td>
                <td nowrap>
                </td>
            </tr>
        @else
            <tr>
                <td nowrap>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" value="1" name="add_view_flag" class="custom-control-input" id="add_view_flag">
                        <label class="custom-control-label" for="add_view_flag"></label>
                    </div>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('add_display_sequence', '')}}" name="add_display_sequence" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('add_classname', '')}}" name="add_classname" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('add_category', '')}}" name="add_category" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('add_color', '')}}" name="add_color" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('add_background_color', '')}}" name="add_background_color" class="form-control"></input>
                </td>
                <td nowrap>
                </td>
            </tr>
        @endif
        </tbody>
        </table>

        <div class="card card-body bg-light p-2 m-2">
            クラス名は cc_category_xxxx で使用できます。
        </div>
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更</button>
    </div>
</form>

<form action="" method="POST" name="form_delete_category" class="">
    {{ csrf_field() }}
</form>
@endif
@endsection
