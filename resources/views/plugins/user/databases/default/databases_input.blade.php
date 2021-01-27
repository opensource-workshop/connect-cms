{{--
 * 登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@php
use App\Models\User\Databases\DatabasesColumns;
@endphp

@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@include('common.errors_form_line')

@if (empty($setting_error_messages))

    <script type="text/javascript">
        /**
         * 公開日時のカレンダーボタン押下
         */
        $(function () {
            $('#posted_at{{$frame_id}}').datetimepicker({
                @if (App::getLocale() == ConnectLocale::ja)
                    dayViewHeaderFormat: 'YYYY年 M月',
                @endif
                locale: '{{ App::getLocale() }}',
                sideBySide: true,
                format: 'YYYY-MM-DD HH:mm'
            });
        });
    </script>

    @if (isset($id))
    <form action="{{URL::to('/')}}/plugin/databases/publicConfirm/{{$page->id}}/{{$frame_id}}/{{$id}}#frame-{{$frame_id}}" name="database_add_column{{$frame_id}}" method="POST" class="form-horizontal" enctype="multipart/form-data">
    @else
    <form action="{{URL::to('/')}}/plugin/databases/publicConfirm/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="database_add_column{{$frame_id}}" method="POST" class="form-horizontal" enctype="multipart/form-data">
    @endif
        {{ csrf_field() }}

{{--
<input name="test_value[0]" class="form-control" type="text" value="{{old('test_value.0', 'default1')}}">
<input name="test_value[1]" class="form-control" type="text" value="{{old('test_value.1', 'default2')}}">
--}}

        @foreach($databases_columns as $database_column)
            {{-- 入力しないカラム型は表示しない --}}
            @if (DatabasesColumns::isNotInputColumnType($database_column->column_type))
                @continue
            @endif

            {{-- 通常の項目 --}}
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{$database_column->column_name}} @if ($database_column->required)<span class="badge badge-danger">必須</span> @endif</label>
                <div class="col-sm-9">
                    @include('plugins.user.databases.default.databases_input_' . $database_column->column_type,['database_obj' => $database_column])
                    <div class="small {{ $database_column->caption_color }}">{!! nl2br($database_column->caption) !!}</div>
                </div>
            </div>
        @endforeach

        {{-- 固定項目エリア --}}
        <hr>
        <div class="form-group row">
            <label class="col-sm-3 control-label">公開日時 <label class="badge badge-danger">必須</label></label>
            <div class="col-sm-9">
                <div class="input-group date" id="posted_at{{$frame_id}}" data-target-input="nearest">
                    <input type="text" name="posted_at" value="{{old('posted_at', $inputs->posted_at)}}" class="form-control datetimepicker-input" data-target="#posted_at{{$frame_id}}">
                    <div class="input-group-append" data-target="#posted_at{{$frame_id}}" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
                @if ($errors && $errors->has('posted_at')) <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first('posted_at')}}</div> @endif
            </div>
        </div>

        @if ($is_hide_posted)
            <input type="hidden" name="display_sequence" value="{{old('display_sequence', $inputs->display_sequence)}}">
        @else
            <div class="form-group row">
                <label class="col-sm-3 control-label">表示順</label>
                <div class="col-sm-9">
                    <input type="text" name="display_sequence" value="{{old('display_sequence', $inputs->display_sequence)}}" class="form-control">
                    <small class="text-muted">※ 未指定時は最後に表示されるように自動登録します。</small>
                    @if ($errors && $errors->has('display_sequence')) <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first('display_sequence')}}</div> @endif
                </div>
            </div>
        @endif

        {{-- ボタンエリア --}}
        <div class="form-group text-center">
            <div class="row">
                <div class="col-xl-3"></div>
                <div class="col-9 col-xl-6">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    <button class="btn btn-primary"><i class="fab fa-facebook-messenger"></i> 確認画面へ</button>
                </div>
                @if (!empty($id))
                <div class="col-3 col-xl-3 text-right">
                        <a data-toggle="collapse" href="#collapse{{$id}}">
                            <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> 削除</span></span>
                        </a>
                </div>
                @endif
            </div>
        </div>
    </form>

    <div id="collapse{{$id}}" class="collapse" style="margin-top: 8px;">
        <div class="card border-danger">
            <div class="card-body">
                <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/')}}/plugin/databases/delete/{{$page->id}}/{{$frame_id}}/{{$id}}#frame-{{$frame->id}}" method="POST">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

@else
    {{-- フレームに紐づくコンテンツがない場合等、表示に支障がある場合は、データ登録を促す等のメッセージを表示 --}}
    <div class="card border-danger">
        <div class="card-body">
            @foreach ($setting_error_messages as $setting_error_message)
                <p class="text-center cc_margin_bottom_0">{{ $setting_error_message }}</p>
            @endforeach
        </div>
    </div>
@endif
@endsection
