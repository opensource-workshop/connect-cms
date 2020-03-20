{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.opacs.opacs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
<form action="{{url('/')}}/plugin/opacs/changeBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group">
        <table class="table table-hover" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th></th>
                <th>OPAC名</th>
                <th>詳細</th>
                <th>作成日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($opacs as $opac)
            <tr @if ($opac_frame->opacs_id == $opac->id) class="active"@endif>
                <td><input type="radio" value="{{$opac->bucket_id}}" name="select_bucket"@if ($opac_frame->bucket_id == $opac->bucket_id) checked @endif></input></td>
                <td>{{$opac->opac_name}}</td>
                <th><button class="btn btn-primary btn-sm" type="button" onclick="location.href='{{url('/')}}/plugin/opacs/editBuckets/{{$page->id}}/{{$frame_id}}/{{$opac->id}}'"><i class="far fa-edit"></i> OPAC設定変更</button></th>
                <td>{{$opac->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <div class="text-center">
        {{ $opacs->links() }}
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-3" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i> キャンセル</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示OPAC変更</button>
    </div>
</form>
@endsection
