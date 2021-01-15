{{--
 * 祝日管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 祝日管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

{{-- ボタンによってアクション --}}
<script type="text/javascript">
    function submitFormYear() {
        form_year.submit();
    }
</script>

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.holiday.holiday_tab')
    </div>

    <div class="card-body">
        <form name="form_year" action="{{url('/')}}/manage/holiday/index" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="year" class="col-md-2 col-form-label text-md-right">表示年</label>
                <div class="col-md-10">
                <select class="form-control" name="year" onChange="submitFormYear()">
                    @for ($i = date("Y") + 1; $i >= 2000; $i--)
                    <option value="{{$i}}"@if (Session::get('holiday_year') == $i) selected @endif>{{$i}}年</option>
                    @endfor
                </select>
                </div>
            </div>
        </form>

        <div class="form-group table-responsive">
            <table class="table table-hover">
            <thead>
                <tr>
                    <th nowrap>日付</th>
                    <th nowrap>祝日名</th>
                    <th nowrap>ステータス</th>
                    <th nowrap>編集</th>
                </tr>
            </thead>
            <tbody>
            @foreach($holidays as $holiday)
                <tr>
                    <td nowrap>{{$holiday->format('Y-m-d')}}（{{DayOfWeek::getDescription($holiday->format('w'))}}）</td>
                    <td nowrap>{{$holiday->getName()}}</td>
                    @if ($holiday->orginal_holiday_status == 1)
                    <td nowrap><span class="badge badge-pill badge-primary">独自追加</span></td>
                    @elseif ($holiday->orginal_holiday_status == 2)
                    <td nowrap><span class="badge badge-pill badge-danger">無効</span></td>
                    @else
                    <td nowrap><span class="badge badge-pill badge-success">計算値</span></td>
                    @endif
                    <td nowrap>
                        {{-- 独自追加の場合は、独自データの編集画面 --}}
                        @if ($holiday->orginal_holiday_status == 1)
                        <a href="{{url('/')}}/manage/holiday/edit/{{$holiday->orginal_holiday_post->id}}">
                        {{-- 計算値 or 計算値の場合は、編集画面 --}}
                        @else
                        <a href="{{url('/')}}/manage/holiday/overrideEdit/{{$holiday->format('Y-m-d')}}">
                        @endif
                            <span class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span class="hidden-xs">編集</span></span>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
