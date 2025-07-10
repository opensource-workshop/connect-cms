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
        @include('plugins.common.errors_all')

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

        <div id="app">
        <form action="{{url('/')}}/manage/site/saveCategories" method="POST">
            {{ csrf_field() }}

            <div class="form-group table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        {{--
                        <tr>
                            <th nowrap colspan="8"><h5 class="mb-0"><span class="badge badge-secondary">共通カテゴリ</span></h5></th>
                        </tr>
                        --}}
                        <tr>
                            <th nowrap>表示順 <span class="badge badge-danger">必須</span></th>
                            <th nowrap>クラス名 <span class="badge badge-danger">必須</span></th>
                            <th nowrap>カテゴリ <span class="badge badge-danger">必須</span></th>
                            <th nowrap>文字色 <span class="badge badge-danger">必須</span><br><small class="text-muted">パレット選択可</small></th>
                            <th nowrap>背景色 <span class="badge badge-danger">必須</span><br><small class="text-muted">パレット選択可</small></th>
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
                                    <div class="d-flex align-items-center">
                                        <input type="text" v-model="v_color_{{$category->id}}" name="color[{{$category->id}}]" class="form-control @if ($errors && $errors->has('color.'.$category->id)) border-danger @endif" placeholder="(例)#000000" style="width: 100px;">
                                        <div class="position-relative ml-2">
                                            <input type="color" v-model="v_color_{{$category->id}}" class="btn" style="width: 38px; height: 38px; border: 2px solid #dee2e6; border-radius: 4px; padding: 0; cursor: pointer;" title="カラーパレットから選択">
                                            <i class="fas fa-palette position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; color: white; text-shadow: 1px 1px 1px rgba(0,0,0,0.5); font-size: 14px;"></i>
                                        </div>
                                    </div>
                                </td>
                                <td nowrap>
                                    <div class="d-flex align-items-center">
                                        <input type="text" v-model="v_background_color_{{$category->id}}" name="background_color[{{$category->id}}]" class="form-control @if ($errors && $errors->has('background_color.'.$category->id)) border-danger @endif" placeholder="(例)#ffffff" style="width: 100px;">
                                        <div class="position-relative ml-2">
                                            <input type="color" v-model="v_background_color_{{$category->id}}" class="btn" style="width: 38px; height: 38px; border: 2px solid #dee2e6; border-radius: 4px; padding: 0; cursor: pointer;" title="カラーパレットから選択">
                                            <i class="fas fa-palette position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; color: white; text-shadow: 1px 1px 1px rgba(0,0,0,0.5); font-size: 14px;"></i>
                                        </div>
                                    </div>
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
                                <div class="d-flex align-items-center">
                                    <input type="text" v-model="v_add_color" name="add_color" class="form-control @if ($errors && $errors->has('add_color')) border-danger @endif" placeholder="(例)#000000" style="width: 100px;">
                                    <div class="position-relative ml-2">
                                        <input type="color" v-model="v_add_color" class="btn" style="width: 38px; height: 38px; border: 2px solid #dee2e6; border-radius: 4px; padding: 0; cursor: pointer;" title="カラーパレットから選択">
                                        <i class="fas fa-palette position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; color: white; text-shadow: 1px 1px 1px rgba(0,0,0,0.5); font-size: 14px;"></i>
                                    </div>
                                </div>
                            </td>
                            <td nowrap>
                                <div class="d-flex align-items-center">
                                    <input type="text" v-model="v_add_background_color" name="add_background_color" class="form-control @if ($errors && $errors->has('add_background_color')) border-danger @endif" placeholder="(例)#ffffff" style="width: 100px;">
                                    <div class="position-relative ml-2">
                                        <input type="color" v-model="v_add_background_color" class="btn" style="width: 38px; height: 38px; border: 2px solid #dee2e6; border-radius: 4px; padding: 0; cursor: pointer;" title="カラーパレットから選択">
                                        <i class="fas fa-palette position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; color: white; text-shadow: 1px 1px 1px rgba(0,0,0,0.5); font-size: 14px;"></i>
                                    </div>
                                </div>
                            </td>
                            <td nowrap></td>
                            <td nowrap></td>
                            <td nowrap></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card card-body bg-light p-2 mb-3 small">
                <ul>
                    <li>対象が「ALL」のカテゴリは「共通カテゴリ」です。</li>
                    <li>対象が各プラグインのカテゴリは「個別カテゴリ」です。</li>
                    <li>カテゴリ設定後は、各プラグインのカテゴリ設定で表示設定が必要です。</li>
                    <li>各プラグインのカテゴリ設定から、コンテンツ単位で独自カテゴリを設定することも可能です。</li>
                    <li>「文字色」「背景色」にはHTMLで指定できる色キーワード（例：<code>red</code>, <code>blue</code>）やRGB色（例：<code>#000000</code>, <code>#111</code>）等を設定できます。パレットアイコンから色を選択することもできます。</li>
                    <li>「クラス名」はCSSのクラス名を設定できます。<code>cc_category_クラス名</code> で使用できます。</li>
                    <ul>
                        <li>「クラス名」は「文字色」「背景色」を反映させるために、他カテゴリとは被らない「クラス名」を設定してください。</li>
                        <li>「クラス名」は必須項目で、システム全体で重複することはできません。</li>
                    </ul>
                </ul>
            </div>

            <div class="text-center">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/site/categories')}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 変更</span></button>
            </div>
        </form>
        </div>

    </div>
</div>
<script>
    createApp({
        data: function() {
            return {
                // カラーピッカー用のデータ
                @foreach($categories as $category)
                    v_color_{{$category->id}}: '{{old("color.".$category->id, $category->color)}}',
                    v_background_color_{{$category->id}}: '{{old("background_color.".$category->id, $category->background_color)}}',
                @endforeach
                v_add_color: '{{old("add_color")}}',
                v_add_background_color: '{{old("add_background_color")}}'
            }
        },
    }).mount('#app');
</script>

@endsection
