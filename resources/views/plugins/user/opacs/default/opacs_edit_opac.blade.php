{{--
 * OPAC編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
 --}}

<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.opacs.opacs_frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

@if (!$opac->id)
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

@if (!$opac->id && !$create_flag)
@else
<form action="/plugin/opacs/saveOpacs/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- create_flag がtrue の場合、新規作成するためにopacs_id を空にする --}}
    @if ($create_flag)
        <input type="hidden" name="opacs_id" value="">
    @else
        <input type="hidden" name="opacs_id" value="{{$opac->id}}">
    @endif

    <div class="form-group">
        <label class="control-label">OPAC名 <span class="label label-danger">必須</span></label>
        <input type="text" name="opac_name" value="{{old('opac_name', $opac->opac_name)}}" class="form-control">
        @if ($errors && $errors->has('opac_name')) <div class="text-danger">{{$errors->first('opac_name')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">表示件数 <span class="label label-danger">必須</span></label>
        <input type="text" name="view_count" value="{{old('view_count', $opac->view_count)}}" class="form-control">
        @if ($errors && $errors->has('view_count')) <div class="text-danger">{{$errors->first('view_count')}}</div> @endif
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6">
                <button type="submit" class="btn btn-primary form-horizontal mr-3"><i class="fas fa-check"></i> 
                @if (empty($opac) || $create_flag)
                    登録確定
                @else
                    変更確定
                @endif
                </button>
                <button type="button" class="btn btn-secondary" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                    <i class="fas fa-times"></i> キャンセル
                </button>
            </div>

            {{-- 既存OPACの場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
            <div class="col-sm-3 pull-right text-right">
                <a data-toggle="collapse" href="#collapse{{$opac_frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> <span class="hidden-xs">削除</span></span>
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
                <form action="{{url('/')}}/redirect/plugin/opacs/opacsDestroy/{{$page->id}}/{{$frame_id}}/{{$opac_frame->opacs_id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
