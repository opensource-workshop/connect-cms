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
        @include('plugins.manage.reservation.reservation_manage_tab')
    </div>

    <div class="card-body">

        {{-- エラーメッセージ --}}
        @include('plugins.common.errors_all')

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        {{-- 削除ボタンのアクション --}}
        <script type="text/javascript">
            function form_delete(id) {
                if (confirm('カテゴリを削除します。\nよろしいですか？')) {
                    form_delete_category.action = "{{url('/manage/reservation/deleteCategories')}}/" + id;
                    form_delete_category.submit();
                }
            }
        </script>

        <form action="" method="POST" name="form_delete_category">
            {{ csrf_field() }}
        </form>

        <form action="{{url('/')}}/manage/reservation/saveCategories" method="POST">
            {{ csrf_field() }}

            <div class="form-group table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th nowrap>表示順 <span class="badge badge-danger">必須</span></th>
                            <th nowrap>カテゴリ <span class="badge badge-danger">必須</span></th>
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
                                    <input type="text" value="{{old('category.'.$category->id, $category->category)}}" name="category[{{$category->id}}]" class="form-control @if ($errors && $errors->has('category.'.$category->id)) border-danger @endif">
                                    @if ($category->id == 1)
                                        <small class="text-muted">※ カテゴリなしのため、削除できません。</small>
                                    @endif
                                </td>
                                <td nowrap>
                                    {{-- カテゴリなしは削除不可 --}}
                                    @if ($category->id != 1)
                                        <a href="javascript:form_delete('{{$category->id}}');"><span class="btn btn-danger"><i class="fas fa-trash-alt"></i></span></a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <td nowrap>
                                <input type="text" value="{{old('add_display_sequence')}}" name="add_display_sequence" class="form-control @if ($errors && $errors->has('add_display_sequence')) border-danger @endif">
                            </td>
                            <td nowrap>
                                <input type="text" value="{{old('add_category')}}" name="add_category" class="form-control @if ($errors && $errors->has('add_category')) border-danger @endif">
                            </td>
                            <td nowrap></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="form-group text-center">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/reservation/categories')}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 変更</span></button>
            </div>
        </form>

    </div>
</div>

@endsection
