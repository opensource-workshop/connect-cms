{{--
 * カウンター・バケツ編集画面テンプレート。
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.counters.counters_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('common.errors_form_line')

<div class="alert alert-info">
    <i class="fas fa-exclamation-circle"></i>
    @if (empty($counter->id))
        新しいカウンター設定を登録します。
    @else
        カウンター設定を変更します。
    @endif
</div>

@if (empty($counter->id))
<form action="{{url('/')}}/redirect/plugin/counters/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST">
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/counters/createBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
@else
<form action="{{url('/')}}/redirect/plugin/counters/saveBuckets/{{$page->id}}/{{$frame_id}}/{{$counter->bucket_id}}#frame-{{$frame->id}}" method="POST">
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/counters/editBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
@endif
    {{ csrf_field() }}

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">カウンター名 <span class="badge badge-danger">必須</span></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="name" value="{{old('name', $counter->name)}}" class="form-control @if ($errors && $errors->has('name')) border-danger @endif">
            @include('common.errors_inline', ['name' => 'name'])
        </div>
    </div>

    {{-- 登録のみ --}}
    @if (empty($counter->id))
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">初期カウント数</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="initial_count" value="{{old('initial_count')}}" class="form-control @if ($errors && $errors->has('initial_count')) border-danger @endif">
                @include('common.errors_inline', ['name' => 'initial_count'])
                <small class="text-muted">※ 未設定時は0で登録します。</small>
            </div>
        </div>
    @endif

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <a href="{{URL::to($page->permanent_link)}}" class="btn btn-secondary mr-2">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                </a>
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                    <span class="{{$frame->getSettingButtonCaptionClass()}}">
                        @if (empty($counter->id))
                            登録確定
                        @else
                            変更確定
                        @endif
                    </span>
                </button>
            </div>

            {{-- 既存カウンターの場合は削除処理のボタンも表示 --}}
            @if (!empty($counter->id))
                <div class="col-3 text-right">
                    <a data-toggle="collapse" href="#collapse{{$frame->id}}">
                        <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$frame->id}}" class="collapse">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">カウンターを削除します。<br>このカウンターに記載した記事も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/counters/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$counter->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection
