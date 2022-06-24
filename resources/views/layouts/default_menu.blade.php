{{--
 * メニュー（default）
 *
 * @param obj $page_obj ページデータ
 * @author 石垣 佑樹 <ishigaki@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コア
--}}
@if ($page_obj->isView(Auth::user(), false, true, $page_roles))
    <li class="nav-item">
        {{-- リンク生成。メニュー項目全体をリンクにして階層はその中でインデント表記したいため、a タグから記載 --}}
        @if (isset($page_obj) && isset($page) && $page_obj->id == $page->id)
            <a href="{{ $page_obj->getUrl() }}" {!!$page_obj->getUrlTargetTag()!!} class="nav-link active">
        @else
            <a href="{{ $page_obj->getUrl() }}" {!!$page_obj->getUrlTargetTag()!!} class="nav-link">
        @endif

        {{-- 各ページの深さをもとにインデントの表現 --}}
        @for ($i = 0; $i < $page_obj->depth; $i++)
            @if ($i+1==$page_obj->depth) <i class="fas fa-chevron-right"></i> @else <span class="px-2"></span>@endif
        @endfor
            {{$page_obj->page_name}}
            </a>
    </li>

    {{-- 子ページの出力 --}}
    @if (count($page_obj->children) > 0)
        @foreach($page_obj->children as $child)
            @include('layouts.default_menu', ['page_obj' => $child])
        @endforeach
    @endif
@endif
