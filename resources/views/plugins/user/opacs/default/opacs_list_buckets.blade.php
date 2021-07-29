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

<!-- フラッシュメッセージ -->
@if (session('change_buckets_opacs'))
    <div class="alert alert-info" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('change_buckets_opacs') }}
    </div>
@endif

<form action="{{url('/')}}/plugin/opacs/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group">
        <table class="table table-hover {{$frame->getSettingTableClass()}}">
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
                <td class="d-table-cell"><input type="radio" value="{{$opac->bucket_id}}" name="select_bucket"@if ($opac_frame->bucket_id == $opac->bucket_id) checked @endif></td>
                <td><span class="{{$frame->getSettingCaptionClass()}}">OPAC名：</span>{{$opac->opac_name}}</td>
                <td>
                    <span class="{{$frame->getSettingCaptionClass()}}">詳細：</span>
                    <a class="btn btn-success btn-sm" href="{{url('/')}}/plugin/opacs/editBuckets/{{$page->id}}/{{$frame_id}}/{{$opac->id}}#frame-{{$frame->id}}">
                        <i class="far fa-edit"></i> 設定変更
                    </a>
                </td>
                <td><span class="{{$frame->getSettingCaptionClass()}}">作成日：</span>{{$opac->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $opacs, 'frame' => $frame, 'aria_label_name' => $frame->plugin_name_full . '選択', 'class' => 'form-group'])

    <div class="text-center">
        <a class="btn btn-secondary mr-2" href="{{URL::to($page->permanent_link)}}"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span></a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示OPAC変更</button>
    </div>
</form>
@endsection
