{{--
 * 新着情報のscriptテンプレート ※app.jsがコンパイル利用できるようになったらVueコンポーネント化する
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 新着情報プラグイン
--}}
<script>
    const app_{{ $frame->id }} = new Vue({
        el: "#app_{{ $frame->id }}",
        data: function() {
            return {
                url: '{{ url('/') }}',
                link_pattern: @json($link_pattern),
                link_base: @json($link_base),
                whatsnewses: [],
                whatsnews_total_count: {{ $whatsnews_total_count }}, // 総件数
                view_posted_at: {{ $whatsnews_frame->view_posted_at }},
                view_posted_name: {{ $whatsnews_frame->view_posted_name }},
                limit: {{ $whatsnews_frame->read_more_fetch_count }},
                offset: {{ $whatsnews->count() }}, // 何件目から取得するか（＝現時点の取得件数）※初期値はサーバから返された一覧件数
                post_detail: @json($frame_configs->where('name', 'post_detail')->first()->value),
                thumbnail: @json($frame_configs->where('name', 'thumbnail')->first()->value),
                thumbnail_size: @json($frame_configs->where('name', 'thumbnail_size')->first()->value),
                border: @json($frame_configs->where('name', 'border')->first()->value)
            }
        },
        methods: {
            searchWhatsnewses: function () {
                let self = this;
                // 非同期通信で追加の一覧を取得
                axios.get("{{url('/')}}/json/whatsnews/indexJson/{{$page->id}}/{{$frame_id}}/?limit=" + this.limit + "&offset=" + this.offset)
                    .then(function(res){
                        // foreach内ではthisでvueインスタンスのwhatsnewsesが参照できない為、tmp_arrに一時的に代入
                        tmp_arr = self.whatsnewses;
                        res.data.forEach(function(obj) {
                            // 取得した差分をループしてtmp_arrに格納
                            tmp_arr.push(obj);
                        });
                        // vueインスタンスのwhatsnewsesに代入
                        this.whatsnewses = tmp_arr;
                    })
                    .catch(function (error) {
                        console.log(error)
                    });
                // offset値をカウントアップ
                this.offset += this.limit;
            }
        },
    });
</script>