{{--
 * Page 編集画面(入力フォーム)
 *
 * 新規登録画面と変更画面を共有して使用しています。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 --}}
<div class="card">
    <div class="card-header">
        @if ($page->id)ページ更新 @else ページ追加 @endif
    </div>
    <div class="card-body">

        <!-- Display Validation Errors -->
{{--
        @include('common.errors')
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
            <div class="form-group row">
                <label for="permanent_link" class="col-md-3 col-form-label text-md-right">テーマ</label>
                <div class="col-md-9">
                    <input type="text" name="theme" id="theme" value="{{$page->theme}}" class="form-control">
                </div>
            </div>
            <div class="form-group row">
                <label for="permanent_link" class="col-md-3 col-form-label text-md-right">レイアウト</label>
                <div class="col-md-9">
                    <select name="layout" class="form-control">
                        <option value=""@if($page->layout == "") selected @endif>設定なし</option>
                        <option value="1|1|0|1"@if($page->layout == "1|1|0|1") selected @endif>ヘッダー＆フッター＆レフト</option>
                        <option value="1|1|1|1"@if($page->layout == "1|1|1|1") selected @endif>ヘッダー＆フッター＆レフト＆ライト</option>
                        <option value="1|0|0|1"@if($page->layout == "1|0|0|1") selected @endif>ヘッダー＆フッター</option>
                        <option value="1|0|0|0"@if($page->layout == "1|0|0|0") selected @endif>ヘッダーのみ</option>
                        <option value="0|0|0|0"@if($page->layout == "0|0|0|0") selected @endif>メインのみ</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="permanent_link" class="col-md-3 col-form-label text-md-right">メニュー表示</label>
                <div class="col-md-9">

                    <div class="custom-control custom-checkbox mt-2">
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
                <label for="permanent_link" class="col-md-3 col-form-label text-md-right">外部サイトTarget</label>
                <div class="col-md-9">

                    <div class="custom-control custom-checkbox mt-2">
                        @if(isset($page->othersite_url_target) && $page->othersite_url_target == 1)
                            <input name="othersite_url_target" value="1" type="checkbox" class="custom-control-input" id="othersite_url_target" checked="checked">
                        @else
                            <input name="othersite_url_target" value="1" type="checkbox" class="custom-control-input" id="othersite_url_target">
                        @endif
                        <label class="custom-control-label" for="othersite_url_target">新しいウィンドウまたはタブで開く</label>
                    </div>
                </div>
            </div>

            <!-- Add or Update Page Button -->
            <div class="form-group">
                <div class="offset-sm-3 col-sm-6">
                    @if ($page->id)
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/page')}}'"><i class="fas fa-times"></i> キャンセル</button>
                    @else
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}'"><i class="fas fa-times"></i> キャンセル</button>
                    @endif
                    <button type="submit" class="btn btn-primary form-horizontal">
                        <i class="fas fa-check"></i> @if ($page->id)ページ更新 @else ページ追加 @endif
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
