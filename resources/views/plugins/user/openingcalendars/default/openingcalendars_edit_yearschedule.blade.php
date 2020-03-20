{{--
 * 年間カレンダー編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 開館カレンダープラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
{{-- エラー表示 --}}
@if ($errors)
<div class="alert alert-danger my-3">
    @foreach($errors as $error)
        <i class="fas fa-exclamation-circle"></i>
        {{$error}}<br />
    @endforeach
</div>
@endif

<form action="{{url('/')}}/plugin/openingcalendars/saveYearschedule/{{$page->id}}/{{$frame_id}}/{{$openingcalendar_frame->openingcalendars_id}}" method="POST" class="" name="chenge_yearschedule" enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">現在の年間カレンダー</label>
        <div class="{{$frame->getSettingInputClass()}} d-flex align-items-center">
        @if (isset($openingcalendar_frame->yearschedule_uploads_id))
            <a href="{{url('/')}}/file/{{$openingcalendar_frame->yearschedule_uploads_id}}" target="_blank" rel="noopener">{{$openingcalendar_frame->client_original_name}}</a>
        @else
            <span class="text-primary">年間カレンダーはアップロードされていません。</span>
        @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">年間カレンダーPDF</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="file" name="yearschedule_pdf" class="form-control-file" id="File">
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">年間カレンダーを削除</label>
        <div class="{{$frame->getSettingInputClass()}} d-flex align-items-center">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="delete_yearschedule_pdf" value="1" class="custom-control-input" id="delete_yearschedule_pdf">
                <label class="custom-control-label" for="delete_yearschedule_pdf">削除する。</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">年間カレンダーのリンク文字列</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="yearschedule_link_text" value="{{old('yearschedule_link_text', $openingcalendar_frame->yearschedule_link_text)}}" class="form-control">
        </div>
    </div>

    <div class="form-group text-center mt-3">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> アップロード</span></button>
    </div>

</form>
@endsection
