{{--
 * カウント一覧画面テンプレート
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.counters.counters_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

<div class="form-group">
    <table class="table table-hover table-sm table-striped">
        <thead>
            <tr>
                <th>日付</th>
                <th>カウント数</th>
                <th>累計</th>
            </tr>
        </thead>
        <tbody>
            @foreach($counter_counts as $counter_count)
                <tr>
                    @php
                        // 曜日class
                        $day_of_week_class = '';
                        if ($counter_count->counted_at->dayOfWeek == 0) {
                            $day_of_week_class = 'cc-color-sunday';
                        } elseif ($counter_count->counted_at->dayOfWeek == 6) {
                            $day_of_week_class = 'cc-color-saturday';
                        }
                    @endphp

                    <td class="{{$day_of_week_class}}">{{ $counter_count->counted_at->isoFormat('YYYY/MM/DD (ddd)') }}</td>
                    <td>{{$counter_count->day_count}}</td>
                    <td>{{$counter_count->total_count}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ページング処理 --}}
{{-- アクセシビリティ対応。1ページしかない時に、空navを表示するとスクリーンリーダーに不要な Navigation がひっかかるため表示させない。 --}}
@if ($counter_counts->lastPage() > 1)
    <nav class="text-center mt-3" aria-label="{{$counter->name}}のページ付け">
        {{ $counter_counts->fragment('frame-' . $frame_id)->links() }}
    </nav>
@endif

{{-- ボタン --}}
<div class="form-group text-center mt-3">
    <div class="row">
        <div class="col">
            <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}">
                <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">カウンター選択へ</span></span>
            </a>
        </div>
    </div>
</div>

@endsection
