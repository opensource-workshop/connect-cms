{{--
 * 詳細表示画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<table class="table table-bordered">
    @foreach($columns as $column)
    @if($column->detail_hide_flag == 0)
    <tr>
        <th style="background-color: #e9ecef;" nowrap>{{$column->column_name}}</th>
        <td class="{{$column->classname}}">
            @include('plugins.user.databases.default.databases_include_detail_value')
        </td>
    </tr>
    @endif
    @endforeach
</table>

@can('posts.update', [[$inputs, $frame->plugin_name, $buckets]])
<div class="row">
    <div class="col-12 text-right mb-1">
        <button type="button" class="btn btn-success btn-sm" onclick="location.href='{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}/{{$inputs->id}}#frame-{{$frame_id}}'">
            <i class="far fa-edit"></i> 編集
        </button>
    </div>
</div>
@endcan

{{-- 一覧へ戻る --}}
<div class="row">
    <div class="col-12 text-center mt-3">
        @if(Session::has('page_no.'.$frame_id))
        <a href="{{url('/')}}{{$page->getLinkUrl()}}?frame_{{$frame_id}}_page={{Session::get('page_no.'.$frame_id)}}#frame-{{$frame_id}}">
        @else
        <a href="{{url('/')}}{{$page->getLinkUrl()}}#frame-{{$frame_id}}">
        @endif
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">{{__('messages.to_list')}}</span></span>
        </a>
    </div>
</div>

@endsection
