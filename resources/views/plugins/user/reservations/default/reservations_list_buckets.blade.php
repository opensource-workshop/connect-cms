{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}

<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.reservations.reservations_frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

<form action="/plugin/reservations/changeBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group">
        <table class="table table-hover" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th></th>
                <th>施設予約名</th>
                <th>施設名</th>
                <th></th>
                <th>作成日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($reservations as $reservation)
            <tr @if ($reservation_frame->reservations_id == $reservation->id) class="active"@endif>
                <td><input type="radio" value="{{$reservation->bucket_id}}" name="select_bucket"@if ($reservation_frame->bucket_id == $reservation->bucket_id) checked @endif></input></td>
                <td>{{$reservation->reservation_name}}</td>
                <td></td>
                <td><button class="btn btn-primary btn-sm" type="button" onclick="location.href='{{url('/')}}/plugin/reservations/editBuckets/{{$page->id}}/{{$frame_id}}/{{$reservation->id}}'"><i class="far fa-edit"></i> 設定変更</button></td>
                <td>{{$reservation->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <div class="text-center">

        {{ $reservations->links() }}

    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i> キャンセル</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示する施設予約を変更</button>
    </div>
</form>
