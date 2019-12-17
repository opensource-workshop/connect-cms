{{--
 * 施設予約データ表示画面（月）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}

<a href="{{url('/')}}/plugin/reservations/week/{{$page->id}}/{{$frame->id}}/#frame-{{$frame->id}}">週表示へ</a>
<br>
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
                <a href="{{url('/')}}/plugin/reservations/month/{{$page->id}}/{{$frame->id}}/{{ Carbon::now()->format('Ym') }}#frame-{{$frame->id}}">
                    <button type="button" class="btn btn-primary rounded-pill">今月へ</button>
                </a>
            </div>
        </div>
    </div>
    <table class="table table-bordered">
        <thead>
            {{-- カレンダーヘッダ部の曜日を表示 --}}
            <tr>
                @foreach (DayOfWeek::getMembers() as $key => $desc)
                    {{-- 日曜なら赤文字、土曜なら青文字 --}}
                    <th class="text-center{{ $key == DayOfWeek::sun ? ' text-danger' : '' }}{{ $key == DayOfWeek::sat ? ' text-primary' : '' }}">{{ $desc }}</th>
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
                    <td 
                        {{-- 当月以外ならセル背景をグレーアウト --}}
                        @if ($date->month != $carbon_target_date->month) class="bg-secondary" @endif
                        {{-- 日曜なら赤文字 --}}
                        @if ($date->dayOfWeek == 0) class="text-danger" @endif
                        {{-- 日曜なら青文字 --}}
                        @if ($date->dayOfWeek == 6) class="text-primary" @endif
                    >
                        {{ $date->day }}
                    </td>
            {{-- 土曜日なら行を閉じる --}}
            @if ($date->dayOfWeek == 6)
                </tr>
            @endif
        @endforeach
    </tbody>
    </table>