{{--
 * 施設予約データ表示画面（月）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}

    {{-- カレンダーヘッダ部 --}}
    <br>

    {{-- メッセージエリア --}}
    @if ($message)
        <div class="alert alert-info mt-2">
            <i class="fas fa-exclamation-circle"></i>{{ $message }}
        </div>
    @endif

    <div class="row">
        <div class="col-12 clearfix">
            <div class="float-left">
                {{-- 前月ボタン --}}
                <a href="{{url('/')}}/plugin/reservations/month/{{$page->id}}/{{$frame->id}}/{{ $carbon_target_date->copy()->subMonth()->format('Ym') }}#frame-{{$frame->id}}">
                    <i class="fas fa-angle-left fa-3x"></i>
                </a>
                {{-- 当月表示 --}}
                <span class="h2">{{ $carbon_target_date->year }}年 {{ $carbon_target_date->month }}月</span>
                {{-- 翌月ボタン --}}
                <a href="{{url('/')}}/plugin/reservations/month/{{$page->id}}/{{$frame->id}}/{{ $carbon_target_date->copy()->addMonth()->format('Ym') }}#frame-{{$frame->id}}">
                    <i class="fas fa-angle-right fa-3x"></i>
                </a>
            </div>
            <div class="float-right">
                {{-- 今月へボタン --}}
                <a href="{{url('/')}}/plugin/reservations/month/{{$page->id}}/{{$frame->id}}/{{ Carbon::today()->format('Ym') }}#frame-{{$frame->id}}">
                    <button type="button" class="btn btn-primary rounded-pill">今月へ<br>({{ Carbon::today()->format('Y年m月') }})</button>
                </a>
            </div>
        </div>
    </div>
    <br>
    {{-- 登録している施設分ループ --}}
    @foreach ($facilities as $facility)

        {{-- 施設名 --}}
        <span class="h4">＜{{ $facility->facility_name }}＞</span>

        {{-- カレンダーデータ部 --}}
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    {{-- カレンダーヘッダ部の曜日を表示 --}}
                    <tr>
                        @foreach (DayOfWeek::getMembers() as $key => $desc)
                            {{-- 日曜なら赤文字、土曜なら青文字 --}}
                            <th class="text-center bg-light{{ $key == DayOfWeek::sun ? ' text-danger' : '' }}{{ $key == DayOfWeek::sat ? ' text-primary' : '' }}">{{ $desc }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- カレンダーデータ部の表示 --}}
                    @foreach ($dates as $date)
                        {{-- 日曜日なら新しい行 --}}
                        @if ($date->dayOfWeek == 0)
                            <tr>
                        @endif
                                <td class="
                                    {{-- 当月以外ならセル背景をグレーアウト --}}
                                    {{ $date->month != $carbon_target_date->month ? 'bg-secondary' : '' }}
                                    {{-- 当月、且つ、日曜なら赤文字 --}}
                                    {{ $date->month == $carbon_target_date->month && $date->dayOfWeek == DayOfWeek::sun ? ' text-danger' : '' }}
                                    {{-- 当月、且つ、日曜なら赤文字 --}}
                                    {{ $date->month == $carbon_target_date->month && $date->dayOfWeek == DayOfWeek::sat ? ' text-primary' : '' }}
                                    {{-- 当日ならセル背景を黄色 --}}
                                    {{ $date == Carbon::today() ? ' bg-warning' : '' }}
                                    "
                                >
                                    <div class="clearfix">
                                        {{-- 日付 --}}
                                        <div class="float-left">
                                            {{ $date->day }}
                                        </div>
                                        {{-- ＋ボタン --}}
                                        <div class="float-right">
                                            @auth
                                                <form action="{{URL::to('/')}}/plugin/reservations/editBooking/{{$page->id}}/{{$frame_id}}/{{ $date->format('Ymd') }}#frame-{{$frame_id}}" name="form_edit_booking_{{ $reservations->id }}_{{ $facility->id }}_{{ $date->format('Ymd') }}" method="POST" class="form-horizontal">
                                                    {{ csrf_field() }}
                                                    <input type="hidden" name="reservations_id" value="{{ $reservations->id }}">
                                                    <input type="hidden" name="facility_id" value="{{ $facility->id }}">
                                                    <a href="javascript:form_edit_booking_{{ $reservations->id }}_{{ $facility->id }}_{{ $date->format('Ymd') }}.submit()">
                                                        <i class="fas fa-plus-square fa-2x"></i>
                                                    </a>
                                                </form>
                                            @endauth
                                        </div>
                                    </div>
                                    {{-- 
                                    <span class="small">10:00~12:00</span>
                                    <span class="small">12:00~14:00</span>
                                     --}}
                                </td>
                        {{-- 土曜日なら行を閉じる --}}
                        @if ($date->dayOfWeek == 6)
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
