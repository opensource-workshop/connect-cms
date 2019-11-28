{{--
 * 編集画面テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}

{{-- 機能選択タブ --}}
<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.contents.contents_frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

<form action="/plugin/contents/saveBuckets/{{$page->id}}/{{$frame->frame_id}}" name="contents_buckets_form" method="POST" class="mt-3">
    {{ csrf_field() }}

    <div class="form-group">
        <table class="table table-hover" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th></th>
                <th>投稿</th>
                <th>承認</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>記事追加権限</th>
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
            </tr>
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
            </tr>
            </tr>
        </tbody>
        </table>
    </div>

    <div class="form-group row mx-0">
        <div class="offset-md-3">
            <button type="button" class="btn btn-secondary form-horizontal mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i> キャンセル</button>
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
        </div>
    </div>
</form>

