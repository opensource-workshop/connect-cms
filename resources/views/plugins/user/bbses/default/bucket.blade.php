{{--
 * 掲示板・バケツ編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板・プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.bbses.bbses_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('common.errors_form_line')

@if (empty($bbs->id) && $action != 'createBuckets')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        選択画面から、使用する掲示板を選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i>
        @if (empty($bbs->id))
            新しい掲示板設定を登録します。
        @else
            掲示板設定を変更します。
        @endif
    </div>

    @if (empty($bbs->id))
    <form action="{{url('/')}}/redirect/plugin/bbses/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/bbses/createBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
    @else
    <form action="{{url('/')}}/redirect/plugin/bbses/saveBuckets/{{$page->id}}/{{$frame_id}}/{{$bbs->bucket_id}}#frame-{{$frame->id}}" method="POST" class="">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/bbses/editBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
    @endif
        {{ csrf_field() }}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">掲示板名 <label class="badge badge-danger">必須</label></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="name" value="{{old('name', $bbs->name)}}" class="form-control">
                @if ($errors && $errors->has('name')) <div class="text-danger">{{$errors->first('name')}}</div> @endif
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
                        @if (empty($bbs->id))
                            登録確定
                        @else
                            変更確定
                        @endif
                        </span>
                    </button>
                </div>

                {{-- 既存掲示板の場合は削除処理のボタンも表示 --}}
                @if (!empty($bbs->id))
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
                <span class="text-danger">掲示板を削除します。<br>この掲示板に記載した記事も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/')}}/redirect/plugin/bbses/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$bbs->id}}#frame-{{$frame->id}}" method="POST">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endif
@endsection
