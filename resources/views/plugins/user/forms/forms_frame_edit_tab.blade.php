{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォームプラグイン
 --}}
@extends('plugins.common.frame_edit_tab')
@section('content_frame_edit_tab')
@if ($action == 'editColumn' || $action == '')
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/forms/editColumn/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">項目設定</a></li>
@else
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/forms/editColumn/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">項目設定</a></li>
@endif
@if ($action == 'editPlugin' || $action == '')
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/forms/editPlugin/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">{{$frame->plugin_name_full}}設定</a></li>
@else
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/forms/editPlugin/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">{{$frame->plugin_name_full}}設定</a></li>
@endif
@if ($action == 'createPlugin')
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/createPlugin/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link active">{{$frame->plugin_name_full}}作成</a></li>
@else
    <li role="presentation" class="nav-item"><a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/createPlugin/{{$page->id}}/{{$frame->id}}#{{$frame->id}}" class="nav-link">{{$frame->plugin_name_full}}作成</a></li>
@endif
@endsection
