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

            {{-- 詳細表示 --}}
            <h3 class="cc_margin_top_4"><span class="label label-primary">パスワードを忘れてしまいました。</span></h3><span class="label label-warning">Connect-CMS</span> <span class="label label-default">ログイン</span>
            <br /><br />
            <p>
            パスワードは・・・・・・・・・・・・・・・・・・<br />
            パスワードは・・・・・・・・・・・・・・・・・・<br />
            </p>

            <div class="form-group">
                <div class="row">
                    <div class="text-center">
                        <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick=""><span class="glyphicon glyphicon-remove"></span> 一覧へ</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
