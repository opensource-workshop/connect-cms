{{--
 * サイト管理（サイト設計書）のサイト基本設定のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト管理
 --}}
{{-- CSS --}}
@include('plugins/manage/site/pdf/css')

<br />
<h2 style="text-align: center; font-size: 28px;">サイト基本設定</h2>

<br />
<h4>サイト基本設定</h4>
<table border="0" class="table_css">
    <tr nobr="true">
        <th class="doc_th">設定項目</th>
        <th class="doc_th">設定内容</th>
    </tr>
    <tr nobr="true">
        <td>サイトURL</td>
        <td>{{url('/')}}</td>
    </tr>
    <tr nobr="true">
        <td>サイト名</td>
        <td>{{$configs->firstWhere('name', 'base_site_name')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>基本テーマ</td>
        <td>{{$configs->firstWhere('name', 'base_theme')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>追加テーマ</td>
        <td>{{$configs->firstWhere('name', 'additional_theme')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>背景色</td>
        <td>{{$configs->firstWhere('name', 'base_background_color')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>ヘッダーバーの背景色</td>
        <td>{{$configs->firstWhere('name', 'base_header_color')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>ヘッダーバーの文字色</td>
        <td>{{$configs->firstWhere('name', 'base_header_font_color_class')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>ヘッダーバーの任意クラス</td>
        <td>{{$configs->firstWhere('name', 'base_header_optional_class')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>センターエリア任意クラス</td>
        <td>{{$configs->firstWhere('name', 'center_area_optional_class')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>bodyタグ任意クラス</td>
        <td>{{$configs->firstWhere('name', 'body_optional_class')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>フッターエリア任意クラス</td>
        <td>{{$configs->firstWhere('name', 'footer_area_optional_class')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>ヘッダーバーの表示</td>
        @if ($configs->firstWhere('name', 'base_header_hidden')->value == '1') <td>表示しない</td> @else <td>表示する</td> @endif
    </tr>
    <tr nobr="true">
        <td>ヘッダーバーの固定</td>
        @if ($configs->firstWhere('name', 'base_header_fix')->value == '1') <td>固定する</td> @else <td>固定しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>ログインリンクの表示</td>
        @if ($configs->firstWhere('name', 'base_header_login_link')->value == '1') <td>表示する</td> @else <td>表示しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>パスワードリセットの使用</td>
        @if ($configs->firstWhere('name', 'base_login_password_reset')->value == '1') <td>許可する</td> @else <td>許可しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>ログイン後に移動するページ</td>
        @if ($configs->firstWhere('name', 'base_login_redirect_previous_page')->value == '1') <td>元いたページ</td> @elseif ($configs->firstWhere('name', 'base_login_redirect_previous_page')->value == '2') <td>指定したページ</td> @else <td>トップページ</td> @endif
    </tr>
    <tr nobr="true">
        <td>ログイン後に移動する指定ページ</td>
        <td>{{$configs->firstWhere('name', 'base_login_redirect_select_page')->value}}</td>
    </tr>
    <tr nobr="true">
        <td>マイページの使用</td>
        @if ($configs->firstWhere('name', 'use_mypage')->value == '1') <td>許可する</td> @else <td>許可しない</td> @endif
    </tr>
    <tr nobr="true">
        <td>画像の保存機能の無効化</td>
            @php
                $image_nosaves = array();
                if (Configs::checkConfigValue($configs, 'base_mousedown_off', '1')) {
                    $image_nosaves[] = 'ドラッグ禁止';
                }
                if (Configs::checkConfigValue($configs, 'base_contextmenu_off', '1')) {
                    $image_nosaves[] = '右クリックメニュー禁止';
                }
                if (Configs::checkConfigValue($configs, 'base_contextmenu_off', '1')) {
                    $image_nosaves[] = 'スマホ長押し禁止';
                }
            @endphp
        <td>@php echo implode(', ', $image_nosaves); @endphp</td>
    </tr>
    <tr nobr="true">
        <td>スマホメニューのフォーマット</td>
        <td>{{$configs->firstWhere('name', 'smartphone_menu_template')->value}}</td>
    </tr>
</table>