{{--
 * 施設予約データ表示画面（月）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 --}}

月表示テンプレート<br />
<br />
<a href="{{url('/')}}/plugin/reservations/week/{{$page->id}}/{{$frame->id}}/#frame-{{$frame->id}}">週表示へ</a>
<br>

{{-- 前月ボタン --}}
<a href="{{url('/')}}/plugin/reservations/month/{{$page->id}}/{{$frame->id}}/{{ $carbon_target_date->copy()->subMonth()->format('Ym') }}#frame-{{$frame->id}}">
    <i class="text-info fas fa-angle-left fa-3x"></i>
</a>
{{-- 当月表示 --}}
<span class="h2">{{ $carbon_target_date->year }}年 {{ $carbon_target_date->month }}月</span>
{{-- 翌月ボタン --}}
<a href="{{url('/')}}/plugin/reservations/month/{{$page->id}}/{{$frame->id}}/{{ $carbon_target_date->copy()->addMonth()->format('Ym') }}#frame-{{$frame->id}}">
    <i class="text-info fas fa-angle-right fa-3x"></i>
</a>

    <table class="table table-bordered">
        <thead>
            {{-- カレンダーヘッダ部の曜日を表示 --}}
            <tr>
                @foreach (DayOfWeek::getMembers() as $key => $desc)
                    <th class="text-center">{{ $desc }}</th>
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
                    
                    <td @if ($date->month != $carbon_target_date->month) class="bg-secondary" @endif>
                        {{ $date->day }}
                    </td>
            {{-- 土曜日なら行を閉じる --}}
            @if ($date->dayOfWeek == 6)
                </tr>
            @endif
        @endforeach
    </tbody>
    </table>