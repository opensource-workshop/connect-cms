{{--
 * カテゴリテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Blogプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.blogs.blogs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- エラーメッセージ --}}
@if (empty($blog_frame) || empty($blog_frame->blogs_id))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        表示するコンテンツを選択するか、新規作成してください。
    </div>
@else
{{-- 最後にendif --}}

{{-- エラーメッセージ --}}
@if ($errors->count() > 0)
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
            form_delete_category.action = "{{url('/')}}/redirect/plugin/blogs/deleteCategories/{{$page->id}}/{{$frame_id}}/" + id + "#frame-{{$frame->id}}";
            form_delete_category.submit();
        }
    }
</script>
<form action="" method="POST" name="form_delete_category">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/blogs/listCategories/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
</form>

<form action="{{url('/')}}/redirect/plugin/blogs/saveCategories/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/blogs/listCategories/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">

    <div class="form-group table-responsive">
        <table class="table table-hover table-sm mb-0">
        <thead>
            <tr>
                <th nowrap colspan="7"><h5 class="mb-0"><span class="badge badge-secondary">共通カテゴリ</span></h5></th>
            </tr>
            <tr>
                <th nowrap>表示</th>
                <th nowrap>表示順 <span class="badge badge-danger">必須</span></th>
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
                <td nowrap class="align-middle text-center">
                    <input type="hidden" value="{{$category->categories_id}}" name="general_categories_id[{{$category->categories_id}}]">
                    <input type="hidden" value="{{$category->blogs_categories_id}}" name="general_blogs_categories_id[{{$category->categories_id}}]">

                    <div class="custom-control custom-checkbox">
                        {{-- チェック外した場合にも値を飛ばす対応 --}}
                        <input type="hidden" value="0" name="general_view_flag[{{$category->categories_id}}]">

                        <input type="checkbox" value="1" name="general_view_flag[{{$category->categories_id}}]" class="custom-control-input" id="general_view_flag[{{$category->categories_id}}]"@if (old('general_view_flag.'.$category->categories_id, $category->view_flag) == 1) checked="checked"@endif>
                        <label class="custom-control-label" for="general_view_flag[{{$category->categories_id}}]"></label>
                    </div>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('general_display_sequence.'.$category->categories_id, $category->blogs_categories_display_sequence)}}" name="general_display_sequence[{{$category->categories_id}}]" class="form-control @if ($errors && $errors->has('general_display_sequence.'.$category->categories_id)) border-danger @endif">
                </td>
                <td nowrap class="align-middle">{{$category->classname}}</td>
                <td nowrap class="align-middle">{{$category->category}}</td>
                <td nowrap class="align-middle">{{$category->color}}</td>
                <td nowrap class="align-middle">{{$category->background_color}}</td>
                <td nowrap></td>
            </tr>
        @endforeach

            <tr>
                <th nowrap colspan="7"><h5 class="mb-0"><span class="badge badge-secondary">個別カテゴリ</span></h5></th>
            </tr>
            <tr>
                <th nowrap>表示</th>
                <th nowrap>表示順 <span class="badge badge-danger">必須</span></th>
                <th nowrap>クラス名</th>
                <th nowrap>カテゴリ <span class="badge badge-danger">必須</span></th>
                <th nowrap>文字色 <span class="badge badge-danger">必須</span></th>
                <th nowrap>背景色 <span class="badge badge-danger">必須</span></th>
                <th nowrap class="text-center"><i class="fas fa-trash-alt"></i></th>
            </tr>

        @if ($plugin_categories)
        @foreach($plugin_categories as $category)
            <tr>
                <td nowrap class="align-middle text-center">
                    <input type="hidden" value="{{$category->categories_id}}" name="plugin_categories_id[{{$category->categories_id}}]">
                    <input type="hidden" value="{{$category->blogs_categories_id}}" name="plugin_blogs_categories_id[{{$category->categories_id}}]">

                    <div class="custom-control custom-checkbox">
                        {{-- チェック外した場合にも値を飛ばす対応 --}}
                        <input type="hidden" value="0" name="plugin_view_flag[{{$category->categories_id}}]">

                        <input type="checkbox" value="1" name="plugin_view_flag[{{$category->categories_id}}]" class="custom-control-input" id="plugin_view_flag[{{$category->categories_id}}]"@if (old('plugin_view_flag.'.$category->categories_id, $category->view_flag)) checked="checked"@endif>
                        <label class="custom-control-label" for="plugin_view_flag[{{$category->categories_id}}]"></label>
                    </div>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_display_sequence.'.$category->categories_id, $category->blogs_categories_display_sequence)}}" name="plugin_display_sequence[{{$category->categories_id}}]" class="form-control @if ($errors && $errors->has('plugin_display_sequence.'.$category->categories_id)) border-danger @endif">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_classname.'.$category->categories_id, $category->classname)}}" name="plugin_classname[{{$category->categories_id}}]" class="form-control">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_category.'.$category->categories_id, $category->category)}}" name="plugin_category[{{$category->categories_id}}]" class="form-control @if ($errors && $errors->has('plugin_category.'.$category->categories_id)) border-danger @endif">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_color.'.$category->categories_id, $category->color)}}" name="plugin_color[{{$category->categories_id}}]" class="form-control @if ($errors && $errors->has('plugin_color.'.$category->categories_id)) border-danger @endif">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_background_color.'.$category->categories_id, $category->background_color)}}" name="plugin_background_color[{{$category->categories_id}}]" class="form-control @if ($errors && $errors->has('plugin_background_color.'.$category->categories_id)) border-danger @endif">
                </td>
                <td nowrap>
                    <a href="javascript:form_delete('{{$category->categories_id}}');"><span class="btn btn-danger"><i class="fas fa-trash-alt"></i></span></a>
                </td>
            </tr>
        @endforeach
        @endif

            <tr>
                <td nowrap class="align-middle text-center">
                    <div class="custom-control custom-checkbox">
                        {{-- チェック外した場合にも値を飛ばす対応 --}}
                        <input type="hidden" value="0" name="add_view_flag">

                        <input type="checkbox" value="1" name="add_view_flag" class="custom-control-input" id="add_view_flag"@if (old('add_view_flag')) checked="checked"@endif>
                        <label class="custom-control-label" for="add_view_flag"></label>
                    </div>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('add_display_sequence')}}" name="add_display_sequence" class="form-control @if ($errors && $errors->has('add_display_sequence')) border-danger @endif">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('add_classname')}}" name="add_classname" class="form-control">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('add_category')}}" name="add_category" class="form-control @if ($errors && $errors->has('add_category')) border-danger @endif">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('add_color')}}" name="add_color" class="form-control @if ($errors && $errors->has('add_color')) border-danger @endif" placeholder="(例)#000000">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('add_background_color')}}" name="add_background_color" class="form-control @if ($errors && $errors->has('add_background_color')) border-danger @endif" placeholder="(例)#ffffff">
                </td>
                <td nowrap>
                </td>
            </tr>
        </tbody>
        </table>

        @include('plugins.common.description_plugin_category')
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更</button>
    </div>
</form>

@endif
@endsection
