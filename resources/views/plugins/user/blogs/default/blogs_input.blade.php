{{--
 * ブログ記事登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
--}}
@php
use App\Models\User\Blogs\BlogsPosts;
@endphp

@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

{{-- WYSIWYG 呼び出し --}}
@include('plugins.common.wysiwyg', ['target_class' => 'wysiwyg' . $frame->id])

{{-- 一時保存ボタンのアクション --}}
<script type="text/javascript">
    function save_action() {
        @if (empty($blogs_posts->id))
            form_blogs_posts{{$frame_id}}.action = "{{url('/')}}/plugin/blogs/temporarysave/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}";
        @else
            form_blogs_posts{{$frame_id}}.action = "{{url('/')}}/plugin/blogs/temporarysave/{{$page->id}}/{{$frame_id}}/{{$blogs_posts->id}}#frame-{{$frame->id}}";
        @endif
        form_blogs_posts{{$frame_id}}.submit();
    }
</script>

{{-- 投稿用フォーム --}}
@if (empty($blogs_posts->id))
    <form action="{{url('/')}}/redirect/plugin/blogs/save/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" name="form_blogs_posts{{$frame_id}}">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/blogs/create/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
@else
    <form action="{{url('/')}}/redirect/plugin/blogs/save/{{$page->id}}/{{$frame_id}}/{{$blogs_posts->id}}#frame-{{$frame->id}}" method="POST" name="form_blogs_posts{{$frame_id}}">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/blogs/edit/{{$page->id}}/{{$frame_id}}/{{$blogs_posts->id}}#frame-{{$frame_id}}">
