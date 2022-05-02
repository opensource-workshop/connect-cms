{{--
 * メニュー：ハンバーガーメニュー用のscript・CSSテンプレート
 *
 * @author 牧野 可也子 <makino@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メニュープラグイン
--}}

<!-- ハンバーガーメニューの表示/非表示制御スクリプト -->
<script>
    $(function(){
     $('.menu-humburger-link').on('click', function() {
       $('.menu-humburger-button').toggleClass('active');
       $('.hamburger-menu').toggleClass('active');
       return false;
     });
   });
</script>

<!-- ハンバーガーメニューの基本CSS -->
<style>
    /* ハンバーガーボタンのデザインCSS */
    .menu-humburger-button {
        float: right;
        text-align: -webkit-center;
        padding: 5px 0;
        width: 60px;
        justify-content: center;
        border-radius: 3px;
    }
    .menu-humburger-button p {
        margin: 8px 0 8px 0;
        content: '';
        display: block;
        height: 3px;
        width: 30px;
        border-radius: 3px;
        background-color: #ccc;
        transition: all 0.5s;
    }
    /* ハンバーガーボタン：押下時のデザインCSS */
    .menu-humburger-button.active p:nth-of-type(1) {
        transform: translateY(11px) rotate(-45deg);
        transition: all 0.5s;
    }
    .menu-humburger-button.active p:nth-of-type(2) {
        transform: translateY(0) rotate(45deg);
        transition: all 0.5s;
    }
    .menu-humburger-button.active p:nth-of-type(3) {
        opacity: 0;
        transition: all 0s;
    }
    /* メニュー本体のサイズ設定 */
    .hamburger-menu-area {
        overflow-x: clip;
        position: relative;
        top: 50px;
    }
    .hamburger-menu {
        clear: both;
        width:500px;
        right: -100%;
        overflow: auto;
        position: absolute;
        z-index: 9999;
        opacity: 0;
        transition: all 0.5s;
    }
    .hamburger-menu.active {
        right: 0;
        opacity: 1;
        transition: all 0.5s;
    }
    @media (max-width: 576px) {
        .hamburger-menu {
            clear: both;
            width:100%;
            right: -100%;
            overflow: auto;
            position: absolute;
            z-index: 9999;
            opacity: 0;
        }
    }
    </style>
