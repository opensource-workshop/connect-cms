{{--
 * 一般プラグイン－カテゴリ設定画面の共通blade
 *
 * @param $general_categories 共通カテゴリ
 * @param $plugin_categories 個別カテゴリ
 *
 * // 以下は UserPluginBase::view() で自動セット
 * @param $frame フレーム
 * @param $frame_id フレーム
 * @param $page ページ
 * @param $errors 入力エラー情報
--}}

{{-- エラーメッセージ --}}
@include('plugins.common.errors_all')

{{-- 削除ボタンのアクション --}}
<script type="text/javascript">
    function form_delete(id) {
        if (confirm('カテゴリを削除します。\nよろしいですか？')) {
            form_delete_category.action = "{{url('/')}}/redirect/plugin/{{$frame->plugin_name}}/deleteCategories/{{$page->id}}/{{$frame_id}}/" + id + "#frame-{{$frame->id}}";
            form_delete_category.submit();
        }
    }
</script>
<form action="" method="POST" name="form_delete_category">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/{{$frame->plugin_name}}/listCategories/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
</form>

<div id="app">
<form action="{{url('/')}}/redirect/plugin/{{$frame->plugin_name}}/saveCategories/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/{{$frame->plugin_name}}/listCategories/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">

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

                    {{-- カスタムチェックボックスのインプットとラベルをくくる div の id は自動テスト時、ラベルが空の場合にクリックできないための対応 --}}
                    <div class="custom-control custom-checkbox" id="div_general_view_flag_{{$category->id}}">
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
                <th nowrap>クラス名 <span class="badge badge-danger">必須</span></th>
                <th nowrap>カテゴリ <span class="badge badge-danger">必須</span></th>
                <th nowrap>文字色 <span class="badge badge-danger">必須</span><br><small class="text-muted">パレット選択可</small></th>
                <th nowrap>背景色 <span class="badge badge-danger">必須</span><br><small class="text-muted">パレット選択可</small></th>
                <th nowrap class="text-center"><i class="fas fa-trash-alt"></i></th>
            </tr>

        @if ($plugin_categories)
        @foreach($plugin_categories as $category)
            <tr>
                <td nowrap class="align-middle text-center">
                    <input type="hidden" value="{{$category->id}}" name="plugin_categories_id[{{$category->id}}]">

                    {{-- カスタムチェックボックスのインプットとラベルをくくる div の id は自動テスト時、ラベルが空の場合にクリックできないための対応 --}}
                    <div class="custom-control custom-checkbox" id="div_plugin_view_flag_{{$category->id}}">
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
                    <input type="text" value="{{old('plugin_classname.'.$category->id, $category->classname)}}" name="plugin_classname[{{$category->id}}]" class="form-control @if ($errors && $errors->has('plugin_classname.'.$category->id)) border-danger @endif">
                </td>
                <td nowrap>
                    <input type="text" value="{{old('plugin_category.'.$category->id, $category->category)}}" name="plugin_category[{{$category->id}}]" class="form-control @if ($errors && $errors->has('plugin_category.'.$category->id)) border-danger @endif">
                </td>
                <td nowrap>
                    <div class="d-flex align-items-center">
                        <input type="text" v-model="v_plugin_color_{{$category->id}}" name="plugin_color[{{$category->id}}]" class="form-control @if ($errors && $errors->has('plugin_color.'.$category->id)) border-danger @endif" placeholder="(例)#000000" style="width: 100px;">
                        <div class="position-relative ml-2">
                            <input type="color" v-model="v_plugin_color_{{$category->id}}" class="btn" style="width: 38px; height: 38px; border: 2px solid #dee2e6; border-radius: 4px; padding: 0; cursor: pointer;" title="カラーパレットから選択">
                            <i class="fas fa-palette position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; color: white; text-shadow: 1px 1px 1px rgba(0,0,0,0.5); font-size: 14px;"></i>
                        </div>
                    </div>
                </td>
                <td nowrap>
                    <div class="d-flex align-items-center">
                        <input type="text" v-model="v_plugin_background_color_{{$category->id}}" name="plugin_background_color[{{$category->id}}]" class="form-control @if ($errors && $errors->has('plugin_background_color.'.$category->id)) border-danger @endif" placeholder="(例)#ffffff" style="width: 100px;">
                        <div class="position-relative ml-2">
                            <input type="color" v-model="v_plugin_background_color_{{$category->id}}" class="btn" style="width: 38px; height: 38px; border: 2px solid #dee2e6; border-radius: 4px; padding: 0; cursor: pointer;" title="カラーパレットから選択">
                            <i class="fas fa-palette position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; color: white; text-shadow: 1px 1px 1px rgba(0,0,0,0.5); font-size: 14px;"></i>
                        </div>
                    </div>
                </td>
                <td nowrap>
                    <a href="javascript:form_delete('{{$category->id}}');"><span class="btn btn-danger"><i class="fas fa-trash-alt"></i></span></a>
                </td>
            </tr>
        @endforeach
        @endif

            <tr>
                <td nowrap class="align-middle text-center">
                    {{-- カスタムチェックボックスのインプットとラベルをくくる div の id は自動テスト時、ラベルが空の場合にクリックできないための対応 --}}
                    <div class="custom-control custom-checkbox" id="div_add_view_flag">
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
                <td nowrap>
                </td>
            </tr>
        </tbody>
        </table>
    </div>

    @include('plugins.common.description_plugin_category')

    <div class="form-group text-center">
        <a href="{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}" class="btn btn-secondary mr-2"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更</button>
    </div>
</form>
</div>
<script>
    createApp({
        data: function() {
            return {
                // カラーピッカー用のデータ
                @if ($plugin_categories)
                    @foreach($plugin_categories as $category)
                        v_plugin_color_{{$category->id}}: '{{old("plugin_color.".$category->id, $category->color)}}',
                        v_plugin_background_color_{{$category->id}}: '{{old("plugin_background_color.".$category->id, $category->background_color)}}',
                    @endforeach
                @endif
                v_add_color: '{{old("add_color")}}',
                v_add_background_color: '{{old("add_background_color")}}'
            }
        },
    }).mount('#app');
</script>
