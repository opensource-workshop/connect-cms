{{--
 * 詳細表示画面テンプレート（defaultテンプレートをベース）
 * ・項目の部分をdl,dt,ddタグに置き換え
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<div class="container">
    {{-- 行グループ ループ --}}
    @php
        $row_group_count = 1;
        $column_group_count = 1;
    @endphp
    @foreach($group_rows_cols_columns as $group_row_cols_columns)
        <div class="row border-left border-right border-bottom @if($loop->first) border-top @endif {{ "row-group-" . $row_group_count++ }}">
        {{-- 列グループ ループ --}}
        @foreach($group_row_cols_columns as $group_col_columns)
            <div class="col-sm {{ "column-group-" . $column_group_count++ }}">
            {{-- カラム ループ --}}
                <dl>
                @foreach($group_col_columns as $column)
                    @if ($column_group_count == 2 && $loop->first)
                        {{-- 1列目、且つ、一番最初の項目はタイトル項目とする為、強調表示＆表頭の表示なし --}}
                        <h2>
                            @include('plugins.user.databases.default.databases_include_detail_value')
                        </h2>
                    @else
                        {{-- 上記以外は通常表示 --}}
                        @if ($column->label_hide_flag == '0')
                            <dt>{{$column->column_name}}</dt>
                        @endif
                        <dd>
                            @include('plugins.user.databases.default.databases_include_detail_value')
                        </dd>
                    @endif
                @endforeach
                </dl>
            </div>
        @endforeach
        </div>
    @endforeach
</div>

@php
    $database_show_like_detail = FrameConfig::getConfigValueAndOld($frame_configs, DatabaseFrameConfig::database_show_like_detail, ShowType::show);
@endphp
<div class="row mt-2">
    <div class="col-12">
        {{-- いいねボタン --}}
        @include('plugins.common.like', [
            'use_like' => ($database->use_like && $database_show_like_detail),
            'like_button_name' => $database->like_button_name,
            'contents_id' => $inputs->id,
            'like_id' => $inputs->like_id,
            'like_count' => $inputs->like_count,
            'like_users_id' => $inputs->like_users_id,
        ])
    </div>
</div>

@can('role_update_or_approval', [[$inputs, $frame->plugin_name, $buckets]])
<div class="row mt-2">
    <div class="col-12 text-right mb-1">
        {{-- ステータス表示＋ボタン --}}
        @include('plugins.user.databases.default.databases_include_status_and_button', [
            'add_badge_class' => 'align-bottom',
            'input' => $inputs,
        ])
    </div>
</div>
@endcan

{{-- 一覧へ戻る --}}
<div class="row">
    <div class="col-12 text-center mt-3">
        @if(Session::has('page_no.'.$frame_id))
        <a href="{{url('/')}}{{$page->getLinkUrl()}}?frame_{{$frame_id}}_page={{Session::get('page_no.'.$frame_id)}}#frame-{{$frame_id}}">
        @else
        <a href="{{url('/')}}{{$page->getLinkUrl()}}?frame_{{$frame_id}}_page=1#frame-{{$frame_id}}">
        @endif
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="d-none d-sm-inline">{{__('messages.to_list')}}</span></span>
        </a>
    </div>
</div>

@endsection
