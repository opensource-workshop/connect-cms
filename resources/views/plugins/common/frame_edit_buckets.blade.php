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
    {{$frame->includeFrameTab($page, $frame, $action)}}
@endsection

@section("plugin_setting_$frame->id")

<form action="{{url('/')}}/plugin/{{$frame->plugin_name}}/saveBucketsRoles/{{$page->id}}/{{$frame->frame_id}}#frame-{{$frame_id}}" name="{{$frame->plugin_name}}_buckets_form" method="POST">
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
                <th>編集者</th>
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
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span></button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 更新</button>
    </div>
</form>

@endsection
