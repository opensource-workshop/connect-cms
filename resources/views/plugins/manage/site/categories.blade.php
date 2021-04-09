{{--
 * サイト管理（カテゴリ）のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.site.site_manage_tab')
    </div>

    <div class="card-body">

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
                    form_delete_category.action = "{{url('/manage/site/deleteCategories')}}/" + id;
                    form_delete_category.submit();
                }
            }
        </script>

        <form action="" method="POST" name="form_delete_category">
            {{ csrf_field() }}
        </form>

        <form action="{{url('/')}}/manage/site/saveCategories" method="POST">
            {{ csrf_field() }}

            <div class="form-group table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th nowrap colspan="8"><h5 class="mb-0"><span class="badge badge-secondary">共通カテゴリ</span></h5></th>
                        </tr>
                        <tr>
                            <th nowrap>表示順 <span class="badge badge-danger">必須</span></th>
                            <th nowrap>クラス名</th>
                            <th nowrap>カテゴリ <span class="badge badge-danger">必須</span></th>
                            <th nowrap>文字色 <span class="badge badge-danger">必須</span></th>
                            <th nowrap>背景色 <span class="badge badge-danger">必須</span></th>
                            <th nowrap>対象</th>
                            <th nowrap>対象カテゴリID</th>
                            <th nowrap class="text-center"><i class="fas fa-trash-alt"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td nowrap>
                                    <input type="hidden" value="{{$category->id}}" name="categories_id[{{$category->id}}]">
                                    <input type="text" value="{{old('display_sequence.'.$category->id, $category->display_sequence)}}" name="display_sequence[{{$category->id}}]" class="form-control @if ($errors && $errors->has('display_sequence.'.$category->id)) border-danger @endif">
                                </td>
                                <td nowrap>
                                    <input type="text" value="{{old('classname.'.$category->id, $category->classname)}}" name="classname[{{$category->id}}]" class="form-control @if ($errors && $errors->has('classname.'.$category->id)) border-danger @endif">
                                </td>
                                <td nowrap>
                                    <input type="text" value="{{old('category.'.$category->id, $category->category)}}" name="category[{{$category->id}}]" class="form-control @if ($errors && $errors->has('category.'.$category->id)) border-danger @endif">
                                </td>
                                <td nowrap>
                                    <input type="text" value="{{old('color.'.$category->id, $category->color)}}" name="color[{{$category->id}}]" class="form-control @if ($errors && $errors->has('color.'.$category->id)) border-danger @endif">
                                </td>
                                <td nowrap>
                                    <input type="text" value="{{old('background_color.'.$category->id, $category->background_color)}}" name="background_color[{{$category->id}}]" class="form-control @if ($errors && $errors->has('background_color.'.$category->id)) border-danger @endif">
                                </td>
                                <td nowrap class="align-middle">
                                    <input type="hidden" value="{{old('target.'.$category->id, $category->target)}}" name="target[{{$category->id}}]" class="form-control">
                                    @if ($category->target)
                                        <span class="badge" style="color:{{$category->color}};background-color:{{$category->background_color}};">{{$category->target}}</span>
                                    @else
                                        <span class="badge" style="color:{{$category->color}};background-color:{{$category->background_color}};">ALL</span>
                                    @endif
                                </td>
                                <td nowrap class="align-middle">
                                    <input type="hidden" value="{{old('plugin_id.'.$category->id, $category->plugin_id)}}" name="plugin_id[{{$category->id}}]">
                                    {{$category->plugin_id}}
                                </td>
                                <td nowrap>
                                    <a href="javascript:form_delete('{{$category->id}}');"><span class="btn btn-danger"><i class="fas fa-trash-alt"></i></span></a>
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td nowrap>
                                <input type="text" value="{{old('add_display_sequence')}}" name="add_display_sequence" class="form-control @if ($errors && $errors->has('add_display_sequence')) border-danger @endif">
                            </td>
                            <td nowrap>
                                <input type="text" value="{{old('add_classname')}}" name="add_classname" class="form-control @if ($errors && $errors->has('add_classname')) border-danger @endif">
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
                                <input type="hidden" value="{{old('add_target')}}" name="add_target" class="form-control">
                            </td>
                            <td nowrap>
                                <input type="hidden" value="{{old('add_plugin_id')}}" name="add_plugin_id">
                            </td>
                            <td nowrap>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card card-body bg-light p-2 mb-3">
                <ul>
                    <li>カテゴリ設定後は、各プラグインのカテゴリ設定で表示設定が必要です。</li>
                    <li>各プラグインのカテゴリ設定から、コンテンツ単位で独自カテゴリを設定することも可能です。</li>
                    <li>「文字色」「背景色」にはHTMLで指定できる色キーワード（例：<code>red</code>, <code>blue</code>）やRGB色（例：<code>#000000</code>, <code>#111</code>）等を設定できます。</li>
                    <li>「クラス名」はCSSのクラス名を設定できます。<code>cc_category_クラス名</code> で使用できます。</li>
                </ul>
            </div>

            <div class="form-group text-center">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/site/categories')}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 変更</span></button>
            </div>
        </form>

    </div>
</div>

@endsection
