{{--
 * 編集画面テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.' . $frame->plugin_name . '.' . $frame->plugin_name . '_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

@if($plugin_name == 'contents' || $buckets)
{{-- 固定記事プラグイン(=コンテンツプラグイン)はバケツありなし、どちらでも表示する。 --}}
{{-- 固定記事プラグイン(=コンテンツプラグイン)以外はバケツありのみ、表示する。 --}}

<form action="{{url('/')}}/plugin/{{$frame->plugin_name}}/saveBucketsRoles/{{$page->id}}/{{$frame->frame_id}}#frame-{{$frame_id}}" name="{{$frame->plugin_name}}_buckets_form" method="POST" class="mt-3">
    {{ csrf_field() }}

    <div class="form-group">
        <table class="table {{$frame->getSettingTableClass()}}">
        <thead>
            <tr>
                <th class="border-top-0"></th>
                <th class="border-top-0">投稿</th>
                @if($use_approval)
                <th class="border-top-0">承認</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>モデレータ</th>
                <td>
                    <div class="custom-control custom-checkbox custom-control-inline">
                        @if($buckets && $buckets->canPost("role_article"))
                            <input name="role_article[post]" value="1" type="checkbox" class="custom-control-input" id="role_article_post" checked="checked">
                        @else
                            <input name="role_article[post]" value="1" type="checkbox" class="custom-control-input" id="role_article_post">
                        @endif
                        <label class="custom-control-label" for="role_article_post">投稿できる</label>
                    </div>
                </td>

                @if($use_approval)
                <td>
                    <div class="custom-control custom-checkbox custom-control-inline">
                        @if($buckets && $buckets->needApproval("role_article"))
                            <input name="role_article[approval]" value="1" type="checkbox" class="custom-control-input" id="role_article_approval" checked="checked">
                        @else
                            <input name="role_article[approval]" value="1" type="checkbox" class="custom-control-input" id="role_article_approval">
                        @endif
                        <label class="custom-control-label" for="role_article_approval">承認が必要</label>
                    </div>
                </td>
                @endif
            </tr>
            <tr>
                <th>編集者権限</th>
                <td>
                    <div class="custom-control custom-checkbox custom-control-inline">
                        @if($buckets && $buckets->canPost("role_reporter"))
                            <input name="role_reporter[post]" value="1" type="checkbox" class="custom-control-input" id="role_reporter_post" checked="checked">
                        @else
                            <input name="role_reporter[post]" value="1" type="checkbox" class="custom-control-input" id="role_reporter_post">
                        @endif
                        <label class="custom-control-label" for="role_reporter_post">投稿できる</label>
                    </div>
                </td>

                @if($use_approval)
                <td>
                    <div class="custom-control custom-checkbox custom-control-inline">
                        @if($buckets && $buckets->needApproval("role_reporter"))
                            <input name="role_reporter[approval]" value="1" type="checkbox" class="custom-control-input" id="role_reporter_approval" checked="checked">
                        @else
                            <input name="role_reporter[approval]" value="1" type="checkbox" class="custom-control-input" id="role_reporter_approval">
                        @endif
                        <label class="custom-control-label" for="role_reporter_approval">承認が必要</label>
                    </div>
                </td>
                @endif
            </tr>
        </tbody>
        </table>
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 更新</button>
    </div>
</form>

@else
{{-- 表示しない。 --}}
<div class="alert alert-warning">
    <i class="fas fa-exclamation-circle"></i>
    表示するコンテンツを選択するか、新規作成してください。
</div>
@endif

@endsection
