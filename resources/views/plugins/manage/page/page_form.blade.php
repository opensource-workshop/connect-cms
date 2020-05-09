{{--
 * Page 編集画面(入力フォーム)
 *
 * 新規登録画面と変更画面を共有して使用しています。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 --}}

@if ($page->id)
<form action="{{url('/manage/page/update')}}/{{$page->id}}" method="POST" class="form-horizontal">
@else
<form action="{{url('/manage/page/store')}}" method="POST" class="form-horizontal">
@endif
    {{ csrf_field() }}

    <!-- Page form  -->
    <div class="form-group row @if ($errors && $errors->has('page_name')) has-error @endif">
        <label for="page_name" class="col-md-3 col-form-label text-md-right">ページ名</label>
        <div class="col-md-9">
            <input type="text" name="page_name" id="page_name" value="{{$page->page_name}}" class="form-control">
            @if ($errors && $errors->has('page_name')) <div class="text-danger">{{$errors->first('page_name')}}</div> @endif
        </div>
    </div>
    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">固定リンク</label>
        <div class="col-md-9">
            <input type="text" name="permanent_link" id="permanent_link" value="{{$page->permanent_link}}" class="form-control">
        </div>
    </div>

    <div class="form-group row mb-0">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">公開設定</label>
        <div class="col-md-9 d-sm-flex align-items-center">

            <div class="custom-control custom-radio custom-control-inline">
                @if ($page->membership_flag == 0)
                    <input type="radio" value="0" id="membership_flag_0" name="membership_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="membership_flag_0" name="membership_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="membership_flag_0">公開</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if ($page->membership_flag == 1)
                    <input type="radio" value="1" id="membership_flag_1" name="membership_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="membership_flag_1" name="membership_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="membership_flag_1">メンバーシップページ</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-3"></div>
        <div class="col-md-9 text-danger">メンバーシップページの下層のページもメンバーシップページになります。</div>
    </div>

    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">ページ権限設定</label>
        <div class="col-md-9 d-sm-flex align-items-center">
            <a href="{{url('/manage/page/role')}}/{{$page->id}}" class="btn btn-primary" target="_blank"><i class="fas fa-external-link-alt"></i> <span>権限設定画面へ</span></a>
        </div>
    </div>

    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">パスワード</label>
        <div class="col-md-9">
            <input type="text" name="password" id="password" value="{{$page->password}}" class="form-control">
            <small class="form-text text-muted">ページにパスワードで閲覧制限を設ける場合に使用します。</small>
        </div>
    </div>
    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">背景色</label>
        <div class="col-md-9">
            <input type="text" name="background_color" id="background_color" value="{{$page->background_color}}" class="form-control">
        </div>
    </div>
    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">ヘッダーの背景色</label>
        <div class="col-md-9">
            <input type="text" name="header_color" id="header_color" value="{{$page->header_color}}" class="form-control">
        </div>
    </div>

    {{-- テーマ --}}
    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">テーマ</label>
        <div class="col-md-9">
            <select name="theme" class="form-control">
                <option value="">設定なし</option>
                @foreach($themes as $theme)
                    @isset($theme['themes'])
                        <optgroup label="{{$theme['dir']}}">
                        @foreach($theme['themes'] as $sub_theme)
                            <option value="{{$sub_theme['dir']}}"@if($sub_theme['dir'] == $page->theme) selected @endif>{{$sub_theme['name']}}</option>
                        @endforeach
                        </optgroup>
                    @else
                        <option value="{{$theme['dir']}}"@if($theme['dir'] == $page->theme) selected @endif>{{$theme['name']}}</option>
                    @endisset
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">レイアウト</label>
        <div class="col-md-9">

            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '')
                    <input type="radio" value="" id="layout_null" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="layout_null" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_null"><img src="{{asset('/images/core/layout/null.png')}}" title="未設定" alt="未設定"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0000')
                    <input type="radio" value="0|0|0|0" id="layout_0000" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|0|0|0" id="layout_0000" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0000"><img src="{{asset('/images/core/layout/0000.png')}}" title="メインのみ" alt="メインのみ"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0001')
                    <input type="radio" value="0|0|0|1" id="layout_0001" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|0|0|1" id="layout_0001" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0001"><img src="{{asset('/images/core/layout/0001.png')}}" title="フッター" alt="フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0010')
                    <input type="radio" value="0|0|1|0" id="layout_0010" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|0|1|0" id="layout_0010" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0010"><img src="{{asset('/images/core/layout/0010.png')}}" title="右" alt="右"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0011')
                    <input type="radio" value="0|0|1|1" id="layout_0011" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|0|1|1" id="layout_0011" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0011"><img src="{{asset('/images/core/layout/0011.png')}}" title="右、フッター" alt="右、フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0100')
                    <input type="radio" value="0|1|0|0" id="layout_0100" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|1|0|0" id="layout_0100" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0100"><img src="{{asset('/images/core/layout/0100.png')}}" title="左" alt="左"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0101')
                    <input type="radio" value="0|1|0|1" id="layout_0101" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|1|0|1" id="layout_0101" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0101"><img src="{{asset('/images/core/layout/0101.png')}}" title="左、フッター" alt="左、フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0110')
                    <input type="radio" value="0|1|1|0" id="layout_0110" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|1|1|0" id="layout_0110" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0110"><img src="{{asset('/images/core/layout/0110.png')}}" title="左、右" alt="左、右"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0111')
                    <input type="radio" value="0|1|1|1" id="layout_0111" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|1|1|1" id="layout_0111" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0111"><img src="{{asset('/images/core/layout/0111.png')}}" title="左、右、フッター" alt="左、右、フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1000')
                    <input type="radio" value="1|0|0|0" id="layout_1000" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|0|0|0" id="layout_1000" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1000"><img src="{{asset('/images/core/layout/1000.png')}}" title="ヘッダー" alt="ヘッダー"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1001')
                    <input type="radio" value="1|0|0|1" id="layout_1001" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|0|0|1" id="layout_1001" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1001"><img src="{{asset('/images/core/layout/1001.png')}}" title="ヘッダー、フッター" alt="ヘッダー、フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1010')
                    <input type="radio" value="1|0|1|0" id="layout_1010" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|0|1|0" id="layout_1010" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1010"><img src="{{asset('/images/core/layout/1010.png')}}" title="ヘッダー、右" alt="ヘッダー、右"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1011')
                    <input type="radio" value="1|0|1|1" id="layout_1011" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|0|1|1" id="layout_1011" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1011"><img src="{{asset('/images/core/layout/1011.png')}}" title="ヘッダー、右、フッター" alt="ヘッダー、右、フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1100')
                    <input type="radio" value="1|1|0|0" id="layout_1100" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|1|0|0" id="layout_1100" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1100"><img src="{{asset('/images/core/layout/1100.png')}}" title="ヘッダー、左" alt="ヘッダー、左"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1101')
                    <input type="radio" value="1|1|0|1" id="layout_1101" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|1|0|1" id="layout_1101" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1101"><img src="{{asset('/images/core/layout/1101.png')}}" title="ヘッダー、左、フッター" alt="ヘッダー、左、フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1110')
                    <input type="radio" value="1|1|1|0" id="layout_1110" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|1|1|0" id="layout_1110" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1110"><img src="{{asset('/images/core/layout/1110.png')}}" title="ヘッダー、左、右" alt="ヘッダー、左、右"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1111')
                    <input type="radio" value="1|1|1|1" id="layout_1111" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|1|1|1" id="layout_1111" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1111"><img src="{{asset('/images/core/layout/1111.png')}}" title="ヘッダー、左、右、フッター" alt="ヘッダー、左、右、フッター"></label>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">メニュー表示</label>
        <div class="col-md-9 d-sm-flex align-items-center">

            <div class="custom-control custom-checkbox">
                @if(isset($page->base_display_flag) && $page->base_display_flag == 1)
                    <input name="base_display_flag" value="1" type="checkbox" class="custom-control-input" id="base_display_flag" checked="checked">
                @else
                    <input name="base_display_flag" value="1" type="checkbox" class="custom-control-input" id="base_display_flag">
                @endif
                <label class="custom-control-label" for="base_display_flag">表示する</label>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">ウィンドウ</label>
        <div class="col-md-9 d-sm-flex align-items-center">

            <div class="custom-control custom-checkbox">
                @if(isset($page->othersite_url_target) && $page->othersite_url_target == 1)
                    <input name="othersite_url_target" value="1" type="checkbox" class="custom-control-input" id="othersite_url_target" checked="checked">
                @else
                    <input name="othersite_url_target" value="1" type="checkbox" class="custom-control-input" id="othersite_url_target">
                @endif
                <label class="custom-control-label" for="othersite_url_target">新しいウィンドウまたはタブで開く</label>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">IPアドレス制限</label>
        <div class="col-md-9">
            <input type="text" name="ip_address" id="ip_address" value="{{$page->ip_address}}" class="form-control">
            <small class="form-text text-muted">カンマで複数、CIDR形式での指定可能、*での指定は不可</small>
        </div>
    </div>
    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">外部サイトURL</label>
        <div class="col-md-9">
            <input type="text" name="othersite_url" id="othersite_url" value="{{$page->othersite_url}}" class="form-control">
            <small class="form-text text-muted">メニューから直接開く外部サイトURL</small>
        </div>
    </div>
    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">クラス名</label>
        <div class="col-md-9">
            <input type="text" name="class" id="class" value="{{$page->class}}" class="form-control">
            <small class="form-text text-muted">デザインで使用するためのclass名</small>
        </div>
    </div>

    {{-- ボタンエリア --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-xl-3"></div>
            <div class="col-9 col-xl-6 mx-auto">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/page')}}'"><i class="fas fa-times"></i> キャンセル</button>
                <button type="submit" class="btn btn-primary form-horizontal">
                    <i class="fas fa-check"></i> @if ($page->id)ページ更新 @else ページ追加 @endif
                </button>
            </div>
            {{-- 編集モード＆ページがトップページではない --}}
            @if ($page->id && $pages && $pages->first()->id != $page->id)
            <div class="col-3 col-xl-3 text-right">
                    <a data-toggle="collapse" href="#collapse{{$page->id}}">
                        <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="d-none d-md-inline"> 削除</span></span>
                    </a>
            </div>
            @else
            <div class="col-xl-3"></div>
            @endif
        </div>
    </div>
</form>
