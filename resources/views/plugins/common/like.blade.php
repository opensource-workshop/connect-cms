{{--
 * いいねテンプレート
 *
 * @param $use_like         いいねボタンを使う
 * @param $like_button_name いいねボタン名
 * @param $contents_id      記事ID
 * @param $like_id          いいねID
 * @param $like_count       いいね数
 * @param $like_users_id    この記事の自分のいいね済みlike_users_id
 *
 * // 暗黙で利用
 * @param $frame
 * @param $page
--}}
@php
    $like_button_name = $like_button_name ?? Like::like_button_default;
    $like_id = $like_id ?? null;
    $like_count = $like_count ?? 0;

    if (Like::isLiked($like_id, $like_users_id)) {
        // いいね済み
        $is_disabled = 'true';
    } else {
        // 未いいね
        $is_disabled = 'false';
    }
@endphp

@if ($use_like)
    {{-- いいねボタン --}}
    <span id="app_like_{{$frame->id}}_{{$contents_id}}">
        <button class="btn btn-sm btn-link border font-weight-light" v-bind:disabled="is_disabled" v-on:click="like">
            {{-- JS描画でカウント数の初期表示がちょっと遅く一瞬消えるため、laraveの変数表示＆v-htmlでバインディング --}}
            {{$like_button_name}} <span class="badge badge-light font-weight-light" v-html="like_count">{{$like_count}}</span>
        </button>
    </span>

    <script>
        const app_like_{{$frame->id}}_{{$contents_id}} = new Vue({
            el: "#app_like_{{$frame->id}}_{{$contents_id}}",
            data: function() {
                return {
                    like_count: {{ $like_count }},
                    is_disabled: {{ $is_disabled }}
                }
            },
            methods: {
                like: function () {
                    let self = this;
                    // 非同期
                    axios.get("{{url('/')}}/json/{{$frame->plugin_name}}/saveLikeJson/{{$page->id}}/{{$frame_id}}/{{$contents_id}}/")
                        .then(function(res){
                            self.like_count = res.data;
                            self.is_disabled = true;
                            // console.log(res.data);
                        })
                        .catch(function (error) {
                            console.log(error);
                        });
                }
            },
        });
    </script>
@endif
