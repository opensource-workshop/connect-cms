{{--
 * 施設予約データ表示画面（月と週のラッパーテンプレート）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}

    {{-- 必要なデータ揃っているか確認 --}}
    @if (
        // フレームに紐づいた施設予約親データが存在すること
        isset($frame) && $frame->bucket_id &&
        // 施設データが存在すること
        !$facilities->isEmpty() &&
        // 予約項目データが存在すること
        !$columns->isEmpty()
        )

        {{-- タブ表示 --}}
        <ul class="nav nav-tabs">
            <li class="nav-item">
                {{-- 月タブ --}}
                <a href="{{url('/')}}/plugin/reservations/month/{{$page->id}}/{{$frame->id}}/{{ Carbon::now()->format('Ym') }}#frame-{{$frame->id}}" class="nav-link{{ $view_format == ReservationCalendarDisplayType::month ? ' active' : '' }}">月</a>
            </li>
            <li class="nav-item">
                {{-- 週タブ --}}
                <a href="{{url('/')}}/plugin/reservations/week/{{$page->id}}/{{$frame->id}}/{{ Carbon::now()->format('Ym') }}#frame-{{$frame->id}}" class="nav-link{{ $view_format == ReservationCalendarDisplayType::week ? ' active' : '' }}">週</a>
            </li>
        </ul>

        <div id="app">

            @if ($view_format == ReservationCalendarDisplayType::month)

                {{-- 月で表示 --}}
                @include('plugins.user.reservations.default.reservations_calendar_month')

            @elseif ($view_format == ReservationCalendarDisplayType::week)

                {{-- 週で表示 --}}
                @include('plugins.user.reservations.default.reservations_calendar_week')

            @endif
            {{-- 予約登録モーダルウィンドウのvueコンポーネント --}}
            <reservations-calendar-add-booking v-show="showContent" v-on:from-child="closeModal" :ymd="ymd">
                {{-- TODO:ここは項目テーブル見て動的に --}}
                {{-- モック項目１ --}}
                <div class="form-group">
                    <label class="col-4 control-label">受付番号</label>
                    <div class="col-4">
                        <input name="" class="form-control" type="text" value="">
                    </div>
                </div>
                {{-- モック項目２ --}}
                <div class="form-group">
                    <label class="col-4 control-label">利用施設</label>
                    <div class="col-8">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="" id="mock_radio_1_1" name="mock_radio_1_1" class="custom-control-input" checked="checked">
                            <label class="custom-control-label" for="mock_radio_1_1">ルームA</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="" id="mock_radio_1_2" name="mock_radio_1_2" class="custom-control-input">
                            <label class="custom-control-label" for="mock_radio_1_2">ルームB</label>
                        </div>
                    </div>
                </div>
                {{-- モック項目３ --}}
                <div class="form-group">
                    <label class="col-4 control-label">利用目的</label>
                    <div class="col-8">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="" id="mock_radio_2_1" name="mock_radio_2_1" class="custom-control-input" checked="checked">
                            <label class="custom-control-label" for="mock_radio_2_1">授業</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="" id="mock_radio_2_2" name="mock_radio_2_2" class="custom-control-input">
                            <label class="custom-control-label" for="mock_radio_2_2">ゼミ</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="" id="mock_radio_2_3" name="mock_radio_2_3" class="custom-control-input">
                            <label class="custom-control-label" for="mock_radio_2_3">その他</label>
                        </div>
                    </div>
                </div>
            </reservations-calendar-add-booking>
        </div>
    
    @else
        {{-- フレームに紐づくコンテンツがない場合、データ登録を促すメッセージを表示 --}}
        <div class="card border-danger">
            <div class="card-body">
                {{-- フレームに紐づく親データがない場合 --}}
                @if (!(isset($frame) && $frame->bucket_id))
                    <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用する施設予約を選択するか、作成してください。</p>
                @endif
                {{-- 施設データがない場合 --}}
                @if ($facilities->isEmpty())
                    <p class="text-center cc_margin_bottom_0">フレームの設定画面から、施設データを作成してください。</p>
                @endif
                {{-- 予約項目データがない場合 --}}
                @if ($columns->isEmpty())
                    <p class="text-center cc_margin_bottom_0">フレームの設定画面から、予約項目データを作成してください。</p>
                @endif
            </div>
        </div>
    @endif

<script>
    new Vue({
        el: '#app',
        data: {
            showContent: false,
            ymd:null,
            reservationColumns:[]
        },
        methods:{
            openModal: function(ymd){
                // axios.get('/plugin/reservations/getReservationColumns/1/1')
                //     .then((res)=>{this.reservationColumns = res.data})
                //     .catch(function (error) {
                //         this.reservationColumns = [];
                //         console.log('ERROR!! happend by Backend.')
                //     });
                // reservationColumns
                this.ymd = ymd;
                this.showContent = true
            },    
            closeModal: function(){
                this.showContent = false
            },
        }
    })
</script>