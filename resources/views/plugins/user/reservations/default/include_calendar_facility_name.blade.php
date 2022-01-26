{{--
 * 施設名
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 *
 * @param $action string アクション名
--}}
@if ($facility_display_type == FacilityDisplayType::all)
    <span class="h5">＜{{ $facility_name }}＞</span>{{-- <button type="button" class="btn btn-link pl-0 pr-0">詳細</button> --}}
@else
    <form action="{{url('/')}}/plugin/reservations/{{$action}}/{{$page->id}}/{{$frame_id}}/{{ $carbon_target_date->format('Ymd') }}#frame-{{$frame_id}}" method="get" role="search" aria-label="{{$reservations->reservation_name}}" class="form-inline pb-2">
        {{-- onChange="javascript:submit(this.form);" --}}
        <select class="form-control" name="initial_facility" title="施設名" aria-describedby="initial_facility{{$frame_id}}">
            @foreach ($facilities as $facility)
                <option value="{{$facility->id}}" @if(old('initial_facility', $initial_facility) == $facility->id) selected="selected" @endif>{{$facility->facility_name}}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline-primary ml-1">切替</button>
        {{-- <button type="button" class="btn btn-link">詳細</button> --}}
    </form>
@endif
