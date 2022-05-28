@extends("manual.common.main_full_norow")

@section('content_main')
<div class="card mt-3">
    <div class="card-header text-white bg-primary">Connect-CMS オンライン・マニュアル</div>
    <div class="card-body">
        @if (\App\Models\Core\Dusks::hasMp4File('top/index/index/mp4/mizuki/_video.mp4'))
            <div class="row">
                <div class="col-lg-4 p-0">
                    <div class="embed-responsive embed-responsive-16by9">
                        @if (\App\Models\Core\Dusks::hasPosterFile('top/index/index/mp4/mizuki/_poster.png'))
                        <video src="./top/index/index/mp4/mizuki/_video.mp4"
                               class="embed-responsive-item"
                               poster="./top/index/index/mp4/mizuki/_poster.png"
                               controls>
                        </video>
                        @else
                        <video src="./top/index/index/mp4/mizuki/_video.mp4"
                               class="embed-responsive-item"
                               controls>
                        </video>
                        @endif
                    </div>
                </div>

                <div class="col-lg-8">
                    <p>ようこそ、Connect-CMS のマニュアルへ。</p>
                    <p>まずは、バッジ・メニューから、見たいカテゴリをクリックしましょう。</p>
                    {{-- バッジ・メニュー --}}
                    @include('manual.common.badge_menu')
                </div>
            </div>
        @else
            <p>ようこそ、Connect-CMS のマニュアルへ。</p>
            <p>まずは、バッジ・メニューから、見たいカテゴリをクリックしましょう。</p>
            {{-- バッジ・メニュー --}}
            @include('manual.common.badge_menu')
        @endif
    </div>
</div>

<div class="row mt-3">
    <div class="col-sm">
        <div class="card">
            <div class="card-header text-white bg-primary">Connect-CMS ダウンロード・マニュアル</div>
            <div class="card-body">
                <p>Connect-CMS のマニュアルをPDF でダウンロードできます。</p>
                <ul>
                    <li><a href="./pdf/manual.pdf" target="_blank">Connect-CMS 全機能版マニュアルPDF ダウンロード</a>
                    <li><a href="./pdf/basic.pdf" target="_blank">Connect-CMS 基本機能版マニュアルPDF ダウンロード</a>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="row mt-3">
    <div class="col-sm">
        <div class="card">
            <div class="card-header text-white bg-primary">Connect-CMS 情報源</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dd class="col-md-2"><a href="https://connect-cms.jp/" target="_blank">Connect-CMS公式サイト</a></dd>
                    <dd class="col-md-10">フォーラム掲示板や基本的な情報はこちらを参照してください。</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
<div class="row mt-3">
    <div class="col-sm">
        <div class="card">
            <div class="card-header text-white bg-primary">ライセンス</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dd class="col-md-2">Connect-CMS のライセンス</dd>
                    <dd class="col-md-10">ソフトウェアとしての Connect-CMS は MIT ライセンスで公開しています。<br /><a href="https://github.com/opensource-workshop/connect-cms/blob/master/LICENSE" target="_blank">https://github.com/opensource-workshop/connect-cms/blob/master/LICENSE</a></dd>
                </dl>
                <dl class="row mb-0">
                    <dd class="col-md-2">ドキュメントのライセンス</dd>
                    <dd class="col-md-10">Connect-CMS マニュアルは GFDL ライセンスで公開しています。<br /><a href="./LICENSE.md" target="_blank">LICENSE.md</a></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
<div class="row mt-3">
    <div class="col-sm">
        <div class="card">
            <div class="card-header text-white bg-primary">動作環境</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dd class="col-md-2"><a href="https://github.com/opensource-workshop/connect-cms/wiki/Install" target="_blank">サーバ側の動作環境(Github)</a></dd>
                    <dd class="col-md-10"><p>ご自分でインストールなど行う方はこちらを参照してください。</p></dd>
                    <dd class="col-md-2">PCでの動作環境</dd>
                    <dd class="col-md-10"><ul><li>Chrome、Firefox、Edge（Chromium版）、Safari（Mac）の最新バージョン</li></ul></dd>
                    <dd class="col-md-2">スマートフォンでの動作環境</dd>
                    <dd class="col-md-10"><ul><li>iPhone、Androidの標準ブラウザの最新バージョン</li></ul></dd>
                </dl>
                <p>その他、基本的なブラウザでは、PC、スマートフォンとも動作するように設計、実装しております。<br />
                   もし、うまく動かないよ。というパターンがありましたら、お使いのOS、ブラウザとそれぞれのバージョンを公式サイトのお問い合わせフォームでお知らせください。<br />
                   可能な範囲で調査したいと思います。
                </p>
                <p>※ InternetExplorer は動作確認対象外とさせていただいております。</p>
            </div>
        </div>
    </div>
</div>
@endsection
