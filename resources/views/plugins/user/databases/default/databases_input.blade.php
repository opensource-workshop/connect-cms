{{--
 * 登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@include('plugins.common.errors_form_line')

@if (empty($setting_error_messages))

    @if (isset($id))
    <form action="{{URL::to('/')}}/plugin/databases/publicConfirm/{{$page->id}}/{{$frame_id}}/{{$id}}#frame-{{$frame_id}}" name="database_add_column{{$frame_id}}" method="POST" class="form-horizontal" enctype="multipart/form-data">
    @else
    <form action="{{URL::to('/')}}/plugin/databases/publicConfirm/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="database_add_column{{$frame_id}}" method="POST" class="form-horizontal" enctype="multipart/form-data">
    @endif
        {{ csrf_field() }}

        @foreach($databases_columns as $database_column)
            {{-- 入力しないカラム型は表示しない --}}
            @if ($database_column->isNotInputColumnType())
                @continue
            @endif

            {{-- 通常の項目 --}}
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{$database_column->column_name}} @if ($database_column->required)<span class="badge badge-danger">必須</span> @endif</label>
                <div class="col-sm-9">
                    @include('plugins.user.databases.default.databases_input_' . $database_column->column_type,['database_obj' => $database_column])
                    @php
                        $caption = nl2br($database_column->caption);
                        $caption = str_ireplace('[[upload_max_filesize]]', ini_get('upload_max_filesize'), $caption);
                    @endphp
                    <div class="small {{ $database_column->caption_color }}">{!! $caption !!}</div>
                </div>
            </div>
        @endforeach

        {{-- 固定項目エリア --}}
        <hr>
        <div class="form-group row">
            <label class="col-sm-3 control-label">公開日時 <label class="badge badge-danger">必須</label></label>
            <div class="col-sm-9">
                <div class="input-group date" id="posted_at{{$frame_id}}" data-target-input="nearest">
                    <input type="text" name="posted_at" value="{{old('posted_at', $inputs->posted_at)}}" class="form-control datetimepicker-input @if ($errors && $errors->has('posted_at')) border-danger @endif" data-target="#posted_at{{$frame_id}}">
                    <div class="input-group-append" data-target="#posted_at{{$frame_id}}" data-toggle="datetimepicker">
                        <div class="input-group-text @if ($errors && $errors->has('posted_at')) border-danger @endif"><i class="far fa-clock"></i></div>
                    </div>
                </div>
                @include('plugins.common.errors_inline', ['name' => 'posted_at'])
                {{-- DateTimePicker 呼び出し --}}
                @include('plugins.common.datetimepicker', ['element_id' => "posted_at{$frame_id}", 'side_by_side' => true])
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-3 control-label">公開終了日時</label>
            <div class="col-sm-9">
                <div class="input-group date" id="expires_at{{$frame_id}}" data-target-input="nearest">
                    <input type="text" name="expires_at" value="{{old('expires_at', $inputs->expires_at)}}" class="form-control datetimepicker-input @if ($errors && $errors->has('expires_at')) border-danger @endif" data-target="#expires_at{{$frame_id}}">
                    <div class="input-group-append" data-target="#expires_at{{$frame_id}}" data-toggle="datetimepicker">
                        <div class="input-group-text @if ($errors && $errors->has('expires_at')) border-danger @endif"><i class="far fa-clock"></i></div>
                    </div>
                </div>
                @include('plugins.common.errors_inline', ['name' => 'expires_at'])
                {{-- DateTimePicker 呼び出し --}}
                @include('plugins.common.datetimepicker', ['element_id' => "expires_at{$frame_id}", 'side_by_side' => true])
            </div>
        </div>

        @if ($is_hide_posted)
            <input type="hidden" name="display_sequence" value="{{old('display_sequence', $inputs->display_sequence)}}">
        @else
            <div class="form-group row">
                <label class="col-sm-3 control-label">表示順</label>
                <div class="col-sm-9">
                    <input type="text" name="display_sequence" value="{{old('display_sequence', $inputs->display_sequence)}}" class="form-control @if ($errors && $errors->has('display_sequence')) border-danger @endif">
                    @include('plugins.common.errors_inline', ['name' => 'display_sequence'])
                    <small class="text-muted">※ 未指定時は最後に表示されるように自動登録します。</small>
                </div>
            </div>
        @endif

        <div class="form-group row">
            <label class="col-sm-3 control-label">カテゴリ</label>
            <div class="col-sm-9">
                <select class="form-control" name="categories_id" class="form-control @if ($errors && $errors->has('categories_id')) border-danger @endif">
                    <option value=""></option>
                    @foreach($databases_categories as $category)
                    <option value="{{$category->id}}" @if(old('categories_id', $inputs->categories_id)==$category->id) selected="selected" @endif>{{$category->category}}</option>
                    @endforeach
                </select>
                @include('plugins.common.errors_inline', ['name' => 'categories_id'])
                <small class="text-muted">※ カテゴリは新着情報に表示されます。サイト内検索の検索対象になります。</small>
            </div>
        </div>

        {{-- ボタンエリア --}}
        <div class="form-group text-center">
            <div class="row">
                <div class="col-xl-3"></div>
                <div class="col-9 col-xl-6">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    <button class="btn btn-primary"><i class="fa-solid fa-comment"></i> 確認画面へ</button>
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
