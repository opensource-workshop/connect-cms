{{--
 * データベース検索画面テンプレート（カード４タイプ）
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牧野　可也子 <makino@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース検索プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@php
    // 表示項目
    $view_columns = explode(',', $databasesearches->view_columns);
@endphp

<article class="clearfix">
    <div class="row">
        @foreach($inputs_ids as $input_id)
            @if (isset($is_template_col_3))
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 p-2 dbsearch_card">
            @else
            <div class="col-12 col-sm-6 col-md-6 col-lg-3 p-2 dbsearch_card">
            @endif

                <div class="dbsearch_card_data">
                    <a href="{{url('/')}}/plugin/databases/detail/{{$input_id->page_id}}/{{$input_id->frames_id}}/{{$input_id->databases_inputs_id}}#frame-{{$input_id->frames_id}}" style="text-decoration: none; color: initial;">
                        @foreach($view_columns as $view_column)
                            @php
                            // 表示項目
                            $view_col = $input_cols->where('databases_inputs_id', $input_id->databases_inputs_id)
                                                    ->where('column_name', trim($view_column))
                                                    ->first();
                            @endphp

                            @if($view_col)
                                <div class="dbsearch_col_{{$view_col->databases_columns_id}}" >
                                        @if ($view_col->column_type == DatabaseColumnType::wysiwyg)
                                            {{-- wysiwygエディタ項目の場合 --}}
                                            <span class="column_title">{{$view_column}}：</span><span class="column_value">{!! $view_col->value !!}</span>
                                        @elseif ($view_col->column_type == DatabaseColumnType::image)
                                            {{-- 画像項目の場合 --}}
                                            <img class="img-fluid" src="{{url('/')}}/file/{{$view_col->value}}">
                                        @elseif ($view_col->column_type == DatabaseColumnType::date)
                                            {{-- 日付項目の場合 --}}
                                            {{-- TODO：NC2から移行されてきたデータが「yyyy-mm-dd 00:00:00」で保存されてしまっている
                                                 移行プログラムが修正されるまでは、データフォーマット処理を入れたままとする。issues:1155 --}}
                                            <p><span class="column_title">{{$view_column}}：</span><span class="column_value">@php echo date('Y/m/d', strtotime($view_col->value)) @endphp</span></p>
                                        @else
                                            <p><span class="column_title">{{$view_column}}：</span><span class="column_value">{{$view_col->value}}</span></p>
                                        @endif
                                </div>
                            @endif

                        @endforeach
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</article>

{{-- ページング処理 --}}
<div class="dbsearch_paging">
    @include('plugins.common.user_paginate', ['posts' => $inputs_ids, 'frame' => $frame, 'aria_label_name' => $databasesearches->databasesearches_name])
</div>

@endsection
