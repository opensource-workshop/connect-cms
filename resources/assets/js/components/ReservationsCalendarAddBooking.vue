<template>
    <div>
        <!-- <div id="overlay" v-on:click="clickEvent"> -->
        <div id="overlay">
            <div id="content" class="card">
                <div class="card-header">
                    予約登録<br>※開発中です。各項目、ボタンは機能しません。レイアウト等は変わる可能性がございます。
                </div>
                <div class="card-body">
                    <form>
                        <!-- 可変項目 -->
                        <slot></slot>
                        <hr>
                        <!-- 予約日 -->
                        <div class="form-group">
                            <label class="col-4 control-label">予約日</label>
                    
                            <div class="col-8 input-group date" id="reservation_date">
                                <input type="text" name="reservation_date" v-model="ymd" class="form-control col-md-4" readonly>
                            </div>
                        </div>
                        <!-- 予約開始日時 -->
                        <div class="form-group">
                            <label class="col-4 control-label">開始時間</label>
                    
                            <div class="col-8 input-group date" id="start_datetime" data-target-input="nearest">
                                <input type="text" name="start_datetime" value="" class="form-control datetimepicker-input  col-md-4" data-target="#start_datetime" readonly>
                                <div class="input-group-append" data-target="#start_datetime" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>

                        <!-- 予約終了日時 -->
                        <div class="form-group">
                            <label class="col-4 control-label">終了時間</label>
                    
                            <div class="col-8 input-group date" id="end_datetime" data-target-input="nearest">
                                <input type="text" name="end_datetime" value="" class="form-control datetimepicker-input  col-md-4" data-target="#end_datetime" readonly>
                                <div class="input-group-append" data-target="#end_datetime" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button class="btn btn-secondary" v-on:click="clickEvent">
                                <i class="fas fa-times"></i> キャンセル
                            </button>
                            <button class="btn btn-primary" v-on:click="clickEvent">
                                <i class="fas fa-check"></i> 登録
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props:[
            'ymd'
        ],
        methods :{
            /**
             * 呼び出し元へイベント送出してモーダルを閉じる
             */
            clickEvent: function(){
                this.$emit('from-child')
            }
        }
    }
    /**
     * カレンダーボタン押下時の処理
     */
    $(function () {
        $('#start_datetime').datetimepicker({
            tooltips: {
                close: '閉じる',
                pickHour: '時間を取得',
                incrementHour: '時間を増加',
                decrementHour: '時間を減少',
                pickMinute: '分を取得',
                incrementMinute: '分を増加',
                decrementMinute: '分を減少',
                pickSecond: '秒を取得',
                incrementSecond: '秒を増加',
                decrementSecond: '秒を減少',
                togglePeriod: '午前/午後切替',
                selectTime: '時間を選択'
            },
            ignoreReadonly: true,
            locale: 'ja',
            sideBySide: true,
            format: 'HH:mm'
        });
    });
    $(function () {
        $('#end_datetime').datetimepicker({
            tooltips: {
                close: '閉じる',
                pickHour: '時間を取得',
                incrementHour: '時間を増加',
                decrementHour: '時間を減少',
                pickMinute: '分を取得',
                incrementMinute: '分を増加',
                decrementMinute: '分を減少',
                pickSecond: '秒を取得',
                incrementSecond: '秒を増加',
                decrementSecond: '秒を減少',
                togglePeriod: '午前/午後切替',
                selectTime: '時間を選択'
            },
            ignoreReadonly: true,
            locale: 'ja',
            sideBySide: true,
            format: 'HH:mm'
        });
    });

</script>
<style>
    #content{
        z-index:10;
        width:50%;
        /* padding: 1em; */
        background:#fff;
    }

    #overlay{
        /*　要素を重ねた時の順番　*/

        z-index:1;

        /*　画面全体を覆う設定　*/
        position:fixed;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background-color:rgba(0,0,0,0.5);

        /*　画面の中央に要素を表示させる設定　*/
        display: flex;
        align-items: center;
        justify-content: center;

    }
</style>