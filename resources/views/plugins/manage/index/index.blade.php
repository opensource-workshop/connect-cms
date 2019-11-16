{{--
 * 管理画面のトップのメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 管理画面インデックス
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

@if($rss_xml)
<div class="card">
    <div class="card-header">Connect-CMS 更新情報等</div>
    <div class="list-group">
    @foreach($rss_xml->channel->item as $rss_item)
        <div class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <a href="{{$rss_item->link}}" >{{$rss_item->title}}</a>
                @php
                    $news_date = date('Y-m-d', strtotime($rss_item->pubDate))
                @endphp
                <small>{{$news_date}}</small>
            </div>
            <p class="mb-1">{{$rss_item->description}}</p>
        </div>
    @endforeach
    </div>
</div>
@else
<div class="card">
    <div class="card-header">Connect-CMS 更新情報等</div>
    <div class="list-group">
        <div class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                @foreach($errors as $error)
                {{$error}}
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
@endsection