@endif
    {{ csrf_field() }}
    <input type="hidden" name="blogs_id" value="{{$blog_frame->blogs_id}}">

    <div class="form-group">
        <label class="control-label">タイトル <span class="badge badge-danger">必須</span></label>
        <input type="text" name="post_title" value="{{old('post_title', $blogs_posts->post_title)}}" class="form-control @if ($errors && $errors->has('post_title')) border-danger @endif">
        @include('plugins.common.errors_inline', ['name' => 'post_title'])
    </div>

    <div class="form-group">
        <label class="control-label">投稿日時 <span class="badge badge-danger">必須</span></label>

        <div class="input-group date" id="posted_at" data-target-input="nearest">
            <input type="text" name="posted_at" value="{{old('posted_at', $blogs_posts->posted_at)}}" class="form-control datetimepicker-input col-md-3 @if ($errors && $errors->has('posted_at')) border-danger @endif" data-target="#posted_at">
            <div class="input-group-append" data-target="#posted_at" data-toggle="datetimepicker">
                <div class="input-group-text @if ($errors && $errors->has('posted_at')) border-danger @endif"><i class="far fa-clock"></i></div>
            </div>
        </div>
        @include('plugins.common.errors_inline', ['name' => 'posted_at'])
    </div>
    <script type="text/javascript">
        $(function () {
            $('#posted_at').datetimepicker({
                locale: 'ja',
                sideBySide: true,
                dayViewHeaderFormat: 'YYYY年 M月',
                format: 'YYYY-MM-DD HH:mm'
            });
        });
    </script>

    <div class="form-group">
        <label class="control-label">重要記事</label>
        <div class="custom-control custom-checkbox">
            <input type="checkbox" name="important" value="1" class="custom-control-input" id="important{{$frame_id}}" @if(old('important', $blogs_posts->important)) checked=checked @endif>
            <label class="custom-control-label" for="important{{$frame_id}}">チェックすると、新着に表示し続けることができます。</label>
            <small class="form-text text-muted">※ プラグイン「新着情報」側の設定（重要記事の扱い）も必要です。</small>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label">本文 <span class="badge badge-danger">必須</span></label>
        <div @if ($errors && $errors->has('post_text')) class="border border-danger" @endif>
            <textarea name="post_text" class="wysiwyg{{$frame->id}}">{!!old('post_text', $blogs_posts->post_text)!!}</textarea>
        </div>
        @include('plugins.common.errors_inline_wysiwyg', ['name' => 'post_text'])
    </div>

    <div class="form-row">
        <div class="form-group col-md">
            <label class="control-label">続き</label>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="read_more_flag" value="1" class="custom-control-input" id="read_more_flag{{$frame_id}}" @if(old('read_more_flag', $blogs_posts->read_more_flag)) checked=checked @endif>
                <label class="custom-control-label" for="read_more_flag{{$frame_id}}">続きを表示する</label>
            </div>
        </div>

        <div class="form-group col-md">
            <label class="control-label">続きを読むボタン名</label>
            <input type="text" name="read_more_button" value="{{old('read_more_button', $blogs_posts->read_more_button)}}" class="form-control @if ($errors && $errors->has('read_more_button')) border-danger @endif">
            @include('plugins.common.errors_inline', ['name' => 'read_more_button'])
            <small class="form-text text-muted">空の場合「{{BlogsPosts::read_more_button_default}}」を表示します。</small>
        </div>

        <div class="form-group col-md">
            <label class="control-label">続きを閉じるボタン名</label>
            <input type="text" name="close_more_button" value="{{old('close_more_button', $blogs_posts->close_more_button)}}" class="form-control @if ($errors && $errors->has('close_more_button')) border-danger @endif">
            @include('plugins.common.errors_inline', ['name' => 'close_more_button'])
            <small class="form-text text-muted">空の場合「{{BlogsPosts::close_more_button_default}}」を表示します。</small>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label">続き本文</label>
        <div @if ($errors && $errors->has('post_text2')) class="border border-danger" @endif>
            <textarea name="post_text2" class="wysiwyg{{$frame->id}}">{!!old('post_text2', $blogs_posts->post_text2)!!}</textarea>
        </div>
        @include('plugins.common.errors_inline_wysiwyg', ['name' => 'post_text2'])
    </div>

    <div class="form-group">
        <label class="control-label">カテゴリ</label>
        <select name="categories_id" class="form-control @if ($errors && $errors->has('category')) border-danger @endif">
            <option value=""></option>
            @foreach($blogs_categories as $category)
            <option value="{{$category->id}}" @if(old('categories_id', $blogs_posts->categories_id)==$category->id) selected="selected" @endif>{{$category->category}}</option>
            @endforeach
        </select>
        @include('plugins.common.errors_inline', ['name' => 'category'])
    </div>

    <div class="form-group">
        <label class="control-label">タグ</label>
        <input type="text" name="tags" value="{{old('tags', $blogs_posts_tags)}}" class="form-control @if ($errors && $errors->has('tags')) border-danger @endif">
        @include('plugins.common.errors_inline', ['name' => 'tags'])
        <small class="form-text text-muted">カンマ区切りで複数指定可能</small>
    </div>

    <div class="form-group">
        <div class="row">
            @if (empty($blogs_posts->id))
            <div class="col-12">
            @else
            <div class="col-3 d-none d-xl-block"></div>
            <div class="col-9 col-xl-6">
            @endif
                <div class="text-center">
                    <a href="{{URL::to($page->permanent_link)}}" class="btn btn-secondary mr-2"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></a>
                    <button type="button" class="btn btn-info mr-2" onclick="javascript:save_action();"><i class="far fa-save"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 一時保存</span></button>
                    <input type="hidden" name="bucket_id" value="">
                    @if (empty($blogs_posts->id))
                        @if ($buckets->needApprovalUser(Auth::user(), $frame))
                            <button type="submit" class="btn btn-success"><i class="far fa-edit"></i> 登録申請</button>
                        @else
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 登録確定</button>
                        @endif
                    @else
                        @if ($buckets->needApprovalUser(Auth::user(), $frame))
                            <button type="submit" class="btn btn-success"><i class="far fa-edit"></i> 変更申請</button>
                        @else
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更確定</button>
                        @endif
                    @endif
                </div>
            </div>
            @if (!empty($blogs_posts->id))
                <div class="col-3 col-xl-3 text-right">
                    <a data-toggle="collapse" href="#collapse{{$blogs_posts->id}}">
                        <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> 削除</span></span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$blogs_posts->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/plugin/blogs/delete/{{$page->id}}/{{$frame_id}}/{{$blogs_posts->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
