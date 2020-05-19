{{--
* 詳細表示画面テンプレート。
*
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>, よたか <info@hanamachi.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @copyright Hanamachi All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base')
@section("plugin_contents_$frame->id")
@php $row_flag = 0; @endphp

    @can("role_article")
        <div class="row">
            <div class="col-12 text-right mb-1">
                <button type="button" class="btn btn-success btn-sm" onclick="location.href='{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}/{{$inputs->id}}'">
                    <i class="far fa-edit"></i> 編集
                </button>
            </div>
        </div>
    @endcan

    <div class="db-default-detail">
        @foreach($columns as $column)
            @if($column->detail_hide_flag == 0)

                {{-- 項目表示 --}}
                @if(!$loop->first)
                    @php
                        $tmp_classes = array();
                        $tmp_class = '';
                        switch($column->column_type){ //データのタイプを取得してクラスを指定する
                            case 'file':
                            case 'image':
                            case 'video':
                                $tmp_classes[] = 'type-'.$column->column_type;
                                break;
                        }
                        if($column->classname){
                            $tmp_classes[] = $column->classname;
                        }
                        if(count($tmp_classes)){
                            $tmp_class = implode(' ', $tmp_classes);
                            $tmp_class = ' class="'.$tmp_class.'"';
                        }
                    @endphp
                    <dl{!!$tmp_class!!}>
                        <dt>{{$column->column_name}}</dt>
                        <dd>
                            @if($column->classname == 'db-url')
                                <a href="@include('plugins.user.databases.default.databases_include_detail_value')" target="_new">
                                    @include('plugins.user.databases.default.databases_include_detail_value')
                                </a>
                            @else
                                @include('plugins.user.databases.default.databases_include_detail_value')
                            @endif
                        </dd>
                    </dl>

                {{-- 最初の項目をデータのタイトルにする --}}
                @else
                    @if(!$column->classname)<h2>
                    @else<h2 class="{{$column->classname}}">@endif
                        @include('plugins.user.databases.default.databases_include_detail_value')
                    </h2>
                @endif
            @endif
        @endforeach
    </div>

    @can("role_article")
        <div class="row">
            <div class="col-12 text-right mb-1">
                <button type="button" class="btn btn-success btn-sm" onclick="location.href='{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}/{{$inputs->id}}'">
                    <i class="far fa-edit"></i> 編集
                </button>
            </div>
        </div>
    @endcan

    {{-- 一覧へ戻る --}}
    <div class="row">
        <div class="col-12 text-center mt-3">
            @if(Session::has('page_no.'.$frame_id))
                <a href="{{url('/')}}{{$page->getLinkUrl()}}?page={{Session::get('page_no.'.$frame_id)}}">
            @else
                <a href="{{url('/')}}{{$page->getLinkUrl()}}">
            @endif
                <span class="btn btn-info">
                    <i class="fas fa-list"></i>
                    <span class="hidden-xs">{{__('messages.to_list')}}</span>
                </span>
            </a>
        </div>
    </div>
@endsection
