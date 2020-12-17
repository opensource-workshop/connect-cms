{{--
 * ログ管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ログ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.log.log_tab')
    </div>

    <div class="card-body">
        <div class="accordion" id="search_accordion">
            <div class="card">
                <button class="btn btn-link p-0 text-left collapsed" type="button" data-toggle="collapse" data-target="#search_collapse" aria-expanded="false" aria-controls="search_collapse">
                    <div class="card-header" id="app_log_search_condition">
                        絞り込み条件 <i class="fas fa-angle-down"></i>
                   </div>
                </button>
                @if (Session::has('app_log_search_condition'))
                <div id="search_collapse" class="collapse show" aria-labelledby="app_log_search_condition" data-parent="#search_accordion">
                @else
                <div id="search_collapse" class="collapse" aria-labelledby="app_log_search_condition" data-parent="#search_accordion">
                @endif
                    <div class="card-body">

                        <form name="form_search" id="form_search" class="form-horizontal" method="post" action="{{url('/')}}/manage/log/search">
                            {{ csrf_field() }}

                            {{-- ログインID --}}
                            <div class="form-group row">
                                <label for="app_log_search_condition_userid" class="col-md-3 col-form-label text-md-right">ログインID</label>
                                <div class="col-md-9">
                                    <input type="text" name="app_log_search_condition[userid]" id="app_log_search_condition_userid" value="{{Session::get('app_log_search_condition.userid')}}" class="form-control">
                                </div>
                            </div>

                            {{-- ログ種別 --}}
                            <div class="form-group row">
                                <label for="app_log_search_condition_type" class="col-md-3 text-md-right">ログ種別</label>
                                <div class="col-md-9">
                                    <div class="custom-control custom-control-inline custom-checkbox">
                                        <input name="app_log_search_condition[type_page]" value="1" type="checkbox" class="custom-control-input" id="type_page"@if(Session::get('app_log_search_condition.type_page') == "1") checked @endif>
                                        <label class="custom-control-label" for="type_page">ページ操作</label>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox">
                                        <input name="app_log_search_condition[type_login]" value="1" type="checkbox" class="custom-control-input" id="type_login"@if(Session::get('app_log_search_condition.type_login') == "1") checked @endif>
                                        <label class="custom-control-label" for="type_login">ログイン</label>
                                    </div>
                                    <div class="custom-control custom-control-inline custom-checkbox">
                                        <input name="app_log_search_condition[type_sendmail]" value="1" type="checkbox" class="custom-control-input" id="type_sendmail"@if(Session::get('app_log_search_condition.type_sendmail') == "1") checked @endif>
                                        <label class="custom-control-label" for="type_sendmail">メール送信</label>
                                    </div>
                                </div>
                            </div>

                            {{-- ボタンエリア --}}
                            <div class="form-group text-center">
                                <div class="row">
                                    <div class="mx-auto">
                                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/log/clearSearch')}}'">
                                            <i class="fas fa-times"></i> クリア
                                        </button>
                                        <button type="submit" class="btn btn-primary form-horizontal">
                                            <i class="fas fa-check"></i> 絞り込み
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group table-responsive">
            <table class="table table-hover cc-font-90">
            <thead>
                <tr>
                    <th nowrap>id</th>
                    <th nowrap>日時</th>
                    <th nowrap>ログインID</th>
                    <th nowrap>IPアドレス</th>
                    <th nowrap>プラグイン名</th>
                    <th nowrap>URI</th>
                    <th nowrap>ログ種別</th>
                    <th nowrap>送信先</th>
                    <th nowrap>成否</th>
                    <th nowrap>値など</th>
                </tr>
            </thead>
            <tbody>
            @foreach($app_logs as $app_log)
                <tr>
                    <td nowrap>{{$app_log->id}}</td>
                    <td nowrap>{{$app_log->created_at}}</td>
                    <td nowrap>{{$app_log->userid}}</td>
                    <td nowrap>{{$app_log->ip_address}}</td>
                    <td nowrap>{{$app_log->plugin_name}}</td>
                    <td nowrap>{{$app_log->uri}}</th>
                    <td nowrap>{{$app_log->type}}</td>
                    <td nowrap>{{$app_log->send_address}}</td>
                    <td nowrap>{{$app_log->return_code}}</td>
                    <td nowrap>{{$app_log->value}}</td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>

        {{-- ページング処理 --}}
        <div class="text-center">
            {{ $app_logs->links() }}
        </div>
    </div>
</div>
@endsection
