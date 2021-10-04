{{--
 * 掲示板記事登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('common.errors_form_line')

{{-- WYSIWYG 呼び出し --}}
@include('plugins.common.wysiwyg', ['target_class' => 'wysiwyg' . $frame->id])

{{-- 一時保存ボタンのアクション --}}
<script type="text/javascript">
    function save_action() {
        form_bbses_posts{{$frame_id}}.status.value = "{{StatusType::temporary}}";
        form_bbses_posts{{$frame_id}}.submit();
    }
</script>

@if ($errors && $errors->has('reply_role_error'))
    <div class="alert alert-danger">
        <span class="font-weight-bold">{{$errors->first('reply_role_error')}}</span>
    </div>
@endif

{{-- 投稿用フォーム --}}
@if (empty($post->id))
    <form action="{{url('/')}}/redirect/plugin/bbses/save/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="" name="form_bbses_posts{{$frame_id}}">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/bbses/edit/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
@else
    <form action="{{url('/')}}/redirect/plugin/bbses/save/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" method="POST" class="" name="form_bbses_posts{{$frame_id}}">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/bbses/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">
@endif
    {{ csrf_field() }}
    <input type="hidden" name="status" value="{{StatusType::active}}">
    @if (isset($parent_post))
        <input type="hidden" name="parent_id" value="{{$parent_post->id}}">
    @endif
    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right">状態</label>
        <div class="col-md-10">
            @if ($post->status === null)
                <span class="badge badge-info align-bottom">新規</span>
            @elseif ($post->status == 0)
                <span class="badge badge-info align-bottom">公開中</span>
            @elseif ($post->status == 1)
                <span class="badge badge-warning align-bottom">一時保存</span>
            @elseif ($post->status == 2)
                <span class="badge badge-warning align-bottom">承認待ち</span>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right">タイトル <label class="badge badge-danger">必須</label></label>
        <div class="col-md-10">
            @if (isset($reply_flag) && $reply_flag == true)
            <input type="text" name="title" value="{{old('title', $parent_post->getReplyTitle())}}" class="form-control">
            @else
            <input type="text" name="title" value="{{old('title', $post->title)}}" class="form-control">
            @endif
            @if ($errors && $errors->has('title')) <div class="text-danger">{{$errors->first('title')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right">本文 <label class="badge badge-danger">必須</label></label>
        <div class="col-md-10">
            @if (isset($reply) && $reply == true)
                <textarea name="body" class="form-control wysiwyg{{$frame->id}}" rows=2>{!!old('body', $parent_post->getReplyBody())!!}</textarea>
            @else
                <textarea name="body" class="form-control wysiwyg{{$frame->id}}" rows=2>{!!old('body', $post->body)!!}</textarea>
            @endif
            @if ($errors && $errors->has('body')) <div class="text-danger">{{$errors->first('body')}}</div> @endif
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            @if (empty($post->id))
            <div class="col-12">
            @else
            <div class="col-3 d-none d-xl-block"></div>
            <div class="col-9 col-xl-6">
            @endif
                <div class="text-center">
                    @if (isset($parent_post))
                        {{-- 返信 --}}
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/bbses/show/{{$page->id}}/{{$frame_id}}/{{$parent_post->id}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    @elseif (empty($post->id))
                        {{-- 新規 --}}
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    @else
                        {{-- 編集 --}}
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/bbses/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    @endif
                    <button type="button" class="btn btn-info mr-2" onclick="javascript:save_action();"><i class="far fa-save"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 一時保存</span></button>
                    <input type="hidden" name="bucket_id" value="">
                    @if (empty($post->id))
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
            @if (!empty($post->id))
            <div class="col-3 col-xl-3 text-right">
                <a data-toggle="collapse" href="#collapse{{$frame_id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> 削除</span></span>
                </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$frame_id}}" class="collapse">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/bbses/delete/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <input type="hidden" name="redirect_path" value="{{$page->permanent_link}}#frame-{{$frame_id}}">
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if (isset($reply_flag) && $reply_flag == true)
    {{-- スレッドの投稿一覧 --}}
    @include('plugins.user.bbses.default.thread_show')
@endif

@endsection
