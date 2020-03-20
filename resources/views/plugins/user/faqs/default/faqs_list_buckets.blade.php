{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category FAQプラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.faqs.faqs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
<form action="{{url('/')}}/plugin/faqs/changeBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group">
        <table class="table table-hover {{$frame->getSettingTableClass()}}">
        <thead>
            <tr>
                <th></th>
                <th>FAQ名</th>
                <th>作成日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($faqs as $faq)
            <tr @if ($faq_frame->bucket_id == $faq->bucket_id) class="cc-active-tr"@endif>
                <td>
                    <input type="radio" value="{{$faq->bucket_id}}" name="select_bucket"@if ($faq_frame->bucket_id == $faq->bucket_id) checked @endif></input>
                    <span class="{{$frame->getSettingCaptionClass()}}">{{$faq->faq_name}}</span>
                </td>
                <td class="{{$frame->getNarrowDisplayNone()}}">{{$faq->faq_name}}</td>
                <td>{{$faq->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <div class="text-center">
        {{ $faqs->links() }}
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示FAQ変更</button>
    </div>
</form>
@endsection
