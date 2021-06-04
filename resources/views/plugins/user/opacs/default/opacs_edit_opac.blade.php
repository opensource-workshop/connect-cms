{{--
 * OPAC編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.opacs.opacs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
{{-- opac オブジェクトがない or idがない --}}
@if (!$opac || !$opac->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        設定画面から、使用するOPACを選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>

        @if ($message)
            {{$message}}
        @else
            @if (empty($opac) || $create_flag)
                新しいOPAC設定を登録します。
            @else
                OPAC設定を変更します。
            @endif
        @endif
    </div>
@endif

{{-- opac オブジェクトがない or (idがない ＆ 新規作成でもない) --}}
@if (!$opac || (!$opac->id && !$create_flag))
@else
<form action="{{url('/')}}/plugin/opacs/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- create_flag がtrue の場合、新規作成するためにopacs_id を空にする --}}
    @if ($create_flag)
        <input type="hidden" name="opacs_id" value="">
    @else
        <input type="hidden" name="opacs_id" value="{{$opac->id}}">
    @endif

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">OPAC名 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="opac_name" value="{{old('opac_name', $opac->opac_name)}}" class="form-control">
            @if ($errors && $errors->has('opac_name')) <div class="text-danger">{{$errors->first('opac_name')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">表示件数 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="view_count" value="{{old('view_count', $opac->view_count)}}" class="form-control">
            @if ($errors && $errors->has('view_count')) <div class="text-danger">{{$errors->first('view_count')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">メール送信先</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="moderator_mail_send_flag" value="1" class="custom-control-input" id="moderator_mail_send_flag" @if(old('moderator_mail_send_flag', $opac->moderator_mail_send_flag)) checked=checked @endif>
                <label class="custom-control-label" for="moderator_mail_send_flag">貸し出し・返却時に以下のアドレスにメール送信する</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <label class="control-label">送信するメールアドレス（複数ある場合はカンマで区切る）</label>
            <input type="text" name="moderator_mail_send_address" value="{{old('moderator_mail_send_address', $opac->moderator_mail_send_address)}}" class="form-control">
        </div>
    </div>

    <div class="form-group row mb-0 mb-sm-2">
        <label class="{{$frame->getSettingLabelClass()}} @if(!$frame->isExpandNarrow()) pt-sm-0 @endif">貸出設定</label>
        <div class="{{$frame->getSettingInputClass(true)}} row">
            <div class="mb-0 col-12">
                <div class="custom-control custom-radio custom-control-inline">
                    @if (old('lent_setting', $opac->lent_setting) == null ||
                        old('lent_setting', $opac->lent_setting) == '0')
                        <input type="radio" value="0" id="lent_setting0" name="lent_setting" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="lent_setting0" name="lent_setting" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="lent_setting0">貸し出ししない。</label>
                </div>
            </div>
            <div class="mb-0 col-12">
                <div class="custom-control custom-radio custom-control-inline">
                    @if (old('lent_setting', $opac->lent_setting) == '1')
                        <input type="radio" value="1" id="lent_setting1" name="lent_setting" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="lent_setting1" name="lent_setting" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="lent_setting1">貸し出し許可日数を設定せずに貸し出しする。</label>
                </div>
            </div>
            <div class="mb-0 col-12">
                <div class="custom-control custom-radio custom-control-inline">
                    @if (old('lent_setting', $opac->lent_setting) == '2')
                        <input type="radio" value="2" id="lent_setting2" name="lent_setting" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="2" id="lent_setting2" name="lent_setting" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="lent_setting2">貸し出し許可日数を設定して貸し出しする。</label>
                </div>
            </div>
        </div>
    </div>

    {{-- 貸し出し日数 --}}
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}} row p-0 m-0">
            <div class="col-1"></div>
            <div class="col-11">
                <label class="control-label">日数</label>
                <input type="text" name="opacs_configs[lent_days_global]" value="{{old('opacs_configs.lent_days_global', $opac_configs['lent_days_global'])}}" class="form-control">
                @if ($errors && $errors->has('opacs_configs.lent_days_global')) <div class="text-danger">{{$errors->first('opacs_configs.lent_days_global')}}</div> @endif
            </div>
        </div>
    </div>

    <div class="form-group row mb-0 mb-sm-2">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if (old('lent_setting', $opac->lent_setting) == '3')
                    <input type="radio" value="3" id="lent_setting3" name="lent_setting" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="3" id="lent_setting3" name="lent_setting" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="lent_setting3">役割毎に貸し出し許可日数を設定して貸し出しする。</label>
            </div>
        </div>
    </div>

    {{-- 貸し出し日数（役割設定毎） --}}
    @foreach($original_roles as $original_role)
    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}} row p-0 m-0">
            <div class="col-1"></div>
            <div class="col-11">
            <label class="control-label">日数（{{$original_role->value}}）</label>
            <input type="text" name="opacs_configs[lent_days_{{$original_role->name}}]" value="{{old("opacs_configs.lent_days_$original_role->name", $opac_configs["lent_days_$original_role->name"])}}" class="form-control">
            @if ($errors && $errors->has("opacs_configs.lent_days_$original_role->name")) <div class="text-danger">{{$errors->first("opacs_configs.lent_days_$original_role->name")}}</div> @endif
            </div>
        </div>
    </div>
    @endforeach

    <div class="form-group row mb-0 mb-sm-2">
        <label class="{{$frame->getSettingLabelClass()}} @if(!$frame->isExpandNarrow()) pt-sm-0 @endif">貸出冊数</label>
        <div class="{{$frame->getSettingInputClass(true)}} row">
            <div class="mb-0 col-12">
                <div class="custom-control custom-radio custom-control-inline">
                    @if (old('lent_limit', $opac->lent_limit) == null ||
                        old('lent_limit', $opac->lent_limit) == '0')
                        <input type="radio" value="0" id="lent_limit0" name="lent_limit" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="lent_limit0" name="lent_limit" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="lent_limit0">制限しない。</label>
                </div>
            </div>
            <div class="mb-0 col-12">
                <div class="custom-control custom-radio custom-control-inline">
                    @if (old('lent_limit', $opac->lent_limit) == '1')
                        <input type="radio" value="1" id="lent_limit1" name="lent_limit" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="lent_limit1" name="lent_limit" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="lent_limit1">冊数を制限する。</label>
                </div>
            </div>
        </div>
    </div>

    {{-- 貸し出し冊数 --}}
    <div class="form-group row mb-0 mb-sm-2">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}} row p-0 m-0">
            <div class="col-1"></div>
            <div class="col-11">
                <label class="control-label">冊数</label>
                <input type="text" name="opacs_configs[lent_limit_global]" value="{{old('opacs_configs.lent_limit_global', $opac_configs['lent_limit_global'])}}" class="form-control">
                @if ($errors && $errors->has('opacs_configs.lent_limit_global')) <div class="text-danger">{{$errors->first('opacs_configs.lent_limit_global')}}</div> @endif
            </div>
        </div>
    </div>

    <div class="form-group row mb-0 mb-sm-2">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if (old('lent_limit', $opac->lent_limit) == '2')
                    <input type="radio" value="2" id="lent_limit2" name="lent_limit" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="2" id="lent_limit2" name="lent_limit" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="lent_limit2">役割毎に冊数を設定して貸し出しする。</label>
            </div>
        </div>
    </div>

    {{-- 貸し出し冊数（役割設定毎） --}}
    @foreach($original_roles as $original_role)
    <div class="form-group row mb-0 mb-sm-2">
        <label class="{{$frame->getSettingLabelClass()}}"></label>
        <div class="{{$frame->getSettingInputClass()}} row p-0 m-0">
            <div class="col-1"></div>
            <div class="col-11">
                <label class="col-form-label">冊数（{{$original_role->value}}）</label>
                <input type="text" name="opacs_configs[lent_limit_{{$original_role->name}}]" value="{{old("opacs_configs.lent_limit_$original_role->name", $opac_configs["lent_limit_$original_role->name"])}}" class="form-control">
                @if ($errors && $errors->has("opacs_configs.lent_limit_$original_role->name")) <div class="text-danger">{{$errors->first("opacs_configs.lent_limit_$original_role->name")}}</div> @endif
            </div>
        </div>
    </div>
    @endforeach

    {{-- Submitボタン --}}
    <div class="form-group text-center mt-3">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <button type="button" class="btn btn-secondary mr-3" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                </button>
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                    <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    @if (empty($opac) || $create_flag)
                        登録確定
                    @else
                        変更確定
                    @endif
                    </span>
                </button>
            </div>

            {{-- 既存OPACの場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
            <div class="col-3 text-right">
                <a data-toggle="collapse" href="#collapse{{$opac_frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$opac_frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="panel panel-danger">
        <div class="panel-body">
            <span class="text-danger">OPACを削除します。<br>このOPACに登録した書誌情報も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/opacs/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$opac_frame->opacs_id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
