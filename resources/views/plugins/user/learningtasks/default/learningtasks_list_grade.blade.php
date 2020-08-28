{{--
 * 課題管理成績一覧画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
 --}}
@extends('core.cms_frame_base')

{{-- 編集画面側のフレームメニュー --}}
@include('plugins.user.learningtasks.learningtasks_setting_edit_tab')

@section("plugin_contents_$frame->id")

<div class="card mb-3 border-danger">
    <div class="card-body">
        <h5 class="mb-0">{!!$learningtasks_posts->post_title!!}</h5>
    </div>
</div>

<table class="table table-bordered">
    @foreach ($statuses as $csv_line)
        @if ($loop->index == 0)
        <thead>
            <tr>
                @foreach ($csv_line as $csv_column)
                @if (!$loop->first)
                <th>{{$csv_column}}</th>
                @endif
                @endforeach
            </tr>
        </thead>
        @else
            @if ($loop->index == 1)
                <tbody>
            @endif
            <tr>
                @foreach ($csv_line as $csv_column)
                @if (!$loop->first)
                <td>{{$csv_column}}</td>
                @endif
                @endforeach
            </tr>
        @endif
    @endforeach
    </tbody>
</table>

<form action="{{url('/')}}/redirect/plugin/learningtasks/downloadGrade/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_users_post">
    {{ csrf_field() }}
    <input type="hidden" name="return_mode" value="asis">

    <div class="form-group">
        <div class="row">
            <div class="col-12">
                <div class="text-center">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/learningtasks/edit/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span> キャンセル</span></button>
                    <input type="hidden" name="bucket_id" value="">
                    <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('CSV出力します。\nよろしいですか？')"><i class="fas fa-check"></i> CSV出力</button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection
