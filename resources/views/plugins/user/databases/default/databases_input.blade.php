{{--
 * 登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if (empty($setting_error_messages))

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
            @switch($database_column->column_type)
            {{-- 登録日型・更新日型は入力表示しない --}}
            @case(DatabaseColumnType::created)
            @case(DatabaseColumnType::updated)
                @break
            {{-- 通常の項目 --}}
            @default
                <div class="form-group row">
                    <label class="col-sm-3 control-label">{{$database_column->column_name}} @if ($database_column->required)<label class="badge badge-danger">必須</label> @endif</label>
                    <div class="col-sm-9">
                        @include('plugins.user.databases.default.databases_input_' . $database_column->column_type,['database_obj' => $database_column])
                        <div class="small {{ $database_column->caption_color }}">{!! nl2br($database_column->caption) !!}</div>
                    </div>
                </div>
            @endswitch
        @endforeach
        {{-- ボタンエリア --}}
        <div class="form-group text-center">
            <div class="row">
                <div class="col-xl-3"></div>
                <div class="col-9 col-xl-6">
                    @if($id)
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/databases/detail/{{$page->id}}/{{$frame_id}}/{{$id}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    @else
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    @endif
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
                    <form action="{{url('/')}}/plugin/databases/delete/{{$page->id}}/{{$frame_id}}/{{$id}}" method="POST">
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
