{{--
 * ページのグループ権限設定画面のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.page.page_manage_tab')
    </div>
    <div class="card-body">

        <div class="alert alert-info" style="margin-top: 10px;">
            ページ名：{{$page->page_name}}
        </div>

        <div class="accordion" id="accordion" role="tablist" aria-multiselectable="true">
            @foreach($groups as $group)
            <div class="card">
                <div class="card-header" role="tab" id="heading{{$group->id}}">
                    @if($group->id == $group_id)
                    <a class="text-body d-block p-3 m-n3 text-decoration-none" data-toggle="collapse" href="#collapse{{$group->id}}" role="button" aria-expanded="false" aria-controls="collapse{{$group->id}}">
                    @else
                    <a class="collapsed text-body d-block p-3 m-n3 text-decoration-none" data-toggle="collapse" href="#collapse{{$group->id}}" role="button" aria-expanded="false" aria-controls="collapse{{$group->id}}">
                    @endif
                        {{$group->name}}：
                        @if(empty($group->getRoleNames()))
                            <span class="badge badge-secondary">権限なし</span>
                        @else
                        @foreach($group->getRoleNames() as $role_name)
                            <span class="badge badge-primary">{{$role_name}}</span>
                        @endforeach
                        @endif
                    </a>
                </div><!-- /.card-header -->
                @if($group->id == $group_id)
                <div id="collapse{{$group->id}}" class="collapse show" role="tabpanel" aria-labelledby="heading{{$group->id}}" data-parent="#accordion">
                @else
                <div id="collapse{{$group->id}}" class="collapse" role="tabpanel" aria-labelledby="heading{{$group->id}}" data-parent="#accordion">
                @endif
                    <div class="card-body">

                        <form class="form-horizontal" method="POST" action="{{url('/manage/page/saveRole/')}}/{{$page->id}}">
                            {{csrf_field()}}
                            <input type="hidden" name="group_id" value="{{$group->id}}">

                            <div class="custom-control custom-checkbox">
                                @if($group->hasRole('role_article_admin'))
                                    <input name="role_article_admin" value="1" type="checkbox" class="custom-control-input" id="role_article_admin{{$group->id}}" checked="checked">
                                @else
                                    <input name="role_article_admin" value="1" type="checkbox" class="custom-control-input" id="role_article_admin{{$group->id}}">
                                @endif
                                <label class="custom-control-label" for="role_article_admin{{$group->id}}">コンテンツ管理者</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                @if($group->hasRole('role_arrangement'))
                                    <input name="role_arrangement" value="1" type="checkbox" class="custom-control-input" id="role_arrangement{{$group->id}}" checked="checked">
                                @else
                                    <input name="role_arrangement" value="1" type="checkbox" class="custom-control-input" id="role_arrangement{{$group->id}}">
                                @endif
                                <label class="custom-control-label" for="role_arrangement{{$group->id}}">プラグイン管理者</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                @if($group->hasRole('role_article'))
                                    <input name="role_article" value="1" type="checkbox" class="custom-control-input" id="role_article{{$group->id}}" checked="checked">
                                @else
                                    <input name="role_article" value="1" type="checkbox" class="custom-control-input" id="role_article{{$group->id}}">
                                @endif
                                <label class="custom-control-label" for="role_article{{$group->id}}">モデレータ（他ユーザの記事も更新）</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                @if($group->hasRole('role_approval'))
                                    <input name="role_approval" value="1" type="checkbox" class="custom-control-input" id="role_approval{{$group->id}}" checked="checked">
                                @else
                                    <input name="role_approval" value="1" type="checkbox" class="custom-control-input" id="role_approval{{$group->id}}">
                                @endif
                                <label class="custom-control-label" for="role_approval{{$group->id}}">承認者</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                @if($group->hasRole('role_reporter'))
                                    <input name="role_reporter" value="1" type="checkbox" class="custom-control-input" id="role_reporter{{$group->id}}" checked="checked">
                                @else
                                    <input name="role_reporter" value="1" type="checkbox" class="custom-control-input" id="role_reporter{{$group->id}}">
                                @endif
                                <label class="custom-control-label" for="role_reporter{{$group->id}}">編集者</label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                @if($group->hasRole('role_guest'))
                                    <input name="role_guest" value="1" type="checkbox" class="custom-control-input" id="role_guest{{$group->id}}" checked="checked">
                                @else
                                    <input name="role_guest" value="1" type="checkbox" class="custom-control-input" id="role_guest{{$group->id}}">
                                @endif
                                <label class="custom-control-label" for="role_guest{{$group->id}}">ゲスト</label>
                            </div>

                            <small class="text-muted">
                                ※ 権限設定してページを表示すると、ユーザ権限はこの権限で上書きされます。<br />
                                　例）「コンテンツ管理者」ユーザがグループ1, 2に所属していて、ページのグループ権限でグループ1（編集者）, 所属グループ2（モデレータ）の場合、そのページでの権限は「編集者」「モデレータ」に上書きされます。<br />
                                ※ 子ページがある場合、この権限を引継ぎます。<br />
                                ※ メンバーシップページを参照するには、何らかの権限を設定してください。<br />
                                ※「コンテンツ管理者」は、「コンテンツ管理者」権限と同時に「プラグイン管理者」「モデレータ」「承認者」「編集者」権限も併せて持ちます。
                            </small>

                            <div class="form-group text-center row mt-2">
                                <div class="col-12">
                                    <button type="button" class="btn btn-secondary mr-2" onclick="$('#collapse{{$group->id}}').collapse('hide');">
                                        <i class="fas fa-times"></i> キャンセル
                                    </button>
                                    <button type="submit" class="btn btn-primary form-horizontal">
                                        <i class="fas fa-check"></i> 権限更新
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div><!-- /.card-body -->
                </div><!-- /.collapse -->
            </div><!-- /.card -->
            @endforeach
        </div><!-- /.accordion -->
        <div class="text-center row mt-3">
            <div class="col">
                <a href="{{url('/manage/page/edit')}}/{{$page->id}}" class="btn btn-secondary mr-2">
                    <i class="fas fa-chevron-left"></i> ページ変更へ
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
