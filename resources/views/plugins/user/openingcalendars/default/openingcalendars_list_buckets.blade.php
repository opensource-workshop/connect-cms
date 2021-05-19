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
<form action="{{url('/')}}/plugin/openingcalendars/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST">
    {{ csrf_field() }}

    <div class="form-group">
        <table class="table table-hover {{$frame->getSettingTableClass()}}">
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
                <td class="d-table-cell"><input type="radio" value="{{$openingcalendar->bucket_id}}" name="select_bucket"@if ($openingcalendar_frame->bucket_id == $openingcalendar->bucket_id) checked @endif></td>
                <td><span class="{{$frame->getSettingCaptionClass()}}">開館カレンダー名：</span>{{$openingcalendar->openingcalendar_name}}</td>
                <td>
                    <span class="{{$frame->getSettingCaptionClass()}}">詳細：</span>
                    <a class="btn btn-success btn-sm" href="{{url('/')}}/plugin/openingcalendars/editBuckets/{{$page->id}}/{{$frame_id}}/{{$openingcalendar->id}}#frame-{{$frame->id}}">
                        <i class="far fa-edit"></i> 設定変更
                    </a>
                </td>
                <td><span class="{{$frame->getSettingCaptionClass()}}">作成日：</span>{{$openingcalendar->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <div class="text-center">
        {{ $openingcalendars->fragment('frame-' . $frame_id)->links() }}
    </div>

    <div class="form-group text-center mt-3">
        <a class="btn btn-secondary mr-3" href="{{URL::to($page->permanent_link)}}"><i class="fas fa-times"></i> キャンセル</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i>表示開館カレンダー変更</button>
    </div>
</form>
@endsection
