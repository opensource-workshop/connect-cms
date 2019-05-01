{{--
 * ブログ編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
 --}}

<div class="panel-body">
    <ul class="nav nav-tabs">
        {{-- プラグイン側のフレームメニュー --}}
        @include('plugins.user.blogs.frame_edit_tab')

        {{-- コア側のフレームメニュー --}}
        @include('core.cms_frame_edit_tab')
    </ul>
</div>

<div class="container-fluid">

<div class="alert alert-info" role="info">
    <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
    詳細表示
</div>

    <div class="form-group">
        <label class="control-label">ファイル</label>
        <div class="input-group">
            <span class="input-group-btn"><input value="参照" onclick="$('input[id=column_file]').click();" class="btn btn-primary" type="button"></span> <span id="column_file_path" class="form-control"></span></div> <div></div></div>


    <div class="form-group">
        <label class="control-label">ブログ名</label>
        <input type="text" name="blog_name" value="{{old('blog_name', $blog->blog_name)}}" class="form-control">
        <div class="text-danger"></div>
    </div>

    <div class="form-group">
        <label class="control-label">表示件数</label>
        <input type="text" name="view_count" value="{{old('view_count', $blog->view_count)}}" class="form-control">
        <div class="text-danger"></div>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <button type="submit" class="btn btn-primary form-horizontal"><span class="glyphicon glyphicon-ok"></span> 
                登録
        </button>
        <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'">キャンセル</button>
    </div>

</div>
