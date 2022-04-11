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
    <span class="h5">＜{{ $facility_name }}＞</span>
    <a href="#facilityDetailModal{{$frame_id}}" role="button" data-toggle="modal" data-facility_id="{{$calendar_details['facility']->id}}">
        {{__('messages.detail')}}
    </a>
@else
    @php $before_categories_id = null; @endphp

    <form action="{{url('/')}}/plugin/reservations/{{$action}}/{{$page->id}}/{{$frame_id}}/{{ $carbon_target_date->format('Ymd') }}#frame-{{$frame_id}}" method="get" role="search" aria-label="{{$reservations->reservation_name}}" class="form-inline pb-2" onchange="javascript:submit(this.form);">
        <select class="form-control" name="initial_facility" title="施設名" aria-describedby="initial_facility{{$frame_id}}">
            @foreach ($facilities as $facility)
                @if ($facility->reservations_categories_id != $before_categories_id)
                    {{-- 初回以外 閉じタグ表示 --}}
                    @if (!is_null($before_categories_id)) </optgroup> @endif

                    <optgroup label="{{$facility->category}}">

                    @php $before_categories_id = $facility->reservations_categories_id; @endphp
                @endif
                <option value="{{$facility->id}}" @if(old('initial_facility', $initial_facility) == $facility->id) selected="selected" @endif>{{$facility->facility_name}}</option>
            @endforeach

            {{-- 最後の閉じタグ --}}
            </optgroup>
        </select>
        {{-- <button type="submit" class="btn btn-outline-primary ml-1">{{__('messages.switch')}}</button> --}}
        <a href="#facilityDetailModal{{$frame_id}}" role="button" data-toggle="modal" class="btn btn-outline-primary btn-sm ml-1" data-facility_id="{{$initial_facility}}">
            {{__('messages.detail')}}
        </a>
    </form>
@endif
