{{--
 * インライン WYSIWYG最大バイト数越えエラー表示テンプレート
 *
 * @param $name inputのname
--}}
@if ($errors && $errors->has($name))
    @include('plugins.common.errors_inline', ['name' => $name])

    @if (stripos($errors->first($name), 'バイト以下の文字列'))
        <div class="alert alert-danger">
            <small>
                <i class="fas fa-exclamation-triangle"></i> WYSIWYG のバイト数エラーの詳細については、Connect-CMS公式サイトを参照してください。<br />
                <a href="https://manual.connect-cms.jp/common/wysiwyg/error/index.html" target="_blank">https://manual.connect-cms.jp/common/wysiwyg/error/index.html</a>
            </small>
        </div>
    @endif
@endif
