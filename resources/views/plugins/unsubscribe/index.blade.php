{{--
 * メール配信管理
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メール配信管理
--}}
@php
use App\Models\Common\Unsubscriber;
@endphp
@extends('layouts.app')

@section('content')
<main class="container-fluid mt-3" role="main">
    <div class="card">
        <div class="card-header">メール配信管理</div>
        <div class="card-body">
            {{-- 全エラーメッセージ表示 --}}
            @include('plugins.common.errors_all', ['class' => 'form-group'])

            {{-- 登録後メッセージ表示 --}}
            @include('plugins.common.flash_message')

            <form method="POST" action="{{url('/unsubscribe/save')}}">
                {{ csrf_field() }}

                @foreach ($plugins as $plugin)
                    {{-- メール配信設定 --}}
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label text-md-right pt-0">{{$plugin->plugin_name_full}}のメール配信</label>
                        <div class="col-md-8">
                            @php
                                $unsubscriber = $unsubscribers->firstWhere('plugin_name', $plugin->plugin_name_strtolower) ?? new Unsubscriber();
                            @endphp
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" value="0" id="unsubscribed_flag_off_{{$plugin->plugin_name_strtolower}}" name="unsubscribed_flags[{{$plugin->plugin_name_strtolower}}]" class="custom-control-input" @if(old("unsubscribed_flag.{$plugin->plugin_name_strtolower}", $unsubscriber->unsubscribed_flag) == 0) checked="checked" @endif>
                                        <label class="custom-control-label" for="unsubscribed_flag_off_{{$plugin->plugin_name_strtolower}}">受け取る</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" value="1" id="unsubscribed_flag_on_{{$plugin->plugin_name_strtolower}}" name="unsubscribed_flags[{{$plugin->plugin_name_strtolower}}]" class="custom-control-input" @if(old("unsubscribed_flag.{$plugin->plugin_name_strtolower}", $unsubscriber->unsubscribed_flag) == 1) checked="checked" @endif>
                                        <label class="custom-control-label" for="unsubscribed_flag_on_{{$plugin->plugin_name_strtolower}}">解除</label>
                                    </div>
                                </div>
                            </div>
                            <small class="form-text text-muted">メール配信を解除すると、{{$plugin->plugin_name_full}}からの全てのメール配信を止めます。</small>
                        </div>
                    </div>
                @endforeach

                <div class="text-center">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i>
                        更新
                    </button>
                </div>
            </form>
        </div><!-- /card-body -->
    </div><!-- /card -->
</main>
@endsection
