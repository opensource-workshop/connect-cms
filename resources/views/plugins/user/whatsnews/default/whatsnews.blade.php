{{--
 * 新着情報表示画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 新着情報プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($whatsnews)
<p class="text-left">
    @if (isset($whatsnews_frame->rss) && $whatsnews_frame->rss == 1)
    <a href="{{url('/')}}/redirect/plugin/whatsnews/rss/{{$page->id}}/{{$frame_id}}/" title="{{$whatsnews_frame->whatsnew_name}}のRSS2.0"><span class="badge badge-info">RSS2.0</span></a>
    @endif
</p>

<div id="{{ $whatsnews_frame->read_more_use_flag == UseType::use ? 'app_' . $frame->id : '' }}">
<div>
    <dl>
    @foreach($whatsnews as $whatsnew)
        {{-- 登録日時、カテゴリ --}}
        @if ($whatsnews_frame->view_posted_at)
        <dt>
            {{(new Carbon($whatsnew->posted_at))->format('Y/m/d')}}
            @if($whatsnew->category)
                <span class="badge cc_category_{{$whatsnew->classname}}">{{$whatsnew->category}}</span>
            @endif
        </dt>
        @endif
        {{-- タイトル＋リンク --}}
        <dd>
            @if ($link_pattern[$whatsnew->plugin_name] == 'show_page_frame_post')
            <a href="{{url('/')}}{{$link_base[$whatsnew->plugin_name]}}/{{$whatsnew->page_id}}/{{$whatsnew->frame_id}}/{{$whatsnew->post_id}}#frame-{{$whatsnew->frame_id}}">
                @if ($whatsnew->post_title)
                    {{$whatsnew->post_title}}
                @else
                    (無題)
                @endif
            </a>
            @endif
        </dd>
        {{-- 投稿者 --}}
        @if ($whatsnews_frame->view_posted_name)
        <dd>
            {{$whatsnew->posted_name}}
        </dd>
        @endif
    @endforeach
    {{-- 「もっと見る」ボタン押下時、非同期で新着一覧をレンダリング ※templateタグはタグとして出力されないタグです。 --}}
    <template v-for="whatsnews in whatsnewses">
        {{-- 登録日時、カテゴリ --}}
        <dt v-if="view_posted_at == 1">
            @{{ moment(whatsnews.posted_at).format('YYYY/MM/DD') }}
            <span v-if="whatsnews.category != null && whatsnews.category != ''" :class="'badge cc_category_' + whatsnews.classname">@{{ whatsnews.category }}</span>
        </dt>
        {{-- タイトル＋リンク --}}
        <dd v-if="link_pattern[whatsnews.plugin_name] == 'show_page_frame_post'">
            <a :href="url + link_base[whatsnews.plugin_name] + '/' + whatsnews.page_id + '/' + whatsnews.frame_id + '/' + whatsnews.post_id + '#frame-' + whatsnews.frame_id">
                <template v-if="whatsnews.post_title == null || whatsnews.post_title == ''">（無題）</template>
                <template v-else>@{{ whatsnews.post_title }}</template>
            </a>
        </dd>
        {{-- 投稿者 --}}
        <dd v-if="view_posted_name == 1">
            @{{ whatsnews.posted_name }}
        </dd>
    </template>
    </dl>
    {{-- ページング処理 --}}
    {{-- @if ($whatsnews_frame->page_method == 1)
        <div class="text-center">
            {{ $whatsnews->links() }}
        </div>
    @endif --}}
        {{-- もっと見るボタン ※取得件数が総件数以下で表示 --}}
        @if ($whatsnews_frame->read_more_use_flag == UseType::use)
            @php
                $btn_color = 'btn-';
                $btn_color .= $whatsnews_frame->read_more_btn_transparent_flag == UseType::use ? 'outline-' : '';
                $btn_color .= $whatsnews_frame->read_more_btn_color_type;
            @endphp
            <div v-if="whatsnews_total_count >= offset" class="text-center">
                <button class="btn {{ $btn_color }} {{ $whatsnews_frame->read_more_btn_type }}" v-on:click="searchWhatsnewses">
                    {{ $whatsnews_frame->read_more_name }}
                </button>
            </div>
        @endif
        {{-- debug用 --}}
        {{-- limit<input type="text" name="limit" value="" v-model="limit"> --}}
        {{-- offset<input type="text" name="offset" value="" v-model="offset"> --}}
</div>

    @if ($whatsnews_frame->read_more_use_flag == UseType::use)
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
                        offset: {{ $whatsnews->count() }} // 何件目から取得するか（＝現時点の取得件数）※初期値はサーバから返された一覧件数
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
    @endif
@endif
@endsection
