{{--
 * 開館カレンダー編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 開館カレンダープラグイン
 --}}

<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.openingcalendars.openingcalendars_frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

@if (!$openingcalendar->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        設定画面から、使用する開館カレンダーを選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>

        @if ($message)
            {{$message}}
        @else
            @if (empty($openingcalendar) || $create_flag)
                新しい開館カレンダー設定を登録します。
            @else
                開館カレンダー設定を変更します。
            @endif
        @endif
    </div>
@endif

@if (!$openingcalendar->id && !$create_flag)
@else
<form action="/plugin/openingcalendars/saveBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- create_flag がtrue の場合、新規作成するためにopeningcalendars_id を空にする --}}
    @if ($create_flag)
        <input type="hidden" name="openingcalendars_id" value="">
    @else
        <input type="hidden" name="openingcalendars_id" value="{{$openingcalendar->id}}">
    @endif

    <div class="form-group">
        <label class="control-label">開館カレンダー名 <label class="badge badge-danger">必須</label></label>
        <input type="text" name="openingcalendar_name" value="{{old('openingcalendar_name', $openingcalendar->openingcalendar_name)}}" class="form-control">
        @if ($errors && $errors->has('openingcalendar_name')) <div class="text-danger">{{$errors->first('openingcalendar_name')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">開館カレンダー名（副題） <label class="badge badge-danger">必須</label></label>
        <input type="text" name="openingcalendar_sub_name" value="{{old('openingcalendar_sub_name', $openingcalendar->openingcalendar_sub_name)}}" class="form-control">
        @if ($errors && $errors->has('openingcalendar_sub_name')) <div class="text-danger">{{$errors->first('openingcalendar_sub_name')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">月の表示形式 <label class="badge badge-danger">必須</label></label>
        <select class="form-control" name="month_format" class="form-control">
            <option value=""></option>
            <option value="1" @if(Input::old('month_format', $openingcalendar->month_format)=="1") selected @endif>1:January / YYMM</option>
        </select>
        @if ($errors && $errors->has('month_format')) <div class="text-danger">{{$errors->first('month_format')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">週の表示形式 <label class="badge badge-danger">必須</label></label>
        <select class="form-control" name="week_format" class="form-control">
            <option value=""></option>
            <option value="1" @if(Input::old('week_format', $openingcalendar->week_format)=="1") selected @endif>SUN</option>
        </select>
        @if ($errors && $errors->has('week_format')) <div class="text-danger">{{$errors->first('week_format')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">過去の表示月数</label>
        <input type="text" name="view_before_month" value="{{old('view_before_month', $openingcalendar->view_before_month)}}" class="form-control">
        @if ($errors && $errors->has('view_before_month')) <div class="text-danger">{{$errors->first('view_before_month')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">未来の表示月数</label>
        <input type="text" name="view_after_month" value="{{old('view_after_month', $openingcalendar->view_after_month)}}" class="form-control">
        @if ($errors && $errors->has('view_after_month')) <div class="text-danger">{{$errors->first('view_after_month')}}</div> @endif
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div>
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                <i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span>
            </button>
            <button type="submit" class="btn btn-primary form-horizontal mr-2"><i class="fas fa-check"></i>
                <span class="d-none d-xl-inline">
                @if (empty($openingcalendar) || $create_flag)
                    登録
                @else
                    変更
                @endif
                </span>
            </button>

            {{-- 既存開館カレンダーの場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
                <a data-toggle="collapse" href="#collapse{{$openingcalendar_frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="d-none d-xl-inline"> 削除</span></span>
                </a>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$openingcalendar_frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">開館カレンダーを削除します。<br>この開館カレンダーに記載した記事も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/openingcalendars/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$openingcalendar->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
