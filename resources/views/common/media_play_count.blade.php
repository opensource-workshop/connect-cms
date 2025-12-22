{{--
 * メディア再生回数カウント用スクリプト
 *
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 共通
 --}}
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

            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            var token = tokenMeta ? tokenMeta.getAttribute('content') : '';
            var baseUrl = @json(url('/'));

            fetch(baseUrl + '/api/uploads/play/' + id, {
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
