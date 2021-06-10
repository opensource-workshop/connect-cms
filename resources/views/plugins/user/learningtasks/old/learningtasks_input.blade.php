{{--
 * 課題管理記事登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
{{-- WYSIWYG 呼び出し --}}
@include('plugins.common.wysiwyg')

{{-- 一時保存ボタンのアクション --}}
<script type="text/javascript">
    function save_action() {
        @if (empty($learningtasks_posts->id))
            form_learningtasks_posts.action = "{{url('/')}}/plugin/learningtasks/temporarysave/{{$page->id}}/{{$frame_id}}";
        @else
            form_learningtasks_posts.action = "{{url('/')}}/plugin/learningtasks/temporarysave/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}";
        @endif
        form_learningtasks_posts.submit();
    }
</script>

{{-- 投稿用フォーム --}}
@if (empty($learningtasks_posts->id))
    <form action="{{url('/')}}/plugin/learningtasks/save/{{$page->id}}/{{$frame_id}}" method="POST" class="" name="form_learningtasks_posts" enctype="multipart/form-data">
@else
    <form action="{{url('/')}}/plugin/learningtasks/save/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}" method="POST" class="" name="form_learningtasks_posts" enctype="multipart/form-data">
@endif
    {{ csrf_field() }}
    <input type="hidden" name="learningtasks_id" value="{{$learningtasks_frame->learningtasks_id}}">

    <div class="form-group">
        <label class="control-label">タイトル <label class="badge badge-danger">必須</label></label>
        <textarea name="post_title">{!!old('post_title', $learningtasks_posts->post_title)!!}</textarea>
        @if ($errors && $errors->has('post_title')) <div class="text-danger">{{$errors->first('post_title')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">本文 <label class="badge badge-danger">必須</label></label>
        <textarea name="post_text">{!!old('post_text', $learningtasks_posts->post_text)!!}</textarea>
        @if ($errors && $errors->has('post_text')) <div class="text-danger">{{$errors->first('post_text')}}</div> @endif
    </div>

    <div class="form-group">
        <label for="add_task_file">課題ファイル</label><small class="text-muted">（PDF もしくは ワード形式）</small>
        <input type="file" name="add_task_file" class="form-control-file" id="add_task_file">
    </div>

    <div class="form-group">
        <label class="control-label">ファイル一覧（削除する場合はチェック）</label>
        <div class="card p-2">
        @isset($learningtasks_posts_files)
        @foreach($learningtasks_posts_files as $posts_file)
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="del_task_file[{{$posts_file->id}}]" value="1" class="custom-control-input" id="del_task_file[{{$posts_file->id}}]" @if(old("del_task_file.$posts_file->id")) checked=checked @endif>
                <label class="custom-control-label" for="del_task_file[{{$posts_file->id}}]"><a href="{{url('/')}}/file/{{$posts_file->task_file_uploads_id}}" target="_blank" rel="noopener">{{$posts_file->client_original_name}}</a></label>
            </div>
        @endforeach
        @endisset
        </div>
    </div>

    <div class="form-group">
        <label class="control-label">投稿日時 <label class="badge badge-danger">必須</label></label>

        <div class="input-group date" id="posted_at" data-target-input="nearest">
            <input type="text" name="posted_at" value="{{old('posted_at', $learningtasks_posts->posted_at)}}" class="form-control datetimepicker-input  col-md-3" data-target="#posted_at">
            <div class="input-group-append" data-target="#posted_at" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
            </div>
        </div>
        @if ($errors && $errors->has('posted_at')) <div class="text-danger">{{$errors->first('posted_at')}}</div> @endif
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
            <input type="checkbox" name="important" value="1" class="custom-control-input" id="important" @if(old('important', $learningtasks_posts->important)) checked=checked @endif>
            <label class="custom-control-label" for="important">チェックすると、新着に表示し続けることができます。</label>
            <small class="form-text text-muted">※ プラグイン「新着情報」側の設定（重要記事の扱い）も必要です。</small>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label">表示順</label>
        <input type="text" name="display_sequence" value="{{old('display_sequence', $learningtasks_posts->display_sequence)}}" class="form-control">
        @if ($errors && $errors->has('display_sequence')) <div class="text-danger">{{$errors->first('display_sequence')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">カテゴリ</label>
        <select class="form-control" name="categories_id" class="form-control">
            <option value=""></option>
            @foreach($learningtasks_categories as $category)
            <option value="{{$category->id}}" @if(old('category', $learningtasks_posts->categories_id)==$category->id) selected="selected" @endif>{{$category->category}}</option>
            @endforeach
        </select>
        @if ($errors && $errors->has('category')) <div class="text-danger">{{$errors->first('category')}}</div> @endif
    </div>

    {{-- delete: oldテンプレートで使ってないが、learningtasks_posts_tagsテーブル削除に伴いコメントアウト
    <div class="form-group">
        <label class="control-label">タグ</label>
        <input type="text" name="tags" value="{{old('tags', $learningtasks_posts_tags)}}" class="form-control">
        @if ($errors && $errors->has('tags')) <div class="text-danger">{{$errors->first('tags')}}</div> @endif
        <small class="form-text text-muted">カンマ区切りで複数指定可能</small>
    </div>
    --}}

    <div class="form-group">
        <div class="row">
            @if (empty($learningtasks_posts->id))
            <div class="col-12">
            @else
            <div class="col-3 d-none d-xl-block"></div>
            <div class="col-9 col-xl-6">
            @endif
                <div class="text-center">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    <button type="button" class="btn btn-info mr-2" onclick="javascript:save_action();"><i class="far fa-save"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 一時保存</span></button>
                    <input type="hidden" name="bucket_id" value="">
                    @if (empty($learningtasks_posts->id))
                        @if ($buckets->needApprovalUser(Auth::user()))
                            <button type="submit" class="btn btn-success"><i class="far fa-edit"></i> 登録申請</button>
                        @else
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 登録確定</button>
                        @endif
                    @else
                        @if ($buckets->needApprovalUser(Auth::user()))
                            <button type="submit" class="btn btn-success"><i class="far fa-edit"></i> 変更申請</button>
                        @else
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更確定</button>
                        @endif
                    @endif
                </div>
            </div>
            @if (!empty($learningtasks_posts->id))
            <div class="col-3 col-xl-3 text-right">
                    <a data-toggle="collapse" href="#collapse{{$learningtasks_posts->id}}">
                        <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> 削除</span></span>
                    </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$learningtasks_posts->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/plugin/learningtasks/delete/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
