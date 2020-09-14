{{--
 * データベース検索編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース検索プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.databasesearches.databasesearches_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if (!$databasesearches->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        使用するデータベース検索設定を選択するか、作成してください。
    </div>
@endif

@if (!$databasesearches)
@else

    @if($databasesearches->id)
    <form action="{{url('/')}}/plugin/databasesearches/saveBuckets/{{$page->id}}/{{$frame_id}}/{{$databasesearches->id}}" method="POST" class="">
    @else
    <form action="{{url('/')}}/plugin/databasesearches/saveBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    @endif
        {{ csrf_field() }}

        <input type="hidden" name="databasesearches_id" value="{{$databasesearches->id}}">
        <input type="hidden" name="buckets_id" value="{{$frames->bucket_id}}">

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">データベース検索名 <label class="badge badge-danger">必須</label></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="databasesearches_name" value="{{old('databasesearches_name', $databasesearches->databasesearches_name)}}" class="form-control">
                @if ($errors && $errors->has('databasesearches_name')) <div class="text-danger">{{$errors->first('databasesearches_name')}}</div> @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">表示件数 <label class="badge badge-danger">必須</label></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="view_count" value="{{old('view_count', $databasesearches->view_count)}}" class="form-control col-sm-3">
                @if ($errors && $errors->has('view_count')) <div class="text-danger">{{$errors->first('view_count')}}</div> @endif
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">表示カラム <label class="badge badge-danger">必須</label></label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="view_columns" value="{{old('view_columns', $databasesearches->view_columns)}}" class="form-control">
                @if ($errors && $errors->has('view_columns')) <div class="text-danger">{{$errors->first('view_columns')}}</div> @endif
                <small class="form-text text-muted">
                    ※ カンマ区切り（ex. タイトル,エリア）<br>
                    ※ 表示カラムを設定しても、データベースのDBカラム設定「権限の表示指定」が設定されている場合、設定した権限以外はデータ表示されません。
                </small>
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">条件</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <textarea class="form-control" name="condition" rows=5>{!!old('condition', $databasesearches->condition)!!}</textarea>
                @if ($errors && $errors->has('condition')) <div class="text-danger">{{$errors->first('condition')}}</div> @endif
                <small class="form-text text-muted">
                    name：(カラム名称 | ALL)、where：(ALL | PART | FRONT | REAR | GT | LT | GE | LE)、request：リクエスト項目、request_default：リクエストが空の場合の値<br />ex. {"name":"エリア","where":"ALL","request":"area","request_default":"北海道"}<br />
                    複数条件(AND)は配列で指定 ex. [{"name":"エリア"...},{"name":"エリア"...}]
                </small>
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">フレームの選択</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                <div class="custom-control custom-radio custom-control-inline">
                    @if($databasesearches->frame_select == 0)
                        <input type="radio" value="0" id="frame_select_0" name="frame_select" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="frame_select_0" name="frame_select" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="frame_select_0">全て表示する</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if($databasesearches->frame_select == 1)
                        <input type="radio" value="1" id="frame_select_1" name="frame_select" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="frame_select_1" name="frame_select" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="frame_select_1">選択したものだけ表示する</label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">対象ページ - フレーム</label>
            <div class="{{$frame->getSettingInputClass(false, true)}}">
                <div class="card-body py-2 pl-0">
                @foreach($target_frames as $target_frame)
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="target_frame_ids[]" value="{{$target_frame->id}}" class="custom-control-input" id="target_frame_{{$target_frame->id}}" @if(old("target_frame.$target_frame->id", $databasesearches->isTargetFrame($target_frame->id))) checked=checked @endif>
                        <label class="custom-control-label" for="target_frame_{{$target_frame->id}}">{{$target_frame->page_name}} - {{$target_frame->frame_title}}</label>
                    </div>
                @endforeach
                </div>
            </div>
            @if ($errors && $errors->has('target_frame_ids')) <div class="text-danger">{{$errors->first('target_frame_ids')}}</div> @endif
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
                        @if (empty($databasesearches->id))
                            登録
                        @else
                            変更
                        @endif
                        </span>
                    </button>
                </div>

                {{-- 既存のデータベース検索の場合は削除処理のボタンも表示 --}}
                @if ($databasesearches)
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
                <span class="text-danger">データベース検索を削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/')}}/redirect/plugin/databasesearches/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$databasesearches->id}}" method="POST">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データベース検索を削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
