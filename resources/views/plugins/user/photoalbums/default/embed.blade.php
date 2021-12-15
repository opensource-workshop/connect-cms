{{--
 * フォトアルバム・埋め込み画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
<html>
<body style="margin: 0px; padding: 0px;">
<video controls controlsList="nodownload"
     src="/file/{{$photoalbum_content->upload_id}}"
     id="video"
     style="width: 100%; height: 100%; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
     class="img-fluid"
     @if ($photoalbum_content->poster_upload_id) poster="/file/{{$photoalbum_content->poster_upload_id}}" @endif
     oncontextmenu="return false;"
></video>
</body>
</html>
