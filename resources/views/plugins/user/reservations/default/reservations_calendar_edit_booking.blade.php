{{--
 * 施設予約の予約登録（更新）画面
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
 <script type="text/javascript">
    /**
     * 登録ボタン押下
     */
     function submit_booking_store(btn) {
        form_save_booking{{$frame_id}}.action = "{{URL::to('/')}}/plugin/reservations/saveBooking/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}";
        btn.disabled = true;
        form_save_booking{{$frame_id}}.submit();
    }
    /**
     * 予約開始時間カレンダーボタン押下
     */
     $(function () {
        $('#start_datetime').datetimepicker({
            tooltips: {
                close: '閉じる',
                pickHour: '時間を取得',
                incrementHour: '時間を増加',
                decrementHour: '時間を減少',
                pickMinute: '分を取得',
                incrementMinute: '分を増加',
                decrementMinute: '分を減少',
                pickSecond: '秒を取得',
                incrementSecond: '秒を増加',
                decrementSecond: '秒を減少',
                togglePeriod: '午前/午後切替',
                selectTime: '時間を選択'
            },
            format: 'HH:mm',
            stepping: 5
        });
    });
    /**
     * 予約終了時間カレンダーボタン押下
     */
     $(function () {
        $('#end_datetime').datetimepicker({
            tooltips: {
                close: '閉じる',
                pickHour: '時間を取得',
                incrementHour: '時間を増加',
                decrementHour: '時間を減少',
                pickMinute: '分を取得',
                incrementMinute: '分を増加',
                decrementMinute: '分を減少',
                pickSecond: '秒を取得',
                incrementSecond: '秒を増加',
                decrementSecond: '秒を減少',
                togglePeriod: '午前/午後切替',
                selectTime: '時間を選択'
            },
            format: 'HH:mm',
            stepping: 5
        });
    });
</script>

<form action="" name="form_save_booking{{$frame_id}}" method="POST">
    {{-- メッセージエリア --}}
    <div class="alert {{ $booking ? 'alert-warning' : 'alert-info' }} mt-2">
        <i class="fas fa-exclamation-circle"></i> 対象施設の予約を{{ $booking ? '更新' : '登録' }}します。
    </div>

    {{ csrf_field() }}
    <input type="hidden" name="reservations_id" value="{{ $reservation->id }}">
    <input type="hidden" name="facility_id" value="{{ $facility->id }}">
    <input type="hidden" name="booking_id" value="{{ $booking ? $booking->id : '' }}">
    <input type="hidden" name="target_date" value="{{ $target_date->format('Ymd') }}">

    {{-- 基本項目 --}}
    <div class="card">
        <h5 class="card-header">基本項目</h5>
        <div class="card-body">

            {{-- コンテンツ名 --}}
            <div class="row">
                <div class="col-md-3">コンテンツ名：</div>
                <div class="col-md-9">{{ $reservation->reservation_name }}</div>
            </div>
            {{-- 施設名 --}}
            <div class="row">
                <div class="col-md-3">施設名：</div>
                <div class="col-md-9">{{ $facility->facility_name }}</div>
            </div>
            {{-- 予約日 --}}
            <div class="row">
                <div class="col-md-3">予約日：</div>
                <div class="col-md-9">{{ $target_date->format('Y年n月j日') . '(' . DayOfWeek::getDescription($target_date->dayOfWeek) . ')' }}</div>
            </div>
            {{-- 予約時間 --}}
            <div class="row">
                <div class="col-md-3">予約時間：</div>
            </div>
            <div class="form-group row">
                {{-- 予約開始時間 --}}
                <div class="col-md-3 input-group date" id="start_datetime" data-target-input="nearest">
                    {{-- 表示優先順：
                        ・旧入力値（入力エラー時）
                        ・予約値（更新時）
                        ・初期表示値（新規登録時）
                    --}}
                    <input type="text" name="start_datetime" value="{{ old('start_datetime', $booking ? $booking->start_datetime->format('H:i') : Carbon::now()->addHour(1)->hour) }}" class="form-control datetimepicker-input" data-target="#start_datetime">
                    <div class="input-group-append" data-target="#start_datetime" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="far fa-clock"></i></div>
                    </div>
                </div>
                {{-- 予約終了時間 --}}
                <div class="col-md-3 input-group date" id="end_datetime" data-target-input="nearest">
                    {{-- 表示優先順：
                        ・旧入力値（入力エラー時）
                        ・予約値（更新時）
                        ・初期表示値（新規登録時）
                    --}}
                    <input type="text" name="end_datetime" value="{{ old('end_datetime', $booking ? $booking->end_datetime->format('H:i') : Carbon::now()->addHour(2)->hour) }}" class="form-control datetimepicker-input" data-target="#end_datetime">
                    <div class="input-group-append" data-target="#end_datetime" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="far fa-clock"></i></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    @if ($errors && $errors->has('start_datetime')) <div class="text-danger">{{$errors->first('start_datetime')}}</div> @endif
                    @if ($errors && $errors->has('end_datetime')) <div class="text-danger">{{$errors->first('end_datetime')}}</div> @endif
                </div>
            </div>
        </div>
    </div>

    <br>

    {{-- 詳細項目 --}}
    <div class="card">
        <h5 class="card-header">詳細項目</h5>
        <div class="card-body">
            {{-- 予約項目の出力 --}}
            @foreach ($columns as $column)
                <div class="form-group row">
                    {{-- 項目名称 --}}
                    <label class="col-sm-2 control-label">{{$column->column_name}}
                        @if ($column->required)
                            {{-- 必須マーク --}}
                            <label class="badge badge-danger">必須</label>
                        @endif
                    </label>
                    {{-- 項目本体 --}}
                    <div class="col-sm-10">
                        @switch($column->column_type)

                            {{-- テキスト項目 --}}
                            @case(ReservationColumnType::text)

                                <input name="columns_value[{{$column->id}}]" class="form-control" type="{{$column->column_type}}" value="{{old('columns_value.'.$column->id , $column->value ? $column->value : '')}}">
                                    @if ($errors && $errors->has("columns_value.$column->id"))
                                        <div class="text-danger"><i class="fas fa-exclamation-circle"></i> {{$errors->first("columns_value.$column->id")}}</div>
                                    @endif
                                @break

                            {{-- ラジオボタン項目 --}}
                            @case(ReservationColumnType::radio)

                                {{-- 項目に紐づく選択肢データを抽出 --}}
                                @php
                                    $filtered_selects = $selects->filter(function($select) use($column) {
                                        return $select->reservations_id == $column->reservations_id && $select->column_id == $column->id;
                                    })->sortBy('display_sequence');
                                @endphp

                                {{-- 項目に紐づく選択肢データを表示 --}}
                                <div class="container-fluid">
                                    @foreach ($filtered_selects as $select)
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input name="columns_value[{{ $column->id }}]" id="columns_value[{{ $column->id . '_' . $select->id }}]" class="custom-control-input" type="{{ $column->column_type }}" value="{{ $select->id }}" {{ $loop->first || old('columns_value.'.$column->id) == $select->id || $column->value == $select->id ? 'checked' : null }} >
                                            <label class="custom-control-label" for="columns_value[{{ $column->id . '_' . $select->id }}]">{{ $select->select_name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                @break

                            @default

                        @endswitch
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <br>

    {{-- ボタンエリア --}}
    <div class="form-group text-center">
        {{-- キャンセルボタン --}}
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link) . '#frame-' . $frame->id}}'">
                <i class="fas fa-times"></i> キャンセル
            </button>
    {{-- <button type="button" class="btn btn-secondary mr-2" onclick="javascript:submit_booking_cancel();"><i class="fas fa-times"></i> キャンセル</button> --}}
        {{-- 登録ボタン --}}
        <button type="submit" class="btn btn-primary" onclick="javascript:submit_booking_store(this);"><i class="fas fa-check"></i> {{ $booking ? '更新' : '登録' }} </button>
    </div>
</form>
@endsection
