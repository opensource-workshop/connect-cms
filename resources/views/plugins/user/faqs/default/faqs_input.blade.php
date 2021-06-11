{{--
 * FAQ記事登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('common.errors_form_line')

{{-- WYSIWYG 呼び出し --}}
@include('plugins.common.wysiwyg')

{{-- 一時保存ボタンのアクション --}}
<script type="text/javascript">
    function save_action() {
        @if (empty($faqs_posts->id))
            form_faqs_posts.action = "{{url('/')}}/plugin/faqs/temporarysave/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}";
        @else
            form_faqs_posts.action = "{{url('/')}}/plugin/faqs/temporarysave/{{$page->id}}/{{$frame_id}}/{{$faqs_posts->id}}#frame-{{$frame->id}}";
        @endif
        form_faqs_posts.submit();
    }
</script>

{{-- 投稿用フォーム --}}
@if (empty($faqs_posts->id))
    <form action="{{url('/')}}/plugin/faqs/save/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="" name="form_faqs_posts">
@else
    <form action="{{url('/')}}/plugin/faqs/save/{{$page->id}}/{{$frame_id}}/{{$faqs_posts->id}}#frame-{{$frame->id}}" method="POST" class="" name="form_faqs_posts">
@endif
    {{ csrf_field() }}
    <input type="hidden" name="faqs_id" value="{{$faq_frame->faqs_id}}">

    <div class="form-group">
        <label class="control-label">タイトル <label class="badge badge-danger">必須</label></label>
        <input type="text" name="post_title" value="{{old('post_title', $faqs_posts->post_title)}}" class="form-control">
        @if ($errors && $errors->has('post_title')) <div class="text-danger">{{$errors->first('post_title')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">本文 <label class="badge badge-danger">必須</label></label>
        <textarea name="post_text">{!!old('post_text', $faqs_posts->post_text)!!}</textarea>
        @if ($errors && $errors->has('post_text')) <div class="text-danger">{{$errors->first('post_text')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">投稿日時 <label class="badge badge-danger">必須</label></label>

        <div class="input-group date" id="posted_at" data-target-input="nearest">
            <input type="text" name="posted_at" value="{{old('posted_at', $faqs_posts->posted_at)}}" class="form-control datetimepicker-input  col-md-3" data-target="#posted_at">
            <div class="input-group-append" data-target="#posted_at" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="far fa-clock"></i></div>
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
            <input type="checkbox" name="important" value="1" class="custom-control-input" id="important" @if(old('important', $faqs_posts->important)) checked=checked @endif>
            <label class="custom-control-label" for="important">チェックすると、新着に表示し続けることができます。</label>
            <small class="form-text text-muted">※ プラグイン「新着情報」側の設定（重要記事の扱い）も必要です。</small>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label">表示順</label>
        <input type="text" name="display_sequence" value="{{old('display_sequence', $faqs_posts->display_sequence)}}" class="form-control">
        @if ($errors && $errors->has('display_sequence')) <div class="text-danger">{{$errors->first('display_sequence')}}</div> @endif
        <small class="text-muted">
            ※ FAQ設定の「順序条件」で「指定順」を指定した場合のみ、表示順にFAQが表示されます。<br />
            ※ 未指定時は最後に表示されるように自動登録します。
        </small>
    </div>

    <div class="form-group">
        <label class="control-label">カテゴリ</label>
        <select class="form-control" name="categories_id" class="form-control">
            <option value=""></option>
            @foreach($faqs_categories as $category)
            <option value="{{$category->id}}" @if(old('category', $faqs_posts->categories_id)==$category->id) selected="selected" @endif>{{$category->category}}</option>
            @endforeach
        </select>
        @if ($errors && $errors->has('category')) <div class="text-danger">{{$errors->first('category')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">タグ</label>
        <input type="text" name="tags" value="{{old('tags', $faqs_posts_tags)}}" class="form-control">
        @if ($errors && $errors->has('tags')) <div class="text-danger">{{$errors->first('tags')}}</div> @endif
        <small class="form-text text-muted">カンマ区切りで複数指定可能</small>
    </div>

    <div class="form-group">
        <div class="row">
            @if (empty($faqs_posts->id))
            <div class="col-12">
            @else
            <div class="col-3 d-none d-xl-block"></div>
            <div class="col-9 col-xl-6">
            @endif
                <div class="text-center">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    <button type="button" class="btn btn-info mr-2" onclick="javascript:save_action();"><i class="far fa-save"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 一時保存</span></button>
                    <input type="hidden" name="bucket_id" value="">
                    @if (empty($faqs_posts->id))
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
            @if (!empty($faqs_posts->id))
            <div class="col-3 col-xl-3 text-right">
                    <a data-toggle="collapse" href="#collapse{{$faqs_posts->id}}">
                        <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> 削除</span></span>
                    </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$faqs_posts->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/plugin/faqs/delete/{{$page->id}}/{{$frame_id}}/{{$faqs_posts->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
