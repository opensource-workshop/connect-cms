{{--
 * イベント・バケツ編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category イベント管理プラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.conventions.conventions_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if (empty($convention->id) && $action != 'createBuckets')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        選択画面から、使用するイベントを選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i>
        @if (empty($convention->id))
            新しいイベント設定を登録します。
        @else
            イベント設定を変更します。
        @endif
    </div>

    @if (empty($convention->id))
    <form action="{{url('/')}}/redirect/plugin/conventions/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/conventions/createBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
    @else
    <form action="{{url('/')}}/redirect/plugin/conventions/saveBuckets/{{$page->id}}/{{$frame_id}}/{{$convention->bucket_id}}#frame-{{$frame->id}}" method="POST" class="">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/conventions/editBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
    @endif
        {{ csrf_field() }}
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">イベント名 <label class="badge badge-danger">必須</label></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="name" value="{{old('name', $convention->name)}}" class="form-control">
                @if ($errors && $errors->has('name')) <div class="text-danger">{{$errors->first('name')}}</div> @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">トラック数 <label class="badge badge-danger">必須</label></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="track_count" value="{{old('track_count', $convention->track_count)}}" class="form-control">
                @if ($errors && $errors->has('track_count')) <div class="text-danger">{{$errors->first('track_count')}}</div> @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">コマ数 <label class="badge badge-danger">必須</label></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="period_count" value="{{old('period_count', $convention->period_count)}}" class="form-control">
                @if ($errors && $errors->has('period_count')) <div class="text-danger">{{$errors->first('period_count')}}</div> @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">コマ説明</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <textarea name="period_label" class="form-control" rows=2>{!!old('period_label', $convention->period_label)!!}</textarea>
                @if ($errors && $errors->has('period_label')) <div class="text-danger">{{$errors->first('period_label')}}</div> @endif
                <small class="text-muted">※ コマの説明をカンマ区切りで記載します。</small>
            </div>
        </div>

        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <div class="row">
                <div class="col-3"></div>
                <div class="col-6">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'">
                        <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                    </button>
                    <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                        <span class="{{$frame->getSettingButtonCaptionClass()}}">
                        @if (empty($convention->id))
                            登録確定
                        @else
                            変更確定
                        @endif
                        </span>
                    </button>
                </div>

                {{-- 既存イベントの場合は削除処理のボタンも表示 --}}
                @if (!empty($convention->id))
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
                <span class="text-danger">イベントを削除します。<br>このイベントに記載した詳細も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/')}}/redirect/plugin/conventions/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$convention->id}}#frame-{{$frame->id}}" method="POST">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endif
@endsection
