{{--
 * グループ権限設定画面のテンプレート
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

                        <div class="card border-warning mb-3">
                            <div class="card-header bg-warning">実装状況</div>
                            <div class="card-body">
                                ※ 2020-07-26 現在、ページ権限は以下の対応となっています。
                                <ul>
                                    <li> メンバーシップページに参加するための判定で使用可能（どの権限でも同じ）。
                                    <li> 各権限を用いての基本権限の上書きはまだ（今後の実装予定）
                                </ul>
                            </div>
                        </div>

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

                            <div class="form-group text-center row">
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
            <div class="col-12">
{{-- bugfix: グループ権限はページ一覧から遷移するため、キャンセルで表示するページはページ一覧に修正
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/page/edit')}}/{{$page->id}}'">
--}}
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/page')}}'">
                    <i class="fas fa-times"></i> キャンセル
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
