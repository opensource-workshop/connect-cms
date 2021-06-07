{{--
 * 課題管理記事登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
--}}
@extends('core.cms_frame_base')

{{-- 編集画面側のフレームメニュー --}}
@include('plugins.user.learningtasks.learningtasks_setting_edit_tab')

@section("plugin_contents_$frame->id")

@include('common.errors_form_line')

{{-- WYSIWYG 呼び出し --}}
@include('plugins.common.wysiwyg')

{{-- 投稿用フォーム --}}
@if (empty($learningtasks_posts->id))
    <form action="{{url('/')}}/redirect/plugin/learningtasks/save/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" class="" name="form_learningtasks_posts" enctype="multipart/form-data">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/create/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
@else
    <form action="{{url('/')}}/redirect/plugin/learningtasks/save/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame->id}}" method="POST" class="" name="form_learningtasks_posts" enctype="multipart/form-data">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/edit/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}">
@endif
    {{ csrf_field() }}
    <input type="hidden" name="learningtask_id" value="{{$learningtask->id}}">

    <div class="form-group row">
        <label class="col-md-2">タイトル <label class="badge badge-danger">必須</label></label>
        <div class="col-md-10">
            <input type="text" name="post_title" value="{{old('post_title', $learningtasks_posts->post_title)}}" class="form-control">
            {{-- <textarea name="post_title">{!!old('post_title', $learningtasks_posts->post_title)!!}</textarea> --}}
            @if ($errors && $errors->has('post_title')) <div class="text-danger">{{$errors->first('post_title')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2">本文 <label class="badge badge-danger">必須</label></label>
        <div class="col-md-10">
            <textarea name="post_text">{!!old('post_text', $learningtasks_posts->post_text)!!}</textarea>
            @if ($errors && $errors->has('post_text')) <div class="text-danger">{{$errors->first('post_text')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2" for="add_task_file">課題ファイル</label>
        <div class="col-md-10">
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="add_task_file" name="add_task_file" accept=".pdf, .doc, .docx">
                <label class="custom-file-label" for="add_task_file" data-browse="参照">PDF もしくは ワード形式。</label>
                @if ($errors && $errors->has('add_task_file')) <div class="text-danger">{{$errors->first('add_task_file')}}</div> @endif
                <small class="text-muted">※ アップロードできる１ファイルの最大サイズ: {{ini_get('upload_max_filesize')}}</small><br />
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2">ファイル一覧</label>
        <div class="col-md-10">
            <div class="card p-2">
            @isset($learningtasks_posts_files)
            @foreach($learningtasks_posts_files as $posts_file)
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="del_task_file[{{$posts_file->id}}]" value="1" class="custom-control-input" id="del_task_file[{{$posts_file->id}}]" @if(old("del_task_file.$posts_file->id")) checked=checked @endif>
                    <label class="custom-control-label" for="del_task_file[{{$posts_file->id}}]"><a href="{{url('/')}}/file/{{$posts_file->upload_id}}" target="_blank" rel="noopener">{{$posts_file->client_original_name}}</a></label>
                </div>
            @endforeach
            @endisset
            </div>
            <small class="text-muted">削除する場合はチェックします。</small>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2">投稿日時 <span class="badge badge-danger">必須</span></label>
        <div class="col-md-10">
            <div class="input-group date" id="posted_at" data-target-input="nearest">
                <input type="text" name="posted_at" value="{{old('posted_at', $learningtasks_posts->posted_at)}}" class="form-control datetimepicker-input col-md-4" data-target="#posted_at">
                <div class="input-group-append" data-target="#posted_at" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                </div>
            </div>
            @if ($errors && $errors->has('posted_at')) <div class="text-danger">{{$errors->first('posted_at')}}</div> @endif
        </div>
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

    {{-- delete: 「重要記事」は新着情報にのせる時のオプション項目。課題管理は新着情報に対応していないため、不要な項目のためコメントアウト
    <div class="form-group row">
        <label class="col-md-2">重要記事</label>
        <div class="col-md-10">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="important" value="1" class="custom-control-input" id="important" @if(old('important', $learningtasks_posts->important)) checked=checked @endif>
                <label class="custom-control-label" for="important">チェックすると、新着に表示し続けることができます。</label>
                <small class="form-text text-muted">※ プラグイン「新着情報」側の設定（重要記事の扱い）も必要です。</small>
            </div>
        </div>
    </div>
    --}}

    <div class="form-group row">
        <label class="col-md-2">表示SEQ</label>
        <div class="col-md-10">
            <input type="text" name="display_sequence" value="{{old('display_sequence', $learningtasks_posts->display_sequence)}}" class="form-control">
            @if ($errors && $errors->has('display_sequence')) <div class="text-danger">{{$errors->first('display_sequence')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2">カテゴリ</label>
        <div class="col-md-10">
            <select class="form-control" name="categories_id" class="form-control">
                <option value=""></option>
                @foreach($learningtasks_categories as $category)
                <option value="{{$category->id}}" @if(old('category', $learningtasks_posts->categories_id)==$category->id) selected="selected" @endif>{{$category->category}}</option>
                @endforeach
            </select>
            @if ($errors && $errors->has('category')) <div class="text-danger">{{$errors->first('category')}}</div> @endif
        </div>
    </div>

    {{--
    <div class="form-group row">
        <label class="col-md-2">タグ</label>
        <div class="col-md-10">
            <input type="text" name="tags" value="{{old('tags', $learningtasks_posts_tags)}}" class="form-control">
            @if ($errors && $errors->has('tags')) <div class="text-danger">{{$errors->first('tags')}}</div> @endif
            <small class="form-text text-muted">カンマ区切りで複数指定可能</small>
        </div>
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
                    @if (empty($learningtasks_posts->id))
                        {{-- bugfix: 登録の時は、一覧へボタン表示 --}}
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}{{$page->getLinkUrl()}}#frame-{{$frame->id}}'">
                            <i class="fas fa-angle-left"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> 一覧へ</span>
                        </button>
                    @else
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}'">
                            <i class="fas fa-angle-left"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> 詳細へ</span>
                        </button>
                    @endif
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.reload()">
                        {{-- <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span> --}}
                        <i class="fas fa-undo-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span>
                    </button>
                    <input type="hidden" name="bucket_id" value="">
                    @if (empty($learningtasks_posts->id))
                        @if ($buckets->needApprovalUser(Auth::user()))
                            <button type="submit" class="btn btn-success" onclick="javascript:return confirm('登録します。\nよろしいですか？')"><i class="far fa-edit"></i> 登録申請</button>
                        @else
                            <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('登録します。\nよろしいですか？')"><i class="fas fa-check"></i> 登録確定</button>
                        @endif
                    @else
                        @if ($buckets->needApprovalUser(Auth::user()))
                            <button type="submit" class="btn btn-success" onclick="javascript:return confirm('変更します。\nよろしいですか？')"><i class="far fa-edit"></i> 変更申請</button>
                        @else
                            <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('変更します。\nよろしいですか？')"><i class="fas fa-check"></i> 変更確定</button>
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
                <form action="{{url('/')}}/plugin/learningtasks/delete/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$('.custom-file-input').on('change',function(){
    $(this).next('.custom-file-label').html($(this)[0].files[0].name);
})
</script>
@endsection
