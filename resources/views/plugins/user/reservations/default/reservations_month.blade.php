{{--
 * 施設予約画面テンプレート。
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}

@if (isset($frame) && $frame->bucket_id)
    月表示テンプレート<br />
    <br />
    <a href="{{url('/')}}/plugin/reservations/week/{{$page->id}}/{{$frame->id}}/123#frame-{{$frame->id}}">週表示へ</a>
@else
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用する施設予約を選択するか、作成してください。</p>
        </div>
    </div>
@endif

