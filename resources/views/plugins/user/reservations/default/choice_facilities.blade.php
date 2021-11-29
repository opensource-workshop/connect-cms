{{--
 * 施設設定
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.reservations.reservations_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- エラーメッセージ --}}
@include('plugins.common.errors_all')

{{-- 登録後メッセージ表示 --}}
@include('plugins.common.flash_message')

{{-- メッセージエリア --}}
<div class="alert alert-info">
    <i class="fas fa-exclamation-circle"></i> 施設カテゴリ単位で表示する予約施設を設定します。<br />
    　施設や施設カテゴリ自体の登録は、管理者メニューの [ <a href="{{url('/')}}/manage/reservation">施設管理</a> ] から行えます。
</div>

<form action="{{url('/')}}/redirect/plugin/{{$frame->plugin_name}}/updateChoiceFacilities/{{$page->id}}/{{$frame_id}}/{{$reservation->id}}#frame-{{$frame->id}}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/{{$frame->plugin_name}}/choiceFacilities/{{$page->id}}/{{$frame_id}}/{{$reservation->id}}#frame-{{$frame_id}}">

    <div class="form-group table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr>
                    <th nowrap>表示</th>
                    <th nowrap style="width: 10%;">表示順 <span class="badge badge-danger">必須</span></th>
                    <th nowrap>施設カテゴリ</th>
                    <th nowrap>施設</th>
                </tr>
            </thead>
            <tbody>
            @foreach($reservations_categories as $category)
                <tr>
                    <td nowrap class="align-middle text-center">
                        <input type="hidden" value="{{$category->id}}" name="reservations_category_id[{{$category->id}}]">

                        <div class="custom-control custom-checkbox">
                            {{-- チェック外した場合にも値を飛ばす対応 --}}
                            <input type="hidden" value="0" name="view_flag[{{$category->id}}]">

                            <input type="checkbox" value="1" name="view_flag[{{$category->id}}]" class="custom-control-input" id="view_flag[{{$category->id}}]"@if (old('view_flag.'.$category->id, $category->view_flag)) checked="checked"@endif>
                            <label class="custom-control-label" for="view_flag[{{$category->id}}]"></label>
                        </div>
                    </td>
                    <td nowrap class="align-middle">
                        <input type="text" value="{{old('choice_display_sequence.'.$category->id, $category->choice_display_sequence)}}" name="choice_display_sequence[{{$category->id}}]" class="form-control @if ($errors && $errors->has('choice_display_sequence.'.$category->id)) border-danger @endif">
                    </td>
                    <td nowrap class="align-middle">{{$category->category}}</td>
                    <td class="align-middle">{{$category->facilities_name}}</td>
                </tr>
            @endforeach

            </tbody>
        </table>
    </div>

    <div class="form-group text-center">
        <a class="btn btn-secondary mr-2" href="{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}">
            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
        </a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更</button>
    </div>
</form>

@endsection
