{{--
 * カテゴリテンプレート
 *
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Linklistsプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.linklists.linklists_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- ワーニングメッセージ --}}
@if (empty($linklist) || empty($linklist->id))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i> {{ __('messages.empty_bucket_setting', ['plugin_name' => 'リンクリスト']) }}
    </div>
@else
{{-- 最後にendif --}}

{{-- エラーメッセージ --}}
@include('common.category_errors')

{{-- 削除ボタンのアクション --}}
<script type="text/javascript">
    function form_delete(id) {
        if (confirm('カテゴリを削除します。\nよろしいですか？')) {
            form_delete_category.action = "{{url('/')}}/redirect/plugin/linklists/deleteCategories/{{$page->id}}/{{$frame_id}}/" + id + "#frame-{{$frame->id}}";
            form_delete_category.submit();
        }
    }
</script>
<form action="" method="POST" name="form_delete_category">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/linklists/listCategories/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
</form>

<form action="{{url('/')}}/redirect/plugin/linklists/saveCategories/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/linklists/listCategories/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">

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
                    <input type="hidden" value="{{$category->id}}" name="general_categories_id[{{$category->id}}]">

                    <div class="custom-control custom-checkbox">
                        {{-- チェック外した場合にも値を飛ばす対応 --}}
                        <input type="hidden" value="0" name="general_view_flag[{{$category->id}}]">

                        <input type="checkbox" value="1" name="general_view_flag[{{$category->id}}]" class="custom-control-input" id="general_view_flag[{{$category->id}}]"@if (old('general_view_flag.'.$category->id, $category->view_flag)) checked="checked"@endif>
                        <label class="custom-control-label" for="general_view_flag[{{$category->id}}]"></label>
                    </div>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('general_display_sequence.'.$category->id, $category->general_display_sequence)}}" name="general_display_sequence[{{$category->id}}]" class="form-control @if ($errors && $errors->has('general_display_sequence.'.$category->id)) border-danger @endif">
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
                    <input type="hidden" value="{{$category->id}}" name="plugin_categories_id[{{$category->id}}]">

                    <div class="custom-control custom-checkbox">
                        {{-- チェック外した場合にも値を飛ばす対応 --}}
                        <input type="hidden" value="0" name="plugin_view_flag[{{$category->id}}]">

                        <input type="checkbox" value="1" name="plugin_view_flag[{{$category->id}}]" class="custom-control-input" id="plugin_view_flag[{{$category->id}}]"@if (old('plugin_view_flag.'.$category->id, $category->view_flag)) checked="checked"@endif>
                        <label class="custom-control-label" for="plugin_view_flag[{{$category->id}}]"></label>
                    </div>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_display_sequence.'.$category->id, $category->plugin_display_sequence)}}" name="plugin_display_sequence[{{$category->id}}]" class="form-control @if ($errors && $errors->has('plugin_display_sequence.'.$category->id)) border-danger @endif">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_classname.'.$category->id, $category->classname)}}" name="plugin_classname[{{$category->id}}]" class="form-control">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_category.'.$category->id, $category->category)}}" name="plugin_category[{{$category->id}}]" class="form-control @if ($errors && $errors->has('plugin_category.'.$category->id)) border-danger @endif">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_color.'.$category->id, $category->color)}}" name="plugin_color[{{$category->id}}]" class="form-control @if ($errors && $errors->has('plugin_color.'.$category->id)) border-danger @endif">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_background_color.'.$category->id, $category->background_color)}}" name="plugin_background_color[{{$category->id}}]" class="form-control @if ($errors && $errors->has('plugin_background_color.'.$category->id)) border-danger @endif">
                </td>
                <td nowrap>
                    <a href="javascript:form_delete('{{$category->id}}');"><span class="btn btn-danger"><i class="fas fa-trash-alt"></i></span></a>
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
    </div>

    @include('plugins.common.description_plugin_category')

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更</button>
    </div>
</form>

@endif
@endsection
