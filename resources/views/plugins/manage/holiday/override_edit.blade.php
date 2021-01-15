{{--
 * 祝日管理の編集画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 祝日管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.holiday.holiday_tab')
    </div>

    <div class="card-body">
        <form name="form_edit" method="post" action="{{url('/')}}/manage/holiday/overrideUpdate">
            {{ csrf_field() }}
            <input type="hidden" name="holiday_date" value="{{$holiday->format('Y-m-d')}}">

            {{-- 日付 --}}
            <div class="form-group row">
                <label class="col-md-2 control-label text-md-right">日付</label>
                <div class="col-md-3">
                    {{$holiday->format('Y-m-d')}}
                </div>
            </div>

            {{-- 祝日名 --}}
            <div class="form-group row">
                <label class="col-md-2 control-label text-md-right">祝日名</label>
                <div class="col-md-10">
                    {{$holiday->getName()}}
                </div>
            </div>

            {{-- 有効/無効 --}}
            <div class="form-group row">
                <label class="col-md-2 control-label text-md-right">有効/無効</label>
                <div class="col-md-10">
                    <div class="custom-control custom-radio custom-control-inline">
                        <input 
                            type="radio" value="1" class="custom-control-input" id="status_1" 
                            name="status" @if($post->status === null || $post->status === 0) checked @else disabled @endif>
                        <label class="custom-control-label" for="status_1">
                            この祝日を無効にする。
                        </label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input 
                            type="radio" value="0" class="custom-control-input" id="status_0" 
                            name="status" @if($post->status === 1) checked @else disabled @endif>
                        <label class="custom-control-label" for="status_0">
                            無効を解除して有効に戻す。
                        </label>
                    </div>
                </div>
            </div>

            {{-- 更新ボタン --}}
            <div class="form-group row">
                <div class="col-12 text-center">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/holiday')}}'"><i class="fas fa-times"></i> キャンセル</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 更新</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
