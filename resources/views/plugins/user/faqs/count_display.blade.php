{{--
 * FAQ件数表示共通部品
 *
 * @author 井上 雅人 <inoue@opensource-workshop.co.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category FAQプラグイン
--}}

{{-- 件数表示 --}}
<div class="row mb-3">
    <div class="col-md-9 d-flex align-items-center">
        <div>
            表示件数 {{ $faqs_posts->total() }} 件
            {{ $faqs_posts->total() > 0 ? ' (' . $faqs_posts->firstItem() . '-' . $faqs_posts->lastItem() . ')' : '' }}
        </div>
    </div>
</div>