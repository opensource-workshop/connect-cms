{{--
 * FAQ編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category FAQプラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.faqs.faqs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if (empty($faq) || !$faq->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        設定画面から、使用するFAQを選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>

        @if ($message)
            {{$message}}
        @else
            @if (empty($faq) || $create_flag)
                新しいFAQ設定を登録します。
            @else
                FAQ設定を変更します。
            @endif
        @endif
    </div>
@endif

@if (empty($faq) || (!$faq->id && !$create_flag))
@else
<form action="{{url('/')}}/plugin/faqs/saveBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- create_flag がtrue の場合、新規作成するためにfaqs_id を空にする --}}
    @if ($create_flag)
        <input type="hidden" name="faqs_id" value="">
    @else
        <input type="hidden" name="faqs_id" value="{{$faq->id}}">
    @endif

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">FAQ名 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="faq_name" value="{{old('faq_name', $faq->faq_name)}}" class="form-control">
            @if ($errors && $errors->has('faq_name')) <div class="text-danger">{{$errors->first('faq_name')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">表示件数 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="view_count" value="{{old('view_count', $faq->view_count)}}" class="form-control col-sm-3">
            @if ($errors && $errors->has('view_count')) <div class="text-danger">{{$errors->first('view_count')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">RSS</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if($faq->rss == 1)
                    <input type="radio" value="1" id="rss_off" name="rss" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="rss_off" name="rss" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="rss_off">表示する</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($faq->rss == 0)
                    <input type="radio" value="0" id="rss_on" name="rss" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="rss_on" name="rss" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="rss_on">表示しない</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">RSS件数</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="rss_count" value="{{old('rss_count', isset($faq->rss_count) ? $faq->rss_count : 0)}}" class="form-control col-sm-3">
            @if ($errors && $errors->has('rss_count')) <div class="text-danger">{{$errors->first('rss_count')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">順序条件</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if($faq->sequence_conditions == 0)
                    <input type="radio" value="0" id="sequence_conditions_0" name="sequence_conditions" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="sequence_conditions_0" name="sequence_conditions" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="sequence_conditions_0">最新順</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($faq->sequence_conditions == 1)
                    <input type="radio" value="1" id="sequence_conditions_1" name="sequence_conditions" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="sequence_conditions_1" name="sequence_conditions" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="sequence_conditions_1">投稿順</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($faq->sequence_conditions == 2)
                    <input type="radio" value="2" id="sequence_conditions_2" name="sequence_conditions" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="2" id="sequence_conditions_2" name="sequence_conditions" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="sequence_conditions_2">指定順</label>
            </div>
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
                    @if (empty($faq) || $create_flag)
                        登録確定
                    @else
                        変更確定
                    @endif
                    </span>
                </button>
            </div>

            {{-- 既存FAQの場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
            <div class="col-3 text-right">
                <a data-toggle="collapse" href="#collapse{{$faq_frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$faq_frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">FAQを削除します。<br>このFAQに記載した記事も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/faqs/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$faq->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
@endsection
