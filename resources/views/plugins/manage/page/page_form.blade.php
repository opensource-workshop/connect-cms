{{--
 * Page 編集画面(入力フォーム)
 *
 * 新規登録画面と変更画面を共有して使用しています。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 --}}
<div class="panel panel-default">
    <div class="panel-heading">
        @if ($page->id)ページ更新 @else ページ追加 @endif
    </div>
    <div class="panel-body">

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
            <div class="form-group @if ($errors && $errors->has('page_name')) has-error @endif">
                <label for="page_name" class="col-md-3 control-label">ページ名</label>
                <div class="col-md-9">
                    <input type="text" name="page_name" id="page_name" value="{{$page->page_name}}" class="form-control">
                    @if ($errors && $errors->has('page_name')) <div class="text-danger">{{$errors->first('page_name')}}</div> @endif
                </div>
            </div>
            <div class="form-group">
                <label for="permanent_link" class="col-md-3 control-label">固定リンク</label>
                <div class="col-md-9">
                    <input type="text" name="permanent_link" id="permanent_link" value="{{$page->permanent_link}}" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label for="permanent_link" class="col-md-3 control-label">背景色</label>
                <div class="col-md-9">
                    <input type="text" name="background_color" id="background_color" value="{{$page->background_color}}" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label for="permanent_link" class="col-md-3 control-label">ヘッダーの背景色</label>
                <div class="col-md-9">
                    <input type="text" name="header_color" id="header_color" value="{{$page->header_color}}" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label for="permanent_link" class="col-md-3 control-label">レイアウト</label>
                <div class="col-md-9">
                    <select name="layout" class="form-control">
                        <option value=""@if($page->layout == "") selected @endif>設定なし</option>
                        <option value="1|1|0|1"@if($page->layout == "1|1|0|1") selected @endif>ヘッダー＆フッター＆レフト</option>
                        <option value="1|1|1|1"@if($page->layout == "1|1|1|1") selected @endif>ヘッダー＆フッター＆レフト＆ライト</option>
                        <option value="1|0|0|0"@if($page->layout == "1|1|1|1") selected @endif>ヘッダーのみ</option>
                        <option value="0|0|0|0"@if($page->layout == "0|0|0|0") selected @endif>メインのみ</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="permanent_link" class="col-md-3 control-label">メニュー表示</label>
                <div class="col-md-9">

                    <label class="cc_label_input_group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                @if(isset($page->base_display_flag) && $page->base_display_flag == 1)
                                    <input name="base_display_flag" value="1" type="checkbox" checked="checked">
                                @else
                                    <input name="base_display_flag" value="1" type="checkbox">
                                @endif
                            </span>
                            <span class="form-control" style="height: auto;">表示する</span>
                        </div>
                    </label>

                </div>
            </div>

            <!-- Add or Update Page Button -->
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" class="btn btn-primary form-horizontal">
                        @if ($page->id)ページ更新 @else ページ追加 @endif
                    </button>
                    @if ($page->id)
                        <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{url('/manage/page')}}'">Cancel</button>
                    @else
                        <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{url('/')}}'">Cancel</button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
