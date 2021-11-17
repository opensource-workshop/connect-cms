{{--
 * 施設管理の施設一覧
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.reservation.reservation_manage_tab')
    </div>
    <div class="card-body">

        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        {{-- 一覧エリア --}}
        <div class="text-right"><span class="badge badge-pill badge-light">{{ $facilities->total() }} 件</span></div>
        <table class="table table-hover cc-font-90">
            <tbody>
                <tr class="d-none d-sm-table-row">
                    <th class="d-block d-sm-table-cell text-break">施設名</th>
                    <th class="d-block d-sm-table-cell text-break">表示順</th>
                    <th class="d-block d-sm-table-cell text-break">表示</th>
                    <th class="d-block d-sm-table-cell text-break">カテゴリ</th>
                </tr>

                @php
                    // 一つ前の施設カテゴリID。ループして変わった時だけカテゴリ表示
                    $befor_reservations_categories_id = null;
                @endphp
                @foreach($facilities as $facility)
                    @if ($facility->reservations_categories_id != $befor_reservations_categories_id)
                        <tr>
                            <th nowrap colspan="4"><div class="h5 mb-0"><span class="badge badge-secondary">{{$facility->category}}</span></div></th>
                        </tr>
                        @php
                           $befor_reservations_categories_id = $facility->reservations_categories_id;
                        @endphp
                    @endif
                    <tr class="@if ($facility->hide_flag) bg-warning @endif">
                        <td class="d-block d-sm-table-cell">
                            <a href="{{url('/')}}/manage/reservation/edit/{{$facility->id}}"><i class="far fa-edit"></i></a>
                            <span class="d-sm-none">注釈名：</span>{{$facility->facility_name}}
                        </td>
                        <td class="d-block d-sm-table-cell"><span class="d-sm-none">表示順：</span>{{$facility->display_sequence}}</td>
                        <td class="d-block d-sm-table-cell"><span class="d-sm-none">表示：</span>{{ NotShowType::getDescriptionNullSupport($facility->hide_flag) }}</td>
                        <td class="d-block d-sm-table-cell"><span class="d-sm-none">カテゴリ：</span><span class="badge badge-secondary">{{$facility->category}}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $facilities->links() }}

    </div>
</div>

@endsection
