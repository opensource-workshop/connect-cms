{{--
 * 編集画面tabテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォームプラグイン
 --}}
@extends('plugins.common.frame_edit_tab')
@section('content_frame_edit_tab')
<li role="presentation"@if ($action == 'editColumn' || $action == '') class="active"@endif><a href="{{url('/')}}/plugin/forms/editColumn/{{$page->id}}/{{$frame->id}}#{{$frame->id}}">項目設定</a></li>
<li role="presentation"@if ($action == 'editPlugin' || $action == '') class="active"@endif><a href="{{url('/')}}/plugin/forms/editPlugin/{{$page->id}}/{{$frame->id}}#{{$frame->id}}">登録フォーム設定</a></li>
<li role="presentation"@if ($action == 'createPlugin') class="active"@endif><a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/createPlugin/{{$page->id}}/{{$frame->id}}#{{$frame->id}}">{{$frame->plugin_name_full}}新規作成</a></li>
@endsection
