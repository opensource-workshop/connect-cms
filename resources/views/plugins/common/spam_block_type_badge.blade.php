{{--
 * スパムブロック種別バッジ
 *
 * @param string $block_type SpamBlockType の値
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スパム管理
--}}
@if ($block_type == SpamBlockType::email)
    <span class="badge badge-info">メールアドレス</span>
@elseif ($block_type == SpamBlockType::domain)
    <span class="badge badge-warning">ドメイン</span>
@elseif ($block_type == SpamBlockType::ip_address)
    <span class="badge badge-secondary">IPアドレス</span>
@elseif ($block_type == SpamBlockType::honeypot)
    <span class="badge badge-danger">ハニーポット</span>
@endif
