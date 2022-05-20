{{--
 * データベース検索画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース検索プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@php
    // 表示項目
    $view_columns = explode(',', $databasesearches->view_columns);
@endphp

<table class="table table-bordered">
    <caption class="sr-only">{{$databasesearches->databasesearches_name}}</caption>
    <thead>
        <tr>
        @foreach($view_columns as $view_column)
            <th>{{$view_column}}</th>
        @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($inputs_ids as $input_id)
        <tr>
            @foreach($view_columns as $view_column)
            <td>
                @php
                    // 表示項目
                    $view_col = $input_cols->where('databases_inputs_id', $input_id->databases_inputs_id)
                                            ->where('column_name', trim($view_column))
                                            ->first();
                @endphp
                @if($view_col)
                    @if($loop->first)
                        <a href="{{url('/')}}/plugin/databases/detail/{{$input_id->page_id}}/{{$input_id->frames_id}}/{{$input_id->databases_inputs_id}}#frame-{{$input_id->frames_id}}">
                            @if($view_col->value)
                                {{$view_col->value}}
                            @else
                                (無題)
                            @endif
                        </a>
                    @else
                        @if ($view_col->column_type == DatabaseColumnType::wysiwyg)
                            {!! $view_col->value !!}
                        @else
                            {{ $view_col->value }}
                        @endif
                    @endif
                @endif
            @endforeach
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ページング処理 --}}
@include('plugins.common.user_paginate', ['posts' => $inputs_ids, 'frame' => $frame, 'aria_label_name' => $databasesearches->databasesearches_name])

@endsection
