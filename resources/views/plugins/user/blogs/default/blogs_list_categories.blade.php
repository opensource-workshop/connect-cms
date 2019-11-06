{{--
 * カテゴリテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Blogプラグイン
 --}}

<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.blogs.blogs_frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

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
                @if ($errors->has('add_display_sequence') || $errors->has('add_category') || $errors->has('color') || $errors->has('background_color'))
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
            form_delete_category.action = "/plugin/blogs/deleteCategories/{{$page->id}}/{{$frame_id}}/" + id;
            form_delete_category.submit();
        }
    }
</script>

<form action="" method="POST" name="form_delete_category" class="">
    {{ csrf_field() }}
</form>

<form action="/plugin/blogs/saveCategories/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group table-responsive">
        <table class="table table-hover" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th nowrap>表示順</th>
                <th nowrap>カテゴリ</th>
                <th nowrap>文字色</th>
                <th nowrap>背景色</th>
                <th nowrap><i class="fas fa-trash-alt"></i></th>
            </tr>
        </thead>
        <tbody>
        @foreach($blogs_categories as $category)
            <tr>
                <td nowrap>
                    <input type="hidden" value="{{$category->id}}" name="blogs_categories_id[{{$category->id}}]"></input>
                    <input type="text" value="{{old('display_sequence.'.$category->id, $category->display_sequence)}}" name="display_sequence[{{$category->id}}]" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('category.'.$category->id, $category->category)}}" name="category[{{$category->id}}]" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('color.'.$category->id, $category->color)}}" name="color[{{$category->id}}]" class="form-control"></input>
                </td>
                <td nowrap>
                    <input type="text" value="{{old('background_color.'.$category->id, $category->background_color)}}" name="background_color[{{$category->id}}]" class="form-control"></input>
                </td>
                <td nowrap>
                    <a href="javascript:form_delete('{{$category->id}}');"><span class="btn btn-danger"><i class="fas fa-trash-alt"></i></span></a>
                </td>
            </tr>
        @endforeach
        @if ($create_flag)
            <tr>
                <td nowrap>
                    <input type="text" value="" name="add_display_sequence" class="form-control"></input>
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
                    <input type="text" value="{{old('add_display_sequence', '')}}" name="add_display_sequence" class="form-control"></input>
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
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 変更</span></button>
    </div>
</form>
