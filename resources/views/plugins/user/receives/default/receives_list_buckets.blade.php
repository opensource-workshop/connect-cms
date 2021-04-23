{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データ収集設定プラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.receives.receives_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

<form action="/plugin/receives/changeBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group table-responsive">
        <table class="table table-hover" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th></th>
                <th nowrap>データ収集設定名</th>
                <th nowrap>詳細</th>
                <th nowrap>作成日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($receives as $receive)
            <tr @if ($receive_frame->receives_id == $receive->id) class="active"@endif>
                <td nowrap><input type="radio" value="{{$receive->bucket_id}}" name="select_bucket"@if ($receive_frame->bucket_id == $receive->bucket_id) checked @endif></input></td>
                <td nowrap>{{$receive->dataset_name}}</td>
                <td nowrap><button class="btn btn-primary btn-sm" type="button" onclick="location.href='{{url('/')}}/plugin/receives/editBuckets/{{$page->id}}/{{$frame_id}}/{{$receive->id}}'"><i class="far fa-edit"></i><span class="d-none d-xl-inline"> 設定変更</span></button></td>
                <td nowrap>{{$receive->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <div class="text-center">
        {{ $receives->links() }}
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i><span class="d-none d-md-inline"> 変更</span></button>
    </div>
</form>
@endsection
