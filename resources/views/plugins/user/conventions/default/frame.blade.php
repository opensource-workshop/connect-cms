{{--
 * フレーム表示設定編集画面テンプレート。
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
        フレームごとの表示設定を変更します。
    </div>

    <form action="{{url('/')}}/redirect/plugin/conventions/saveView/{{$page->id}}/{{$frame_id}}/{{$convention->id}}#frame-{{$frame->id}}" method="POST" class="">
        {{ csrf_field() }}
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/conventions/editView/{{$page->id}}/{{$frame_id}}/{{$convention->bucket_id}}#frame-{{$frame_id}}">

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">表示形式</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <select class="form-control" name="type" class="form-control">
                    <option value="0" @if(old('type', $convention_frame->type)==0) selected="selected" @endif>マークなし</option>
                    <option value="1" @if(old('type', $convention_frame->type)==1) selected="selected" @endif>黒丸</option>
                    <option value="2" @if(old('type', $convention_frame->type)==2) selected="selected" @endif>白丸</option>
                    <option value="3" @if(old('type', $convention_frame->type)==3) selected="selected" @endif>黒四角</option>
                    <option value="4" @if(old('type', $convention_frame->type)==4) selected="selected" @endif>1, 2, 3,...</option>
                    <option value="5" @if(old('type', $convention_frame->type)==5) selected="selected" @endif>a, b, c,...</option>
                    <option value="6" @if(old('type', $convention_frame->type)==6) selected="selected" @endif>A, B, C,...</option>
                    <option value="7" @if(old('type', $convention_frame->type)==7) selected="selected" @endif>ⅰ,ⅱ,ⅲ,...</option>
                    <option value="8" @if(old('type', $convention_frame->type)==8) selected="selected" @endif>Ⅰ,Ⅱ,Ⅲ,...</option>
                </select>
                @if ($errors && $errors->has('type')) <div class="text-danger">{{$errors->first('type')}}</div> @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">表示件数</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="view_count" value="{{old('view_count', $convention_frame->view_count)}}" class="form-control">
                @if ($errors && $errors->has('view_count')) <div class="text-danger">{{$errors->first('view_count')}}</div> @endif
                <small class="text-muted">※ 未設定時は10件</small>
            </div>
        </div>
        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'">
                <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
            </button>
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    変更確定
                </span>
            </button>
        </div>
    </form>
@endif
@endsection
