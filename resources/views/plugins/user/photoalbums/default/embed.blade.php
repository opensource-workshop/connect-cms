{{--
 * フォトアルバム・埋め込み画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body style="margin: 0px; padding: 0px;">
<video controls controlsList="nodownload"
     src="{{url('/')}}/file/{{$photoalbum_content->upload_id}}"
     id="video"
     style="width: 100%; height: 100%; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
     class="img-fluid"
     @if ($photoalbum_content->poster_upload_id) poster="{{url('/')}}/file/{{$photoalbum_content->poster_upload_id}}" @endif
     oncontextmenu="return false;"
></video>
<script>
    (function () {
        // Count media play once per page view.
        var countedIds = {};

        // Extract upload id from /file/{id} URLs.
        function extractFileId(src) {
            if (!src) {
                return null;
            }
            var match = src.match(/\/file\/(\d+)/);
            return match ? match[1] : null;
        }

        // Handle media play and send count once per upload id.
        function onMediaPlay(event) {
            var target = event.target;
            if (!target || !target.tagName) {
                return;
            }

            var tag = target.tagName.toLowerCase();
            if (tag !== 'video' && tag !== 'audio') {
                return;
            }

            var src = target.currentSrc || target.src || '';
            var id = extractFileId(src);
            if (!id || countedIds[id]) {
                return;
            }
            countedIds[id] = true;

            var tokenMeta = document.querySelector('meta[name=\"csrf-token\"]');
            var token = tokenMeta ? tokenMeta.getAttribute('content') : '';
            var origin = window.location.origin || (window.location.protocol + '//' + window.location.host);

            fetch(origin + '/api/uploads/play/' + id, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            }).catch(function () {});
        }

        document.addEventListener('play', onMediaPlay, true);
    })();
</script>
</body>
</html>
