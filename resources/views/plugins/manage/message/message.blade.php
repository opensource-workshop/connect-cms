{{--
 * 初回確認メッセージ管理のメインテンプレート
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メッセージ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.message.message_tab')
    </div>

    <div class="card-body">
        <form name="form_message_first" method="post" action="{{url('/')}}/manage/message/update">
            {{ csrf_field() }}

            {{-- 初回確認メッセージの利用有無 --}}
            <div class="form-group">
                <label class="col-form-label">表示の有無</label>
                <div class="row">
                    @foreach (ShowType::getMembers() as $enum_value => $enum_label)
                        <div class="col-md-3">
                            <div class="custom-control custom-radio custom-control-inline">
                                @php $message_first_show_type = Configs::getConfigsValueAndOld($configs, 'message_first_show_type', ShowType::getDefault()); @endphp
                                @if ($message_first_show_type == $enum_value)
                                    <input type="radio" value="{{$enum_value}}" id="message_first_show_type_{{$enum_value}}" name="message_first_show_type" class="custom-control-input" checked="checked">
                                @else
                                    <input type="radio" value="{{$enum_value}}" id="message_first_show_type_{{$enum_value}}" name="message_first_show_type" class="custom-control-input">
                                @endif
                                {{-- duskでradioの選択にlabelのid必要 --}}
                                <label class="custom-control-label" for="message_first_show_type_{{$enum_value}}" id="label_message_first_show_type_{{$enum_value}}">{{$enum_label}}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <small class="form-text text-muted">※サイト訪問者にポップアップメッセージを表示するか選択します。（用例：利用規約、プライバシーポリシーへの同意等）</small>
                <small class="form-text text-muted">※本機能はCookieを使用します。Cookieの取り扱いについてはサイトポリシーで検討の上、ご利用ください。</small>
                <small class="form-text text-muted">※メッセージ内のボタン押下で訪問者ブラウザにCookieをセットし、Cookieセット後はポップアップメッセージは表示されません。</small>
            </div>

            {{-- ウィンドウ外クリックによる離脱許可 --}}
            <div class="form-group">
                <label class="col-form-label">ウィンドウ外クリックによる離脱</label>
                <div class="row">
                    @foreach (PermissionType::getMembers() as $enum_value => $enum_label)
                        <div class="col-md-3">
                            <div class="custom-control custom-radio custom-control-inline">
                                @php $message_first_permission_type = Configs::getConfigsValueAndOld($configs, 'message_first_permission_type', PermissionType::getDefault()); @endphp
                                @if ($message_first_permission_type == $enum_value)
                                    <input type="radio" value="{{$enum_value}}" id="message_first_permission_type_{{$enum_value}}" name="message_first_permission_type" class="custom-control-input" checked="checked">
                                @else
                                    <input type="radio" value="{{$enum_value}}" id="message_first_permission_type_{{$enum_value}}" name="message_first_permission_type" class="custom-control-input">
                                @endif
                                {{-- duskでradioの選択にlabelのid必要 --}}
                                <label class="custom-control-label" for="message_first_permission_type_{{$enum_value}}" id="label_message_first_permission_type_{{$enum_value}}">{{$enum_label}}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <small class="form-text text-muted">※メッセージウィンドウ外クリックによる離脱を許可するか選択します。</small>
            </div>

            {{-- メッセージ内容 --}}
            <div class="form-group">
                <label class="control-label">メッセージ内容</label>
                <textarea name="message_first_content" class="form-control" rows=5 placeholder="（例）当サイトではトラフィック分析を目的として、クッキー(Cookie)を利用しています。当サイトの閲覧を続けた場合、クッキーの利用に同意いただいたことになります。詳しくはプライバシーポリシーをご覧ください。">{{ Configs::getConfigsValueAndOld($configs, 'message_first_content', null) }}</textarea>
                <small class="form-text text-muted">
                    ※ポップアップに表示するメッセージを設定します。HTML入力が可能です。scriptタグは使用できません。<br />
                    ※メッセージ内容を変更した場合、すでに同意済みの訪問者にも再度ポップアップメッセージを表示します。
                </small>
            </div>

            {{-- ボタン名 --}}
            <div class="form-group">
                <label class="col-form-label">ボタン名</label>
                <input type="text" name="message_first_button_name" value="{{ Configs::getConfigsValueAndOld($configs, 'message_first_button_name', null) }}" class="form-control">
                <small class="form-text text-muted">※ポップアップに表示するボタン名を設定します。</small>
            </div>

            {{-- 除外URL --}}
            <div class="form-group">
                <label class="col-form-label">除外URL</label>
                <input type="text" name="message_first_exclued_url" value="{{ Configs::getConfigsValueAndOld($configs, 'message_first_exclued_url', null) }}" class="form-control" placeholder="（例）/about,/policy">
                <small class="form-text text-muted">※メッセージ表示を除外するURLを設定します。「,」区切りで複数設定できます。</small>
            </div>

            {{-- メッセージエリア任意クラス --}}
            <div class="form-group">
                <label class="col-form-label">メッセージエリア任意クラス</label>
                <input type="text" name="message_first_optional_class" value="{{ Configs::getConfigsValueAndOld($configs, 'message_first_optional_class', null) }}" class="form-control">
                <small class="form-text text-muted">※メッセージウィンドウに任意のclass属性を設定します。</small>
            </div>

            {{-- 更新ボタン --}}
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 更新</button>
            </div>
        </form>
    </div>
</div>
@endsection
