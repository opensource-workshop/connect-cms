{{--
 * フラッシュメッセージ ヘッダー表示
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category views/common
--}}
@if (session('flash_message_for_header'))
    <div class="alert {{ session('flash_message_for_header_class') ?? 'alert-success' }} text-center">
        {{ session('flash_message_for_header') }}
    </div>
@endif
