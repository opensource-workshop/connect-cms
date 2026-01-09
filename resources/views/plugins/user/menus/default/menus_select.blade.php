{{--
 * ページ選択画面テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
 --}}
@php
    $can_use_setting_menu = $can_use_setting_menu ?? false;
    $layout = $can_use_setting_menu ? 'core.cms_frame_base_setting' : 'core.cms_frame_base';
    $section = $can_use_setting_menu ? "plugin_setting_{$frame->id}" : "plugin_contents_{$frame->id}";
    $is_page_condition = !isset($menu) || $menu->select_flag == 0;
@endphp

@extends($layout)

@if ($can_use_setting_menu)
    @section("core.cms_frame_edit_tab_$frame->id")
        @include('plugins.user.menus.menus_frame_edit_tab')
    @endsection
@endif

@section($section)
<form action="{{url('/')}}/plugin/menus/saveSelect/{{$page->id}}/{{$frame->frame_id}}#frame-{{$frame->id}}" name="contents_buckets_form" method="POST" class="mt-3">
    {{ csrf_field() }}

    <div class="form-group">
        <label class="col-form-label">ページの表示</label><br />
        <div class="custom-control custom-radio custom-control-inline">
            @if(!isset($menu) || (isset($menu) && $menu->select_flag == 0))
                <input type="radio" value="0" id="select_on" name="select_flag" class="custom-control-input" checked="checked">
            @else
                <input type="radio" value="0" id="select_on" name="select_flag" class="custom-control-input">
            @endif
            <label class="custom-control-label" for="select_on">ページ管理の条件</label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
            @if(isset($menu) && $menu->select_flag == 1)
                <input type="radio" value="1" id="select_off" name="select_flag" class="custom-control-input" checked="checked">
            @else
                <input type="radio" value="1" id="select_off" name="select_flag" class="custom-control-input">
            @endif
            <label class="custom-control-label" for="select_off">選択したもののみ</label>
        </div>
    </div>

    <div id="page-condition-summary" class="alert alert-info small {{ $is_page_condition ? '' : 'd-none' }}">
        <div class="font-weight-bold">ページ管理のメニュー表示が適用されます。</div>
        <div>メニューの表示対象は、ページ管理の「メニュー表示」に従います。</div>
        <div>詳細は <a href="{{url('/manage/page')}}" target="_blank" rel="noopener">ページ管理</a> で確認できます。</div>
        <div class="mt-2">
            <span class="mr-2"><i class="far fa-eye"></i>表示</span>
            <span class="mr-2"><i class="far fa-eye-slash"></i>非表示</span>
        </div>
        <div class="text-muted">※ 親ページの設定を継承している場合があります。</div>
    </div>

@if ($pages)
    <div class="form-group">
        <label class="col-form-label">ページの選択</label><br />
        <small id="page-select-note" class="form-text text-info {{ $is_page_condition ? '' : 'd-none' }}">ページ管理の条件が選択されているため、ページ選択は編集できません。表示ページを個別に選ぶ場合は「選択したもののみ」を選んでください。</small>
        <div id="page-select-wrapper" class="{{ $is_page_condition ? 'cc-menu-select-locked' : '' }}" aria-disabled="{{ $is_page_condition ? 'true' : 'false' }}">
            <div id="page-select-list" class="list-group mb-0">
    @foreach($pages as $page_record)

        {{-- 非表示のページは対象外 --}}

        {{-- 設定画面では、全てのページを表示して、選択可能とする。
        @if ($page_record->display_flag == 1)
        --}}
            {{-- 子供のページがある場合 --}}
            @if (count($page_record->children) > 0)

                {{-- リンク生成。メニュー項目全体をリンクにして階層はその中でインデント表記したいため、a タグから記載 --}}
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="page_select{{$page_record->id}}" name="page_select[]" value="{{$page_record->id}}" @if ($menu && $menu->onPage($page_record->id)) checked @endif />
                    <label class="custom-control-label" for="page_select{{$page_record->id}}">

                    {{-- 各ページの深さをもとにインデントの表現 --}}
                    @for ($i = 0; $i < $page_record->depth; $i++)
                        @if ($i+1==$children->depth) <i class="fas fa-chevron-right"></i> @else <span class="px-2"></span>@endif
                    @endfor
                    {{$page_record->page_name}}
                    @php
                        $menu_display_label = $page_record->display_flag ? 'メニュー表示: 表示' : 'メニュー表示: 非表示';
                        if (!$page_record->display_flag && $page_record->base_display_flag == 1) {
                            $menu_display_label .= '（親ページの非表示を継承）';
                        }
                    @endphp
                    <span class="cc-menu-page-conditions js-page-condition-item ml-2 {{ $is_page_condition ? '' : 'd-none' }}">
                        @if ($page_record->display_flag == 1)
                            <i class="far fa-eye" title="{{$menu_display_label}}"></i>
                        @else
                            <i class="far fa-eye-slash text-muted" title="{{$menu_display_label}}"></i>
                        @endif
                    </span>
                    </label>
                </div>

                {{-- 子要素を再帰的に表示するため、別ファイルに分けてinclude --}}
                @foreach($page_record->children as $children)
                    @include('plugins.user.menus.default.menus_select_children',['children' => $children, 'page_id' => $page_id])
                @endforeach
            @else

                {{-- リンク生成。メニュー項目全体をリンクにして階層はその中でインデント表記したいため、a タグから記載 --}}
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="page_select{{$page_record->id}}" name="page_select[]" value="{{$page_record->id}}" @if ($menu && $menu->onPage($page_record->id)) checked @endif />
                    <label class="custom-control-label" for="page_select{{$page_record->id}}">

                    {{-- 各ページの深さをもとにインデントの表現 --}}
                    @for ($i = 0; $i < $page_record->depth; $i++)
                        @if ($i+1==$children->depth) <i class="fas fa-chevron-right"></i> @else <span class="px-2"></span>@endif
                    @endfor
                    {{$page_record->page_name}}
                    @php
                        $menu_display_label = $page_record->display_flag ? 'メニュー表示: 表示' : 'メニュー表示: 非表示';
                        if (!$page_record->display_flag && $page_record->base_display_flag == 1) {
                            $menu_display_label .= '（親ページの非表示を継承）';
                        }
                    @endphp
                    <span class="cc-menu-page-conditions js-page-condition-item ml-2 {{ $is_page_condition ? '' : 'd-none' }}">
                        @if ($page_record->display_flag == 1)
                            <i class="far fa-eye" title="{{$menu_display_label}}"></i>
                        @else
                            <i class="far fa-eye-slash text-muted" title="{{$menu_display_label}}"></i>
                        @endif
                    </span>
                    </label>
                </div>
            @endif
        {{--
        @endif
        --}}
    @endforeach
            </div>
        </div>
    </div>
