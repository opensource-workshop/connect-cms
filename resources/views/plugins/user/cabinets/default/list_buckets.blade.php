{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 石垣　佑樹 <ishigaki@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category キャビネット・プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.cabinets.cabinets_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if ($plugin_buckets->isEmpty())
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i> {{ __('messages.empty_bucket_setting', ['plugin_name' => 'キャビネット']) }}
    </div>
@else
    <form action="{{url('/')}}/redirect/plugin/cabinets/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
        {{ csrf_field() }}
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/cabinets/listBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
        <div class="form-group">
            <table class="table table-hover {{$frame->getSettingTableClass()}}">
                <thead>
                    <tr>
                        <th></th>
                        <th>キャビネット名</th>
                        <th>作成日</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plugin_buckets as $plugin_bucket)
                        <tr @if ($plugin_bucket->bucket_id == $frame->bucket_id) class="cc-active-tr"@endif>
                            <td class="d-table-cell">
                                <input type="radio" value="{{$plugin_bucket->bucket_id}}" name="select_bucket"@if ($plugin_bucket->bucket_id == $frame->bucket_id) checked @endif>
                            </td>
                            <td><span class="{{$frame->getSettingCaptionClass()}}">キャビネット名：</span>{{$plugin_bucket->name}}</td>
                            <td><span class="{{$frame->getSettingCaptionClass()}}">作成日：</span>{{$plugin_bucket->created_at->format('Y/m/d H:i')}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ページング処理 --}}
        @include('plugins.common.user_paginate', ['posts' => $plugin_buckets, 'frame' => $frame, 'aria_label_name' => $frame->plugin_name_full . '選択', 'class' => 'form-group'])

        <div class="text-center">
            <a href="{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}" class="btn btn-secondary mr-2">
                <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
            </a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示キャビネット変更</button>
        </div>
    </form>
@endif
@endsection
