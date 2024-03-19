{{--
 * Page 編集画面(入力フォーム)
 *
 * 新規登録画面と変更画面を共有して使用しています。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
--}}
@php
use App\Models\Common\Page;
@endphp

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

@if ($page->id)
<form action="{{url('/manage/page/update')}}/{{$page->id}}" method="POST" class="form-horizontal">
@else
<form action="{{url('/manage/page/store')}}" method="POST" class="form-horizontal">
@endif
    {{ csrf_field() }}

    @php
    // 自分のページから親を遡って取得（＋トップページ）
    $page_tree = $page->getPageTreeByGoingBackParent(null);
    @endphp

    <!-- Page form  -->
    <div class="form-group row @if ($errors && $errors->has('page_name')) has-error @endif">
        <label for="page_name" class="col-md-3 col-form-label text-md-right">ページ名 <span class="badge badge-danger">必須</span></label>
        <div class="col-md-9">
            <input type="text" name="page_name" id="page_name" value="{{old('page_name', $page->page_name)}}" class="form-control @if ($errors->has('page_name')) border-danger @endif">
            @include('plugins.common.errors_inline', ['name' => 'page_name'])
        </div>
    </div>
    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">固定リンク</label>
        <div class="col-md-9">
            <input type="text" name="permanent_link" id="permanent_link" value="{{old('permanent_link', $page->permanent_link)}}" class="form-control @if ($errors->has('permanent_link')) border-danger @endif">
            @include('common.errors_inline', ['name' => 'permanent_link'])
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="col-md-3 col-form-label text-md-right">限定公開設定</label>
        <div class="col-md-9 d-sm-flex align-items-center">

            <div class="custom-control custom-radio custom-control-inline">
                @if ($page->membership_flag == 0)
                    <input type="radio" value="0" id="membership_flag_0" name="membership_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="membership_flag_0" name="membership_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="membership_flag_0">設定なし</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if ($page->membership_flag == 1)
                    <input type="radio" value="1" id="membership_flag_1" name="membership_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="membership_flag_1" name="membership_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="membership_flag_1">メンバーシップページ</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if ($page->membership_flag == 2)
                    <input type="radio" value="2" id="membership_flag_2" name="membership_flag" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="2" id="membership_flag_2" name="membership_flag" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="membership_flag_2">ログインユーザ全員参加</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-3"></div>
        <div class="col mx-0">

            @php
            // 自分及び先祖ページを遡る
            $membership_page_parent = new Page();
            foreach ($page_tree as $page_tmp) {
                if ($page_tmp->membership_flag) {
                    $membership_page_parent = $page_tmp;
                    break;
                }
            }
            @endphp
            {{-- 公開設定が公開以外＆親ページありなら --}}
            @if (!$page->membership_flag && $membership_page_parent->id)
                <div class="alert alert-warning small mb-0">
                    設定なしのため、親ページ「<a href="{{url('/manage/page/edit')}}/{{$membership_page_parent->id}}" target="_blank">{{$membership_page_parent->page_name}} <i class="fas fa-external-link-alt"></i></a>」の公開設定「{{MembershipFlag::getDescription($membership_page_parent->membership_flag)}}」を継承しています。<br />
                </div>
            @endif

            <small class="form-text text-muted">
                ※ メンバーシップページの下層のページもメンバーシップページになります。<br />
                ※ ページ及び、メンバーシップページの権限設定は「<a href="{{url('/manage/page/role')}}/{{$page->id}}" target="_blank">ページ変更＞ページ権限設定 <i class="fas fa-external-link-alt"></i></a>」で設定できます。
            </small>
        </div>
    </div>

    {{-- navbar メニューの「その他設定」プルダウン内に移動
    <div class="form-group row">
        <label for="permanent_link" class="col-md-3 col-form-label text-md-right">ページ権限設定</label>
        <div class="col-md-9 d-sm-flex align-items-center">
            <a href="{{url('/manage/page/role')}}/{{$page->id}}" class="btn btn-primary"><i class="fas fa-external-link-alt"></i> <span>権限設定画面へ</span></a>
        </div>
    </div>
    --}}

    @if (config('connect.USE_CONTAINER_BETA'))
        <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-right pt-0">コンテナ</label>
            <div class="col-md-9 d-sm-flex align-items-center">

                <div class="custom-control custom-checkbox">
                    @if(isset($page->container_flag) && $page->container_flag == 1)
                        <input name="container_flag" value="1" type="checkbox" class="custom-control-input" id="container_flag" checked="checked">
                    @else
                        <input name="container_flag" value="1" type="checkbox" class="custom-control-input" id="container_flag">
                    @endif
                    <label class="custom-control-label" for="container_flag">ページをコンテナとして使う</label>

                    @php
                    // 自分及び先祖ページを遡る
                    $container_page_parent = new Page();
                    foreach ($page_tree as $page_tmp) {
                        if ($page_tmp->container_flag) {
                            $container_page_parent = $page_tmp;
                            break;
                        }
                    }
                    @endphp
                    @if (!$page->container_flag && $container_page_parent->id)
                        <div class="alert alert-warning small mb-0">
                            未設定のため、親ページ「<a href="{{url('/manage/page/edit')}}/{{$container_page_parent->id}}" target="_blank">{{$container_page_parent->page_name}} <i class="fas fa-external-link-alt"></i></a>」のコンテナ「ページをコンテナとして使う」を継承しています。<br />
                        </div>
                    @endif

                    <small class="form-text text-muted">
                        ※ コンテナページにした場合、各プラグインの設定＞選択画面で、コンテナページで作成したバケツのみ表示します。<br />
                        ※ コンテナページの下層のページもコンテナページになります。<br />
                    </small>
                    <div class="alert alert-warning small mb-0">
                        【注意】<br />
                        以下のコンテナに対する注意点を理解して設定してください。<br />
                        <br />
                        コンテナページは、これから追加するページのみに設定してください。<br />
                        下記の場合、「既に作成していたデータは 各プラグインの設定＞選択画面 で選択できなくなる」「既に配置していたフレームは設定変更できなくなる」事を理解し、設定してください。<br />
                        ・既存ページをコンテナページにする。<br />
                        ・コンテナページにしたページを途中から「ページをコンテナとして使わない」設定にする。<br />
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="form-group row">
        <label for="password" class="col-md-3 col-form-label text-md-right">パスワード</label>
        <div class="col-md-9">
            <input type="text" name="password" id="password" value="{{old('password', $page->password)}}" class="form-control">
            @include('common.errors_inline', ['name' => 'password'])

            @php
            // 自分及び先祖ページを遡る
            $password_page_parent = new Page();
            foreach ($page_tree as $page_tmp) {
                if ($page_tmp->password) {
                    $password_page_parent = $page_tmp;
                    break;
                }
            }
            @endphp
            @if (!$page->password && $password_page_parent->id)
                <div class="alert alert-warning small mb-0">
                    設定なしのため、親ページ「<a href="{{url('/manage/page/edit')}}/{{$password_page_parent->id}}" target="_blank">{{$password_page_parent->page_name}} <i class="fas fa-external-link-alt"></i></a>」のパスワード「{{$password_page_parent->password}}」を継承しています。<br />
                </div>
            @endif

            <small class="form-text text-muted">※ ページにパスワードで閲覧制限を設ける場合に使用します。</small>
        </div>
    </div>
    <div id="app">
        @php
            // IEか判定
            $ua = $_SERVER['HTTP_USER_AGENT'];
            $is_ie = false;
            $placeholder_message = 'HTMLカラーコードを入力';
            if (strstr($ua, 'Trident') || strstr($ua, 'MSIE')) {
                $is_ie = true;
            }
        @endphp
        <div class="form-group row">
            <label for="background_color" class="col-md-3 col-form-label text-md-right">背景色</label>
            <div class="col-md-9">
                <input type="text" name="background_color" id="background_color" value="{{old('background_color', $page->background_color)}}" class="form-control" v-model="v_background_color" placeholder="{{ $placeholder_message }}">
                @include('common.errors_inline', ['name' => 'background_color'])
                @if (!$is_ie)
                    {{-- IEなら表示しない --}}
                    <input type="color" v-model="v_background_color">
                    <small class="text-muted">※ 左のカラーパレットから選択することも可能です。</small>
                @endif
            </div>
        </div>
        <div class="form-group row">
            <label for="header_color" class="col-md-3 col-form-label text-md-right">ヘッダーバーの背景色</label>
            <div class="col-md-9">
                <input type="text" name="header_color" id="header_color" value="{{old('header_color', $page->header_color)}}" class="form-control" v-model="v_header_color" placeholder="{{ $placeholder_message }}">
                @include('common.errors_inline', ['name' => 'header_color'])
                @if (!$is_ie)
                    {{-- IEなら表示しない --}}
                    <input type="color" v-model="v_header_color">
                    <small class="text-muted">※ 左のカラーパレットから選択することも可能です。</small>
                @endif
            </div>
        </div>
    </div>

    {{-- テーマ --}}
    <div class="form-group row">
        <label class="col-md-3 col-form-label text-md-right">テーマ</label>
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

            @php
            // 自分及び先祖ページを遡る
            $theme_page_parent = new Page();
            $base_theme = Configs::getSharedConfigsValue("base_theme", null);
            // 自分及び先祖ページを遡る
            foreach ($page_tree as $page_tmp) {
                if ($page_tmp->theme) {
                    $theme_page_parent = $page_tmp;
                    break;
                }
            }
            @endphp
            {{-- 公開設定が公開以外＆親ページありなら --}}
            @if (!$page->theme)
                @if ($theme_page_parent->theme)
                    <div class="alert alert-warning small mb-0">
                        設定なしのため、親ページ「<a href="{{url('/manage/page/edit')}}/{{$theme_page_parent->id}}" target="_blank">{{$theme_page_parent->page_name}} <i class="fas fa-external-link-alt"></i></a>」のテーマ「{{$theme_page_parent->theme}}」を継承しています。<br />
                    </div>
                @elseif ($base_theme)
                    <div class="alert alert-warning small mb-0">
                        設定なしのため、「サイト管理＞<a href="{{url('/manage/site')}}" target="_blank">サイト基本設定 <i class="fas fa-external-link-alt"></i></a>」の基本テーマ「{{$base_theme}}」を継承しています。<br />
                    </div>
                @endif
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label text-md-right">レイアウト</label>
        <div class="col-md-9">

            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '')
                    <input type="radio" value="" id="layout_null" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="layout_null" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_null" id="label_layout_null"><img src="{{asset('/images/core/layout/null.png')}}" title="未設定" alt="未設定"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0000')
                    <input type="radio" value="0|0|0|0" id="layout_0000" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|0|0|0" id="layout_0000" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0000" id="label_layout_0000"><img src="{{asset('/images/core/layout/0000.png')}}" title="メインのみ" alt="メインのみ"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0001')
                    <input type="radio" value="0|0|0|1" id="layout_0001" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|0|0|1" id="layout_0001" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0001" id="label_layout_0001"><img src="{{asset('/images/core/layout/0001.png')}}" title="フッター" alt="フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0010')
                    <input type="radio" value="0|0|1|0" id="layout_0010" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|0|1|0" id="layout_0010" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0010" id="label_layout_0010"><img src="{{asset('/images/core/layout/0010.png')}}" title="右" alt="右"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0011')
                    <input type="radio" value="0|0|1|1" id="layout_0011" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|0|1|1" id="layout_0011" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0011" id="label_layout_0011"><img src="{{asset('/images/core/layout/0011.png')}}" title="右、フッター" alt="右、フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0100')
                    <input type="radio" value="0|1|0|0" id="layout_0100" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|1|0|0" id="layout_0100" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0100" id="label_layout_0100"><img src="{{asset('/images/core/layout/0100.png')}}" title="左" alt="左"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0101')
                    <input type="radio" value="0|1|0|1" id="layout_0101" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|1|0|1" id="layout_0101" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0101" id="label_layout_0101"><img src="{{asset('/images/core/layout/0101.png')}}" title="左、フッター" alt="左、フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0110')
                    <input type="radio" value="0|1|1|0" id="layout_0110" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|1|1|0" id="layout_0110" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0110" id="label_layout_0110"><img src="{{asset('/images/core/layout/0110.png')}}" title="左、右" alt="左、右"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '0111')
                    <input type="radio" value="0|1|1|1" id="layout_0111" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0|1|1|1" id="layout_0111" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_0111" id="label_layout_0111"><img src="{{asset('/images/core/layout/0111.png')}}" title="左、右、フッター" alt="左、右、フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1000')
                    <input type="radio" value="1|0|0|0" id="layout_1000" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|0|0|0" id="layout_1000" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1000" id="label_layout_1000"><img src="{{asset('/images/core/layout/1000.png')}}" title="ヘッダー" alt="ヘッダー"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1001')
                    <input type="radio" value="1|0|0|1" id="layout_1001" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|0|0|1" id="layout_1001" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1001" id="label_layout_1001"><img src="{{asset('/images/core/layout/1001.png')}}" title="ヘッダー、フッター" alt="ヘッダー、フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1010')
                    <input type="radio" value="1|0|1|0" id="layout_1010" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|0|1|0" id="layout_1010" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1010" id="label_layout_1010"><img src="{{asset('/images/core/layout/1010.png')}}" title="ヘッダー、右" alt="ヘッダー、右"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1011')
                    <input type="radio" value="1|0|1|1" id="layout_1011" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|0|1|1" id="layout_1011" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1011" id="label_layout_1011"><img src="{{asset('/images/core/layout/1011.png')}}" title="ヘッダー、右、フッター" alt="ヘッダー、右、フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1100')
                    <input type="radio" value="1|1|0|0" id="layout_1100" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|1|0|0" id="layout_1100" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1100" id="label_layout_1100"><img src="{{asset('/images/core/layout/1100.png')}}" title="ヘッダー、左" alt="ヘッダー、左"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1101')
                    <input type="radio" value="1|1|0|1" id="layout_1101" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|1|0|1" id="layout_1101" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1101" id="label_layout_1101"><img src="{{asset('/images/core/layout/1101.png')}}" title="ヘッダー、左、フッター" alt="ヘッダー、左、フッター"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1110')
                    <input type="radio" value="1|1|1|0" id="layout_1110" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|1|1|0" id="layout_1110" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1110" id="label_layout_1110"><img src="{{asset('/images/core/layout/1110.png')}}" title="ヘッダー、左、右" alt="ヘッダー、左、右"></label>
            </div>
            <div class="custom-control custom-radio custom-control-inline mb-2">
                @if ($page->getSimpleLayout() == '1111')
                    <input type="radio" value="1|1|1|1" id="layout_1111" name="layout" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1|1|1|1" id="layout_1111" name="layout" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="layout_1111" id="label_layout_1111"><img src="{{asset('/images/core/layout/1111.png')}}" title="ヘッダー、左、右、フッター" alt="ヘッダー、左、右、フッター"></label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-3"></div>
        <div class="col mx-0">
            @php
            // 自分及び先祖ページを遡る
            $layout_page_parent = new Page();
            // 自分及び先祖ページを遡る
            foreach ($page_tree as $page_tmp) {
                if ($page_tmp->getSimpleLayout()) {
                    $layout_page_parent = $page_tmp;
                    break;
                }
            }
            @endphp
            {{-- 公開設定が公開以外＆親ページありなら --}}
            @if (!$page->getSimpleLayout() && $layout_page_parent->getSimpleLayout())
                <div class="alert alert-warning small mb-0">
                    未設定 <img src="{{asset('/images/core/layout/null.png')}}" title="未設定"> のため、親ページ「<a href="{{url('/manage/page/edit')}}/{{$layout_page_parent->id}}" target="_blank">{{$layout_page_parent->page_name}} <i class="fas fa-external-link-alt"></i></a>」のレイアウト <img src="{{asset('/images/core/layout/' . $layout_page_parent->getSimpleLayout() . '.png')}}" class="cc-page-layout-icon" title="{{$layout_page_parent->getLayoutTitle()}}"> を継承しています。<br />
                </div>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label text-md-right">メニュー表示</label>
        <div class="col-md-9 d-sm-flex align-items-center">

            <div class="custom-control custom-checkbox">
                @if(isset($page->base_display_flag) && $page->base_display_flag == 1)
                    <input name="base_display_flag" value="1" type="checkbox" class="custom-control-input" id="base_display_flag" checked="checked">
                @else
                    <input name="base_display_flag" value="1" type="checkbox" class="custom-control-input" id="base_display_flag">
                @endif
                <label class="custom-control-label" for="base_display_flag" id="label_base_display_flag">表示する</label>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-md-3 col-form-label text-md-right">ウィンドウ</label>
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
        <label class="col-md-3 col-form-label text-md-right pt-0">自動転送</label>
        <div class="col-md-9 d-sm-flex align-items-center">

            <div class="custom-control custom-checkbox">
                <input name="transfer_lower_page_flag" value="0" type="hidden">
                @if ($page->transfer_lower_page_flag == 1)
                    <input name="transfer_lower_page_flag" value="1" type="checkbox" class="custom-control-input" id="transfer_lower_page_flag" checked="checked">
                @else
                    <input name="transfer_lower_page_flag" value="1" type="checkbox" class="custom-control-input" id="transfer_lower_page_flag">
                @endif
                <label class="custom-control-label" for="transfer_lower_page_flag">下層ページへ自動転送する</label>
                <small class="form-text text-muted">※ 下層ページの中から、メニュー表示「表示する」がチェックされた一番上のページに自動転送します。</small>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label for="ip_address" class="col-md-3 col-form-label text-md-right">IPアドレス制限</label>
        <div class="col-md-9">
            <input type="text" name="ip_address" id="ip_address" value="{{old('ip_address', $page->ip_address)}}" class="form-control">
            @include('common.errors_inline', ['name' => 'ip_address'])

            @php
            // 自分及び先祖ページを遡る
            $ip_address_page_parent = new Page();
            // 自分及び先祖ページを遡る
            foreach ($page_tree as $page_tmp) {
                if ($page_tmp->ip_address) {
                    $ip_address_page_parent = $page_tmp;
                    break;
                }
            }
            @endphp
            {{-- 公開設定が公開以外＆親ページありなら --}}
            @if (!$page->ip_address && $ip_address_page_parent->ip_address)
                <div class="alert alert-warning small mb-0">
                    設定なしのため、親ページ「<a href="{{url('/manage/page/edit')}}/{{$ip_address_page_parent->id}}" target="_blank">{{$ip_address_page_parent->page_name}} <i class="fas fa-external-link-alt"></i></a>」のIPアドレス制限「{{$ip_address_page_parent->ip_address}}」を継承しています。<br />
                </div>
            @endif

            <small class="form-text text-muted">※ カンマで複数、CIDR形式での指定可能、*での指定は不可</small>
        </div>
    </div>
    <div class="form-group row">
        <label for="othersite_url" class="col-md-3 col-form-label text-md-right">外部サイトURL</label>
        <div class="col-md-9">
            <input type="text" name="othersite_url" id="othersite_url" value="{{old('othersite_url', $page->othersite_url)}}" class="form-control @if ($errors->has('othersite_url')) border-danger @endif">
            @include('common.errors_inline', ['name' => 'othersite_url'])
            <small class="form-text text-muted">※ メニューから直接開く外部サイトURL</small>
        </div>
    </div>
    <div class="form-group row">
        <label for="class" class="col-md-3 col-form-label text-md-right">クラス名</label>
        <div class="col-md-9">
            <input type="text" name="class" id="class" value="{{old('class', $page->class)}}" class="form-control">
            @include('common.errors_inline', ['name' => 'class'])
            <small class="form-text text-muted">※ デザインで使用するためのclass名</small>
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
<script>
    new Vue({
        el: "#app",
        data: {
            v_background_color: document.getElementById('background_color').value,
            v_header_color: document.getElementById('header_color').value
        },
    })
</script>