@endif

     <div class="form-group">
        <label class="control-label">閉じるフォント</label>
        <select class="form-control" name="folder_close_font" class="form-control">
            <option value="0" @if(old('folder_close_font') == 0 || (isset($menu) && $menu->folder_close_font == 0)) selected="selected" @endif>－（初期値）</option>
            <option value="1" @if(old('folder_close_font') == 1 || (isset($menu) && $menu->folder_close_font == 1)) selected="selected" @endif>なし</option>
            <option value="2" @if(old('folder_close_font') == 2 || (isset($menu) && $menu->folder_close_font == 2)) selected="selected" @endif>V</option>
        </select>
        @if ($errors && $errors->has('folder_close_font')) <div class="text-danger">{{$errors->first('folder_close_font')}}</div> @endif
    </div>

     <div class="form-group">
        <label class="control-label">開くフォント</label>
        <select class="form-control" name="folder_open_font" class="form-control">
            <option value="0" @if(old('folder_open_font') == 0 || (isset($menu) && $menu->folder_open_font == 0)) selected="selected" @endif>＋（初期値）</option>
            <option value="1" @if(old('folder_open_font') == 1 || (isset($menu) && $menu->folder_open_font == 1)) selected="selected" @endif>なし</option>
            <option value="2" @if(old('folder_open_font') == 2 || (isset($menu) && $menu->folder_open_font == 2)) selected="selected" @endif>＞</option>
        </select>
        @if ($errors && $errors->has('folder_open_font')) <div class="text-danger">{{$errors->first('folder_open_font')}}</div> @endif
    </div>

     <div class="form-group">
        <label class="control-label">インデントフォント</label>
        <select class="form-control" name="indent_font" class="form-control">
            <option value="0" @if(old('indent_font') == 0 || (isset($menu) && $menu->indent_font == 0)) selected="selected" @endif>＞（初期値）</option>
            <option value="1" @if(old('indent_font') == 1 || (isset($menu) && $menu->indent_font == 1)) selected="selected" @endif>なし</option>
            <option value="2" @if(old('indent_font') == 2 || (isset($menu) && $menu->indent_font == 2)) selected="selected" @endif>－</option>
        </select>
        @if ($errors && $errors->has('indent_font')) <div class="text-danger">{{$errors->first('indent_font')}}</div> @endif
    </div>

    <div class="form-group row mx-0">
        <div class="offset-md-3">
            <button type="button" class="btn btn-secondary form-horizontal mr-2" onclick="location.href='{{URL::to($current_pages->permanent_link)}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span></button>
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
        </div>
    </div>
</form>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var selectOn = document.getElementById('select_on');
    var selectOff = document.getElementById('select_off');
    var wrapper = document.getElementById('page-select-wrapper');
    var list = document.getElementById('page-select-list');
    var note = document.getElementById('page-select-note');
    var summary = document.getElementById('page-condition-summary');

    function updateLockState() {
        var locked = selectOn && selectOn.checked;
        if (note) {
            note.classList.toggle('d-none', !locked);
        }
        if (summary) {
            summary.classList.toggle('d-none', !locked);
        }
        var items = document.querySelectorAll('.js-page-condition-item');
        items.forEach(function (item) {
            item.classList.toggle('d-none', !locked);
        });
        if (!wrapper || !list) {
            return;
        }
        wrapper.classList.toggle('cc-menu-select-locked', locked);
        wrapper.setAttribute('aria-disabled', locked ? 'true' : 'false');
        var checkboxes = list.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(function (checkbox) {
            if (locked) {
                checkbox.setAttribute('tabindex', '-1');
                checkbox.setAttribute('aria-disabled', 'true');
                checkbox.blur();
            } else {
                checkbox.removeAttribute('tabindex');
                checkbox.removeAttribute('aria-disabled');
            }
        });
    }

    if (selectOn) {
        selectOn.addEventListener('change', updateLockState);
    }
    if (selectOff) {
        selectOff.addEventListener('change', updateLockState);
    }
    if (list) {
        list.addEventListener('click', function (event) {
            if (wrapper && wrapper.classList.contains('cc-menu-select-locked')) {
                event.preventDefault();
            }
        });
        list.addEventListener('keydown', function (event) {
            if (wrapper && wrapper.classList.contains('cc-menu-select-locked')) {
                if (event.key === ' ' || event.key === 'Enter') {
                    event.preventDefault();
                }
            }
        });
    }

    updateLockState();
});
</script>
@endsection
