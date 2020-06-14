{{--
 * データセット編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 感染症数値集計プラグイン(covid)
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.covids.covids_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if(isset($cc_massage))
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        {{$cc_massage}}
    </div>
@endif

@if (!$covid || !$covid->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        表示するコンテンツを選択するか、新規作成してください。
    </div>
@else
    <div class="alert alert-info" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        @if ($action == 'createBuckets')
            新しいデータセット設定を登録します。
        @else
            データセット設定を変更します。
        @endif
    </div>
@endif

{{-- データセット変更画面を開いて、データがない場合はフォームを表示しない --}}
@if($action == 'editBuckets' && !$covid)
@else
<form action="{{url('/')}}/plugin/covids/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- action が createBuckets の場合、新規作成するためにcovid_id を空にする --}}
    @if ($action == 'createBuckets')
        <input type="hidden" name="covid_id" value="">
    @else
        <input type="hidden" name="covid_id" value="{{$covid->id}}">
    @endif

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">データセット名 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="covids_name" value="{{old('covids_name', $covid->covids_name)}}" class="form-control">
            @if ($errors && $errors->has('covids_name')) <div class="text-danger">{{$errors->first('covids_name')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">データの基本URL <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="source_base_url" value="{{old('source_base_url', $covid->source_base_url)}}" class="form-control">
            @if ($errors && $errors->has('source_base_url')) <div class="text-danger">{{$errors->first('source_base_url')}}</div> @endif
        </div>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                </button>
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 
                    <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    @if ($action == 'createBuckets')
                        登録確定
                    @else
                        変更確定
                    @endif
                    </span>
                </button>
            </div>

            {{-- 既存データセットの場合は削除処理のボタンも表示 --}}
            @if (empty($covid->id))
            @else
            <div class="col-3 text-right">
                <a data-toggle="collapse" href="#collapse{{$frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">データセットを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/covids/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$covid->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
@endsection
