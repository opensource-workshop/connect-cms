{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 開館カレンダープラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.openingcalendars.openingcalendars_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
<form action="{{url('/')}}/plugin/openingcalendars/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group table-responsive">
        <table class="table table-hover" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th></th>
                <th nowrap>開館カレンダー名</th>
                <th nowrap>詳細</th>
                <th nowrap>作成日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($openingcalendars as $openingcalendar)
            <tr @if ($openingcalendar_frame->openingcalendars_id == $openingcalendar->id) class="active"@endif>
                <td nowrap><input type="radio" value="{{$openingcalendar->bucket_id}}" name="select_bucket"@if ($openingcalendar_frame->bucket_id == $openingcalendar->bucket_id) checked @endif></td>
                <td nowrap>{{$openingcalendar->openingcalendar_name}}</td>
                <td nowrap><button class="btn btn-primary btn-sm" type="button" onclick="location.href='{{url('/')}}/plugin/openingcalendars/editBuckets/{{$page->id}}/{{$frame_id}}/{{$openingcalendar->id}}#frame-{{$frame->id}}'"><i class="far fa-edit"></i><span class="d-none d-xl-inline"> 設定変更</span></button></td>
                <td nowrap>{{$openingcalendar->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <div class="text-center">
        {{ $openingcalendars->links() }}
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i>キャンセル</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i>変更</button>
    </div>
</form>
@endsection
