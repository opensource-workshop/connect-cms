{{--
 * 施設予約の予約登録（更新）画面
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}
@php
use App\Models\User\Reservations\ReservationsColumn;
use App\Models\User\Reservations\ReservationsFacility;
@endphp

@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
 <script type="text/javascript">
    /**
     * 登録ボタン押下
     */
    function submit_booking_store(btn) {
        btn.disabled = true;
        form_save_booking{{$frame_id}}.submit();
    }

    /**
     * カレンダーボタン押下
     */
    $(function () {
        let calendar_setting = {
            @if (App::getLocale() == ConnectLocale::ja)
                dayViewHeaderFormat: 'YYYY年 M月',
            @endif
            locale: '{{ App::getLocale() }}',
            format: 'YYYY-MM-DD',
            timepicker:false
        };

        // 予約日
        $('#target_date_id').datetimepicker(calendar_setting);
        // 繰り返し終了：指定日
        $('#rrule_until_id').datetimepicker(calendar_setting);
    });

    /**
     * 予約開始・終了時間ボタン押下
     */
    $(function () {
        let time_setting = {
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
        };

        // 予約開始時間ボタン押下
        $('#start_datetime').datetimepicker(time_setting);
        // 予約終了時間ボタン押下
        $('#end_datetime').datetimepicker(time_setting);
    });

    /**
     * 繰り返しselect.change
     */
    $(function () {
        $('#rrule_freq_id').change(function(){
            // 繰り返しルールの表示・非表示
            change_repeat_rule($(this).val());
        });
    });

    /**
     * 繰り返しルールの表示・非表示
     */
    function change_repeat_rule(select_value) {
        // 繰り返しルールの表示・非表示
        switch (select_value) {
            case '{{RruleFreq::DAILY}}':
                $('#repeat_rule_id').collapse('show');
                $('#repeat_rule_daily_id').collapse('show');
                $('#repeat_rule_weekly_id').collapse('hide');
                $('#repeat_rule_monthly_id').collapse('hide');
                $('#repeat_rule_yearly_id').collapse('hide');
                break;
            case '{{RruleFreq::WEEKLY}}':
                $('#repeat_rule_id').collapse('show');
                $('#repeat_rule_daily_id').collapse('hide');
                $('#repeat_rule_weekly_id').collapse('show');
                $('#repeat_rule_monthly_id').collapse('hide');
                $('#repeat_rule_yearly_id').collapse('hide');
                break;
            case '{{RruleFreq::MONTHLY}}':
                $('#repeat_rule_id').collapse('show');
                $('#repeat_rule_daily_id').collapse('hide');
                $('#repeat_rule_weekly_id').collapse('hide');
                $('#repeat_rule_monthly_id').collapse('show');
                $('#repeat_rule_yearly_id').collapse('hide');
                break;
            case '{{RruleFreq::YEARLY}}':
                $('#repeat_rule_id').collapse('show');
                $('#repeat_rule_daily_id').collapse('hide');
                $('#repeat_rule_weekly_id').collapse('hide');
                $('#repeat_rule_monthly_id').collapse('hide');
                $('#repeat_rule_yearly_id').collapse('show');
                break;
            default:
                // 空の場合を想定
                $('#repeat_rule_id').collapse('hide');
                $('#repeat_rule_daily_id').collapse('hide');
                $('#repeat_rule_weekly_id').collapse('hide');
                $('#repeat_rule_monthly_id').collapse('hide');
                $('#repeat_rule_yearly_id').collapse('hide');
        }
    }

</script>

@if ($booking)
<form action="{{url('/')}}/redirect/plugin/reservations/saveBooking/{{$page->id}}/{{$frame_id}}/{{$booking->id}}#frame-{{$frame_id}}" name="form_save_booking{{$frame_id}}" method="POST">
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/reservations/editBooking/{{$page->id}}/{{$frame_id}}/{{$booking->id}}#frame-{{$frame_id}}">
@else
<form action="{{url('/')}}/redirect/plugin/reservations/saveBooking/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" name="form_save_booking{{$frame_id}}" method="POST">
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/reservations/editBooking/{{$page->id}}/{{$frame_id}}?facility_id={{$facility->id}}&target_date={{$target_date->format('Y-m-d')}}#frame-{{$frame_id}}">
@endif
    {{-- 共通エラーメッセージ 呼び出し --}}
    @include('plugins.common.errors_form_line')

    {{-- メッセージエリア --}}
    <div class="alert {{ $booking ? 'alert-warning' : 'alert-info' }} mt-2">
        <i class="fas fa-exclamation-circle"></i> 対象施設の予約を{{ $booking ? '更新' : '登録' }}します。
    </div>

    {{ csrf_field() }}
    <input type="hidden" name="reservations_id" value="{{ $reservation->id }}">
    <input type="hidden" name="facility_id" value="{{ $facility->id }}">
    <input type="hidden" name="columns_set_id" value="{{ $facility->columns_set_id }}">
    {{-- <input type="hidden" name="booking_id" value="{{ $booking ? $booking->id : '' }}"> --}}

    {{-- 基本項目 --}}

    {{-- コンテンツ名 --}}
    {{-- delete: 他プラグイン同様に、登録・編集画面にバケツ名は表示しない
    <div class="row">
        <div class="col-md-3">コンテンツ名：</div>
        <div class="col-md-9">{{ $reservation->reservation_name }}</div>
    </div>
    --}}
    {{-- 施設名 --}}
    <div class="form-group row">
        <div class="col-md-2">施設名</div>
        <div class="col-md-10">{{ $facility->facility_name }}</div>
    </div>
    {{-- 予約日 --}}
    <div class="form-group row">
        <div class="col-md-2">予約日 <span class="badge badge-danger">必須</span></div>
        <div class="col-md-10">

            <div class="row">
                <div class="input-group date col-sm-5" id="target_date_id" data-target-input="nearest">
                    <input
                        type="text"
                        name="target_date"
                        value="{{old('target_date', $target_date->format('Y-m-d'))}}"
                        class="form-control datetimepicker-input @if ($errors->has('target_date')) border-danger @endif"
                        data-target="#target_date_id"
                    >
                    <div class="input-group-append" data-target="#target_date_id" data-toggle="datetimepicker">
                        <div class="input-group-text @if ($errors->has('target_date')) border-danger @endif"><i class="fas fa-calendar-alt"></i></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    @include('plugins.common.errors_inline', ['name' => 'target_date'])
                    @if ($facility->day_of_weeks != ReservationsFacility::all_days) <small class="text-muted">【利用曜日】 {{ $facility->getDayOfWeeksDisplay() }}</small> @endif
                </div>
            </div>

        </div>
    </div>
    {{-- 予約時間 --}}
    <div class="form-group row">
        <div class="col-md-2">予約時間 <span class="badge badge-danger">必須</span></div>
        <div class="col-md-10">

            <div class="row">
                {{-- 予約開始時間 --}}
                <div class="col-md-4">
                    <div class="input-group date" id="start_datetime" data-target-input="nearest">
                        {{-- 表示優先順：
                            ・旧入力値（入力エラー時）
                            ・予約値（更新時）
                            ・初期表示値（新規登録時）
                        --}}
                        <input type="text" name="start_datetime" value="{{ old('start_datetime', $booking ? $booking->start_datetime->format('H:i') : Carbon::now()->addHour(1)->hour.':00') }}" class="form-control datetimepicker-input @if ($errors->has('start_datetime')) border-danger @endif" data-target="#start_datetime">
                        <div class="input-group-append" data-target="#start_datetime" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fas fa-clock"></i></div>
                        </div>
                    </div>
                </div>
                {{-- 予約終了時間 --}}
                <div class="col-md-4">
                    <div class="input-group date" id="end_datetime" data-target-input="nearest">
                        {{-- 表示優先順：
                            ・旧入力値（入力エラー時）
                            ・予約値（更新時）
                            ・初期表示値（新規登録時）
                        --}}
                        <input type="text" name="end_datetime" value="{{ old('end_datetime', $booking ? $booking->end_datetime->format('H:i') : Carbon::now()->addHour(2)->hour.':00') }}" class="form-control datetimepicker-input @if ($errors->has('end_datetime')) border-danger @endif" data-target="#end_datetime">
                        <div class="input-group-append" data-target="#end_datetime" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fas fa-clock"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    @include('plugins.common.errors_inline', ['name' => 'start_datetime'])
                    @include('plugins.common.errors_inline', ['name' => 'end_datetime'])
                    @if ($facility->is_time_control) <small class="text-muted">【利用時間】 {{ substr($facility->start_time, 0, -3) }} ~ {{ substr($facility->end_time, 0, -3) }}</small> @endif
                </div>
            </div>

        </div>
    </div>

    {{-- 繰り返し予定 --}}
    <div class="form-group row">
        <div class="col-md-2">繰り返し</div>
        <div class="col-md-10">

            <select name="rrule_freq" class="custom-select" id="rrule_freq_id">
                <option value="">なし</option>
                @foreach (RruleFreq::getMembers() as $key => $label)
                    <option value="{{$key}}" @if($key == old('rrule_freq', $repeat->getRruleFreq())) selected @endif>{{$label}}</option>
                @endforeach
            </select>

        </div>
    </div>

    {{-- 繰り返しルール --}}
    <div id="repeat_rule_id" class="collapse">
        <div class="form-group row">
            <div class="col-md-2">繰り返し間隔</div>
            <div class="col-md-10">

                {{-- 日ごと --}}
                <div id="repeat_rule_daily_id" class="collapse">
                    <div class="row">
                        <div class="col">

                            <div class="input-group">
                                <select name="rrule_interval_daily" class="custom-select">
                                    @for ($i = 1; $i <= 6; $i++)
                                        <option value="{{$i}}" @if($i == old('rrule_interval_daily', $repeat->getRruleInterval(RruleFreq::DAILY))) selected @endif>{{$i}}日</option>
                                    @endfor
                                </select>
                                <div class="input-group-append"><span class="input-group-text">ごと</span></div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- 週ごと --}}
                <div id="repeat_rule_weekly_id" class="collapse">
                    <div class="form-group row">
                        <div class="col">

                            <div class="input-group">
                                <select name="rrule_interval_weekly" class="custom-select">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{$i}}" @if($i == old('rrule_interval_weekly', $repeat->getRruleInterval(RruleFreq::WEEKLY))) selected @endif>{{$i}}週</option>
                                    @endfor
                                </select>
                                <div class="input-group-append"><span class="input-group-text">ごと</span></div>
                            </div>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            @foreach (RruleDayOfWeek::getMembersJa() as $key => $label)
                                {{-- チェック外した場合にも値を飛ばす対応：value=""にするといずれか必須チェック（required_without）でも使える --}}
                                <input type="hidden" value="" name="rrule_bydays_weekly[{{$key}}]">

                                <div class="custom-control custom-checkbox custom-control-inline">
                                    <input name="rrule_bydays_weekly[{{$key}}]" value="{{$key}}" type="checkbox" class="custom-control-input" id="rrule_bydays_weekly_{{$key}}" @if(old('rrule_bydays_weekly.'.$key, $repeat->getRruleBydayWeekly($key, $target_date)) == $key) checked="checked" @endif>
                                    <label class="custom-control-label" for="rrule_bydays_weekly_{{$key}}">{{$label}}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            @include('plugins.common.errors_inline', ['name' => 'rrule_bydays_weekly'])
                            <small class="text-muted">曜日を選択してください。</small>
                        </div>
                    </div>
                </div>

                {{-- 月ごと --}}
                <div id="repeat_rule_monthly_id" class="collapse">
                    <div class="form-group row">
                        <div class="col">

                            <div class="input-group">
                                <select name="rrule_interval_monthly" class="custom-select">
                                    @for ($i = 1; $i <= 11; $i++)
                                        <option value="{{$i}}" @if($i == old('rrule_interval_monthly', $repeat->getRruleInterval(RruleFreq::MONTHLY))) selected @endif>{{$i}}ヵ月</option>
                                    @endfor
                                </select>
                                <div class="input-group-append"><span class="input-group-text">ごと</span></div>
                            </div>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" value="BYMONTHDAY" id="rrule_repeat_monthly_ordmoday" name="rrule_repeat_monthly" class="custom-control-input" @if(old('rrule_repeat_monthly', $repeat->getRruleRepeatMonthly()) == 'BYMONTHDAY') checked="checked" @endif>
                                <label class="custom-control-label" for="rrule_repeat_monthly_ordmoday">日付指定</label>
                            </div>
                            <select name="rrule_bymonthday_monthly" class="custom-select">
                                <option value=""></option>
                                @for ($i = 1; $i <= 31; $i++)
                                    <option value="{{$i}}" @if($i == old('rrule_bymonthday_monthly', $repeat->getRruleBymonthday())) selected @endif>{{$i}}日</option>
                                @endfor
                            </select>
                            @include('plugins.common.errors_inline', ['name' => 'rrule_bymonthday_monthly'])
                        </div>

                        <div class="col-sm-4">
                            <div class="custom-control custom-radio">
                                <input type="radio" value="BYDAY" id="rrule_repeat_monthly_byday" name="rrule_repeat_monthly" class="custom-control-input" @if(old('rrule_repeat_monthly', $repeat->getRruleRepeatMonthly()) == 'BYDAY') checked="checked" @endif>
                                <label class="custom-control-label" for="rrule_repeat_monthly_byday">曜日指定</label>
                            </div>
                            <select name="rrule_byday_monthly" class="custom-select">
                                <option value=""></option>
                                @foreach (RruleDayOfWeek::getMembersBydayMonthlyJp() as $key => $label)
                                    <option value="{{$key}}" @if($key == old('rrule_byday_monthly', $repeat->getRruleBydayMonthly())) selected @endif>{{$label}}</option>
                                @endforeach
                            </select>
                            @include('plugins.common.errors_inline', ['name' => 'rrule_repeat_monthly'])
                            @include('plugins.common.errors_inline', ['name' => 'rrule_byday_monthly'])
                        </div>
                    </div>
                </div>

                {{-- 年ごと --}}
                <div id="repeat_rule_yearly_id" class="collapse">
                    <div class="form-group row">
                        <div class="col">

                            <div class="input-group">
                                <select name="rrule_interval_yearly" class="custom-select">
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{$i}}" @if($i == old('rrule_interval_yearly', $repeat->getRruleInterval(RruleFreq::YEARLY))) selected @endif>{{$i}}年</option>
                                    @endfor
                                </select>
                                <div class="input-group-append"><span class="input-group-text">ごと</span></div>
                            </div>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col">

                            @foreach (RruleByMonth::getMembersJa() as $i => $label)
                                {{-- チェック外した場合にも値を飛ばす対応：value=""にするといずれか必須チェック（required_without）でも使える --}}
                                <input type="hidden" value="" name="rrule_bymonths_yearly[{{$i}}]">

                                <div class="custom-control custom-checkbox custom-control-inline">
                                    <input name="rrule_bymonths_yearly[{{$i}}]" value="{{$i}}" type="checkbox" class="custom-control-input" id="rrule_bymonths_yearly_{{$i}}" @if(old('rrule_bymonths_yearly.'.$i, $repeat->getRruleBymonthsYearly($i, $target_date)) == $i) checked="checked" @endif>
                                    <label class="custom-control-label" for="rrule_bymonths_yearly_{{$i}}">{{$label}}</label>
                                </div>
                            @endforeach

                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col">
                            @include('plugins.common.errors_inline', ['name' => 'rrule_bymonths_yearly'])
                            <small class="text-muted">月を選択してください。</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <select name="rrule_byday_yearly" class="custom-select">
                                <option value="">開始日と同日</option>
                                @foreach (RruleDayOfWeek::getMembersBydayMonthlyJp() as $key => $label)
                                    <option value="{{$key}}" @if($key == old('rrule_byday_yearly', $repeat->getRruleBydayYearly())) selected @endif>{{$label}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-2">繰り返し終了</div>
            <div class="col-md-10">

                <div class="row">
                    <div class="col-sm-4">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" value="COUNT" id="rrule_repeat_end_count" name="rrule_repeat_end" class="custom-control-input" @if(old('rrule_repeat_end', $repeat->getRruleRepeatEnd()) == 'COUNT') checked="checked" @endif>
                            <label class="custom-control-label" for="rrule_repeat_end_count">指定の回数後</label>
                        </div>
                        <div class="input-group">
                            <input name="rrule_count" class="form-control @if ($errors->has('rrule_count')) border-danger @endif" type="text" value="{{old('rrule_count', $repeat->getRruleCount())}}">
                            <div class="input-group-append"><span class="input-group-text">回</span></div>
                        </div>
                        @include('plugins.common.errors_inline', ['name' => 'rrule_count'])
                    </div>

                    <div class="col-sm-5">
                        <div class="custom-control custom-radio">
                            <input type="radio" value="UNTIL" id="rrule_repeat_end_until" name="rrule_repeat_end" class="custom-control-input" @if(old('rrule_repeat_end', $repeat->getRruleRepeatEnd()) == 'UNTIL') checked="checked" @endif>
                            <label class="custom-control-label" for="rrule_repeat_end_until">指定日</label>
                        </div>
                        <div class="input-group date" id="rrule_until_id" data-target-input="nearest">
                            <input
                                type="text"
                                name="rrule_until"
                                value="{{old('rrule_until', $repeat->getRruleUntil())}}"
                                class="form-control datetimepicker-input @if ($errors->has('rrule_until')) border-danger @endif"
                                data-target="#rrule_until_id"
                            >
                            <div class="input-group-append" data-target="#rrule_until_id" data-toggle="datetimepicker">
                                <div class="input-group-text @if ($errors->has('rrule_until')) border-danger @endif"><i class="fas fa-calendar-alt"></i></div>
                            </div>
                        </div>
                        @include('plugins.common.errors_inline', ['name' => 'rrule_until'])
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- 詳細項目 --}}
    <hr>

    {{-- 予約項目の出力 --}}
    @foreach ($columns as $column)
        {{-- 入力しないカラム型は表示しない --}}
        @if ($column->isNotInputColumnType())
            @continue
        @endif

        <div class="form-group row">
            {{-- 項目名称 --}}
            <label class="col-md-2 control-label">{{$column->column_name}}
                @if ($column->required)
                    {{-- 必須マーク --}}
                    <span class="badge badge-danger">必須</span>
                @endif
            </label>
            {{-- 項目本体 --}}
            <div class="col-md-10">
                @switch($column->column_type)

                    {{-- テキスト項目 --}}
                    @case(ReservationColumnType::text)

                        <input name="columns_value[{{$column->id}}]" class="form-control @if ($errors->has('columns_value.'.$column->id)) border-danger @endif" type="{{$column->column_type}}" value="{{old('columns_value.'.$column->id, $column->value)}}">
                        @include('plugins.common.errors_inline', ['name' => 'columns_value.'.$column->id])
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
                        @include('plugins.common.errors_inline', ['name' => 'columns_value.'.$column->id])
                        @break

                    {{-- wysiwyg項目 --}}
                    @case(ReservationColumnType::wysiwyg)

                        {{-- WYSIWYG 呼び出し --}}
                        @include('plugins.common.wysiwyg', ['target_class' => 'wysiwyg'])

                        <div @if ($errors->has("columns_value.$column->id")) class="border border-danger" @endif>
                            <textarea name="columns_value[{{$column->id}}]" class="form-control wysiwyg">{{old('columns_value.'.$column->id, $column->value)}}</textarea>
                        </div>
                        @include('plugins.common.errors_inline_wysiwyg', ['name' => "columns_value.$column->id"])
                        @break

                    @default

                @endswitch
            </div>
        </div>
    @endforeach

    {{-- ボタンエリア --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <div class="text-center">
                    <a href="{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}" class="btn btn-secondary mr-2">
                        <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                    </a>

                    @if (empty($booking))
                        @if ($buckets->needApprovalUser(Auth::user(), $frame))
                            <button type="submit" class="btn btn-success" onclick="submit_booking_store(this)"><i class="far fa-edit"></i> 登録申請</button>
                        @else
                            <button type="submit" class="btn btn-primary" onclick="submit_booking_store(this)"><i class="fas fa-check"></i> 登録確定</button>
                        @endif
                    @else
                        @if ($buckets->needApprovalUser(Auth::user(), $frame))
                            <button type="submit" class="btn btn-success" onclick="submit_booking_store(this)"><i class="far fa-edit"></i> 変更申請</button>
                        @else
                            <button type="submit" class="btn btn-primary" onclick="submit_booking_store(this)"><i class="fas fa-check"></i> 変更確定</button>
                        @endif
                    @endif
                </div>
            </div>
            @if (!empty($booking))
                <div class="col-3 text-right">
                    <a data-toggle="collapse" href="#collapse{{$booking->id}}">
                        <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> 削除</span></span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</form>

@if (!empty($booking))
    <div id="collapse{{$booking->id}}" class="collapse">
        <div class="card border-danger">
            <div class="card-body">
                <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/')}}/redirect/plugin/reservations/destroyBooking/{{$page->id}}/{{$frame_id}}/{{$booking->id}}#frame-{{$frame->id}}" method="POST">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endif

{{-- 初期状態で開くもの --}}
<script>
    // 繰り返しルールの表示・非表示
    change_repeat_rule('{{old('rrule_freq', $repeat->getRruleFreq())}}');
</script>

@endsection
