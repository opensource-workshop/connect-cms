{{--
 * キャビネット・バケツ編集画面テンプレート。
 *
 * @author 石垣　佑樹 <ishigaki@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category キャビネット・プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.cabinets.cabinets_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('common.errors_form_line')

@if (empty($cabinet->id) && $action != 'createBuckets')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i> {{ __('messages.empty_bucket_setting', ['plugin_name' => 'キャビネット']) }}
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i>
        @if (empty($cabinet->id))
            新しいキャビネット設定を登録します。
        @else
            キャビネット設定を変更します。
        @endif
    </div>

    @if (empty($cabinet->id))
    <form action="{{url('/')}}/redirect/plugin/cabinets/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/cabinets/createBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
    @else
    <form action="{{url('/')}}/redirect/plugin/cabinets/saveBuckets/{{$page->id}}/{{$frame_id}}/{{$cabinet->bucket_id}}#frame-{{$frame->id}}" method="POST">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/cabinets/editBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
    @endif
        {{ csrf_field() }}
        
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">キャビネット名 <label class="badge badge-danger">必須</label></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="name" value="{{old('name', $cabinet->name)}}" class="form-control @if ($errors && $errors->has('name')) border-danger @endif">
                @include('common.errors_inline', ['name' => 'name'])
            </div>
        </div>
    
        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}" for="upload-max-size">ファイル最大サイズ</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <select class="form-control col-md-3 @if ($errors && $errors->has('upload_max_size')) border-danger @endif" name="upload_max_size" id="upload-max-size">
                    @foreach (UploadMaxSize::getMembers() as $key=>$value)
                    <option value="{{$key}}" @if(old("upload_max_size", $cabinet->upload_max_size) == $key) selected="selected" @endif>
                        {{ $value }}
                    </option>
                    @endforeach
                </select>
                @include('common.errors_inline', ['name' => 'upload_max_size'])
                <small id="upload-size-help" class="form-text text-muted">サーバの設定によるため、サイズを変更しても反映されない場合があります。</small>
                <small id="upload-size-server-help" class="form-text text-muted">サーバ設定：アップロードできる最大サイズ&nbsp;<span class="font-weight-bold">{{ini_get('upload_max_filesize')}}</span></small>
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
                        @if (empty($cabinet->id))
                            登録確定
                        @else
                            変更確定
                        @endif
                        </span>
                    </button>
                </div>

                {{-- 既存キャビネットの場合は削除処理のボタンも表示 --}}
                @if (!empty($cabinet->id))
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
                <span class="text-danger">キャビネットを削除します。<br>このキャビネットに追加したファイルも削除され、元に戻すことはできないため、よく確認して実行してください。</span>

                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/')}}/redirect/plugin/cabinets/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$cabinet->id}}#frame-{{$frame->id}}" method="POST">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endif
@endsection
