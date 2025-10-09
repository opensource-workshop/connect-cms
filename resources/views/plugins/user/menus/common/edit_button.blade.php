{{--
 * メニュー編集ボタン
 *
 * モデレータまたは管理者に対して、設定メニュー外に編集ボタンを表示する。
 *
 * @category メニュープラグイン
 --}}
@if ($can_edit_menu)
    <div class="text-right mb-2 menu-edit-button">
        <a href="{{ url('/') }}/plugin/menus/select/{{ $page->id }}/{{ $frame->id }}#frame-{{ $frame->id }}" class="btn btn-sm btn-outline-success">
            <i class="fas fa-edit"></i> メニューを編集
        </a>
    </div>
@endif
