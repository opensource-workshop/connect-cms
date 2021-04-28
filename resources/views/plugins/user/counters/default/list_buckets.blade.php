{{--
 * 編集画面(データ選択)テンプレート
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.counters.counters_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

<form action="{{url('/')}}/redirect/plugin/counters/changeBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/counters/listBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
    <div class="form-group">
        <table class="table table-hover {{$frame->getSettingTableClass()}}">
        <thead>
            <tr>
                <th></th>
                <th>カウンター名</th>
                <th>作成日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($plugin_buckets as $plugin_bucket)
            <tr @if ($plugin_bucket->bucket_id == $frame->bucket_id) class="cc-active-tr"@endif>
                <td>
                    <input type="radio" value="{{$plugin_bucket->bucket_id}}" name="select_bucket"@if ($plugin_bucket->bucket_id == $frame->bucket_id) checked @endif>
                    <span class="{{$frame->getSettingCaptionClass()}}">{{$plugin_bucket->name}}</span>
                </td>
                <td>{{$plugin_bucket->name}}</td>
                <td>{{$plugin_bucket->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <div class="text-center">
        {{ $plugin_buckets->fragment('frame-' . $frame_id)->links() }}
    </div>

    <div class="form-group text-center mt-3">
        <a href="{{URL::to($page->permanent_link)}}" class="btn btn-secondary mr-2">
            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
        </a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示カウンター変更</button>
    </div>
</form>

@endsection
