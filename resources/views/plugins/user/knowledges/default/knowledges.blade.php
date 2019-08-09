{{--
 * 表示画面テンプレート。データのみ。HTMLは解釈する。
 * 
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ナレッジ・プラグイン
 --}}

<div class="container-fruid">

    <div class="row">
        <div class="col-md-3">

            {{-- ソフトウェア表示 --}}
            @include('plugins.user.knowledges.default.knowledges_include_softwares')

            {{-- タグ表示 --}}
            @include('plugins.user.knowledges.default.knowledges_include_tags')

        </div>
        <div class="col-md-9">

            {{-- フリーワード検索 --}}
            @include('plugins.user.knowledges.default.knowledges_include_word_search')
            <br />

            {{-- 新着表示 --}}
            <h3 class="cc_margin_top_4"><span class="label label-primary">サポート・データベース 検索結果</span></h3>
            <ul class="cc_ul_padding_top_10 cc_ul_line_height">
                <li><a href="{{url('/')}}/plugin/knowledges/detail/{{$page->id}}/{{$frame_id}}">パスワードを忘れてしまいました。</a><span class="label label-warning">Connect-CMS</span> <span class="label label-default">ログイン</span>
                <li><a href="{{url('/')}}/plugin/knowledges/detail/{{$page->id}}/{{$frame_id}}">パスワードを忘れてしまいました。</a><span class="label label-success">NetCommons3</span>
                <li><a href="{{url('/')}}/plugin/knowledges/detail/{{$page->id}}/{{$frame_id}}">パスワードを忘れてしまいました。</a><span class="label label-info">NetCommons2</span>
                <li><a href="{{url('/')}}/plugin/knowledges/detail/{{$page->id}}/{{$frame_id}}">パスワードを忘れてしまいました。</a>
            </ul>
        </div>
    </div>
</div>
