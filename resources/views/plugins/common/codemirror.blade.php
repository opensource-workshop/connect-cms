{{--
 * コード色装飾＆入力テンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
--}}
@php
    // CodeMirrorの設定
    $element_id = $element_id ?? null;
    $mode = $mode ?? null;
    $height = $height ?? 'null';
@endphp

<script type="text/javascript">
    var editor_{{$element_id}} = CodeMirror.fromTextArea(document.getElementById("{{$element_id}}"),
    {
        mode:"{{$mode}}",    // 言語を設定する.（laravel mixを使って resources/js/bootstrap.js に対応言語を設定し、public/js/app.js 内にまとめている）
        lineNumbers: true,   // 行番号を表示する
        lineWrapping: true,  // 行を折り返す
    });

    // 高さ設定
    editor_{{$element_id}}.setSize(null, {{$height}});
</script>
