{{--
 * リンクリスト画面 カテゴリタブ切替テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category リンクリストプラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@if (isset($frame) && $frame->bucket_id)
    {{-- バケツあり --}}
@else
    {{-- バケツなし --}}
    @can('frames.edit',[[null, null, null, $frame]])
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">{{ __('messages.empty_bucket', ['plugin_name' => $frame->plugin_name_full]) }}</p>
        </div>
    </div>
    @endcan
@endif

{{-- 新規登録 --}}
@can('posts.create',[[null, 'linklists', $buckets]])
    @if (isset($frame) && $frame->bucket_id)
        <div class="row">
            <p class="text-right col-12">
                {{-- 新規登録ボタン --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/linklists/edit/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}'"><i class="far fa-edit"></i> 新規登録</button>
            </p>
        </div>
    @endif
@endcan

{{-- リンク表示 --}}
@if (isset($posts))
    
    @php
        // 必要変数定義
        $list_start_tag = null;
        $list_end_tag = null;
        $category_lists = array();
        $first_flag = true;
        
        // 表示タイプに合わせて、出力タグを変更
        if ($plugin_frame->type == 0) {      // マークナシ
            $list_start_tag = '<ul style="list-style-type: none;">';
            $list_end_tag   = '</ul>';
        }
        elseif ($plugin_frame->type == 1) {  // ●
            $list_start_tag = '<ul style="list-style-type: disc;">';
            $list_end_tag   = '</ul>';
        }
        elseif ($plugin_frame->type == 2) {  // ○
            $list_start_tag = '<ul style="list-style-type: circle;">';
            $list_end_tag   = '</ul>';
        }
        elseif ($plugin_frame->type == 3) {  // ■
            $list_start_tag = '<ul style="list-style-type: square;">';
            $list_end_tag   = '</ul>';
        }
        elseif ($plugin_frame->type == 4) {  // 1
            $list_start_tag = '<ol style="list-style-type: decimal;">';
            $list_end_tag   = '</ol>';
        }
        elseif ($plugin_frame->type == 5) {  // a
            $list_start_tag = '<ol style="list-style-type: lower-latin;">';
            $list_end_tag   = '</ol>';
        }
        elseif ($plugin_frame->type == 6) { // A
            $list_start_tag = '<ol style="list-style-type: upper-latin;">';
            $list_end_tag   = '</ol>';
        }
        elseif ($plugin_frame->type == 7) {  // i
            $list_start_tag = '<ol style="list-style-type: lower-roman;">';
            $list_end_tag   = '</ol>';
        }
        elseif ($plugin_frame->type == 8) {  // I 
            $list_start_tag = '<ol style="list-style-type: upper-roman;">';
            $list_end_tag   = '</ol>';
        }
    @endphp
    
    {{-- 登録リンクごとのループ ※カテゴリの表示順、リンクリストの表示順、登録日時順にソートされている --}}
    @foreach($posts as $post)
        {{-- カテゴリごとにデータをデータの組み換える --}}
        @php
            if ($post->category_view_flag == 0) {
                // カテゴリ非表示 or カテゴリ登録なし
                if (array_key_exists( 'none', $category_lists ) == false) {
                    $category_lists[ 'none' ] = array( $post );
                }
                else {
                    $category_lists[ 'none' ][] = $post;
                }
            }
            elseif (array_key_exists( $post->plugin_categories_categories_id, $category_lists) == false ) {
                // まだカテゴリIDが登録されていない場合には、設定する
                $category_lists[ $post->plugin_categories_categories_id ] = array( $post );
            }
            else {
                // カテゴリ登録済みの場合には、リストに追加
                $category_lists[ $post->plugin_categories_categories_id ][] = $post;
            }
        @endphp
    @endforeach
    
    {{-- タグ書き出し --}}
    <div class="linktabs">
        
        {{-- タブ部分の書き出し --}}
        <ul class="nav nav-tabs mb-3">
            @foreach( $category_lists as $category_id => $category_list )
                <li class="nav-item">
                    <a href="#linktab_contents_{{$frame_id}}_{{$category_id}}" class="nav-link @if ($first_flag == true) active @endif" data-toggle="tab">
                        @if ($category_id == 'none')
                            {{-- カテゴリがない場合には、仮の[none]を付与する。カテゴリを利用していない場合には、基本このテンプレートは利用しないものと思われる --}}
                            none
                        @else
                            {{$category_list[0]->category}}
                        @endif
                    </a>
                </li>
                @php
                    $first_flag = false;
                @endphp
            @endforeach
        </ul>
        
        {{-- カテゴリごとのリンクリストの書き出し --}}
        <div class="tab-content">
            @php
                $first_flag = true;
            @endphp
            @foreach( $category_lists as $category_id => $category_list )
                {{-- idには、同一ページ内に別リンクリストタブ形式が配置されても被らないように、frame_idを付与する --}}
                <div id="linktab_contents_{{$frame_id}}_{{$category_id}}" class="tab-pane @if ($first_flag == true) active @endif">
                {!! $list_start_tag !!}
                @foreach( $category_list as $linklistdata )
                    <li>
                        @can('posts.update',[[null, 'linklists', $buckets]])
                            <a href="{{url('/')}}/plugin/linklists/edit/{{$page->id}}/{{$frame_id}}/{{$linklistdata->id}}#frame-{{$frame_id}}"><i class="far fa-edit"></a></i>
                        @endcan
                        @if (empty($linklistdata->url))
                            {{$linklistdata->title}}
                        @else
                            @if ($linklistdata->target_blank_flag)
                                <a href="{{$linklistdata->url}}" target="_blank">{{$linklistdata->title}}</a>
                            @else
                                <a href="{{$linklistdata->url}}">{{$linklistdata->title}}</a>
                            @endif
                        @endif
                        @if (!empty($linklistdata->description))
                            <br /><small class="text-muted">{!!nl2br(e($linklistdata->description))!!}</small>
                        @endif
                    </li>
                @endforeach
                {!! $list_end_tag !!}
                </div>
                @php
                    $first_flag = false;
                @endphp
            @endforeach
        </div>
    </div>
    
    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $posts, 'frame' => $frame, 'aria_label_name' => $linklist->name, 'class' => 'mt-3'])

@endif

@endsection
