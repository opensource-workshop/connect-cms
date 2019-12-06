{{--
 * 年間カレンダー編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 開館カレンダープラグイン
 --}}

{{-- エラー表示 --}}
@if ($errors)
<div class="alert alert-danger my-3">
    @foreach($errors as $error)
        <i class="fas fa-exclamation-circle"></i>
        {{$error}}<br />
    @endforeach
</div>
@endif

<form action="/plugin/openingcalendars/saveYearschedule/{{$page->id}}/{{$frame_id}}/{{$openingcalendar_frame->openingcalendars_id}}" method="POST" class="" name="chenge_yearschedule" enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="form-group">
        <label for="nowCarendar">現在の年間カレンダー</label>
        @if (isset($openingcalendar_frame->yearschedule_uploads_id))
            <br /><a href="{{url('/')}}/file/{{$openingcalendar_frame->yearschedule_uploads_id}}" target="_blank" rel="noopener">{{$openingcalendar_frame->client_original_name}}</a>
        @else
            <br /><span class="text-primary">年間カレンダーはアップロードされていません。</span>
        @endif
    </div>

    <div class="form-group">
        <label for="File">年間カレンダーPDF</label>
        <input type="file" name="yearschedule_pdf" class="form-control-file" id="File">
    </div>

    <div class="form-group">
        <label class="control-label">年間カレンダーを削除</label>
        <div class="custom-control custom-checkbox">
            <input type="checkbox" name="delete_yearschedule_pdf" value="1" class="custom-control-input" id="delete_yearschedule_pdf">
            <label class="custom-control-label" for="delete_yearschedule_pdf">削除する。</label>
        </div>
    </div>

    <div class="form-group text-center mt-3">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> アップロード</span></button>
    </div>

</form>

