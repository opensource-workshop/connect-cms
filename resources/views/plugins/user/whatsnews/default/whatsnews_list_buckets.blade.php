{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 新着情報プラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.whatsnews.whatsnews_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
<form action="{{url('/')}}/plugin/whatsnews/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group table-responsive">
        <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th></th>
                <th nowrap>新着情報名</th>
{{--                <th nowrap>詳細</th> --}}
                <th nowrap>作成日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($whatsnews as $whatsnew)
            <tr @if ($whatsnew_frame->whatsnews_id == $whatsnew->id) class="active"@endif>
                <td nowrap><input type="radio" value="{{$whatsnew->bucket_id}}" name="select_bucket"@if ($whatsnew_frame->bucket_id == $whatsnew->bucket_id) checked @endif></td>
                <td nowrap>{{$whatsnew->whatsnew_name}}</td>
{{--
                <td nowrap><button class="btn btn-primary btn-sm" type="button" onclick="location.href='{{url('/')}}/plugin/whatsnews/editBuckets/{{$page->id}}/{{$frame_id}}/{{$whatsnew->id}}'"><i class="far fa-edit"></i><span class="d-none d-xl-inline"> 設定変更</span></button></td>
--}}
                <td nowrap>{{$whatsnew->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <div class="text-center">
        {{ $whatsnews->links() }}
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更</button>
    </div>
</form>
@endsection
