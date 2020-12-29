{{--
 * 掲示板記事登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板・プラグイン
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
        @if (empty($bbses_posts->id))
            form_bbses_posts{{$frame_id}}.action = "{{url('/')}}/plugin/bbses/temporarysave/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}";
        @else
            form_bbses_posts{{$frame_id}}.action = "{{url('/')}}/plugin/bbses/temporarysave/{{$page->id}}/{{$frame_id}}/{{$bbses_posts->id}}#frame-{{$frame->id}}";
        @endif
        form_bbses_posts{{$frame_id}}.submit();
    }
</script>

{{-- 投稿用フォーム --}}
@if (empty($post->id))
    <form action="{{url('/')}}/redirect/plugin/bbses/save/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="" name="form_bbses_posts{{$frame_id}}">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/bbses/edit/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
@else
    <form action="{{url('/')}}/redirect/plugin/bbses/save/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" method="POST" class="" name="form_bbses_posts{{$frame_id}}">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/bbses/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">
@endif
    {{ csrf_field() }}
    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right">タイトル <label class="badge badge-danger">必須</label></label>
        <div class="col-md-10">
            <input type="text" name="title" value="{{old('title', $post->title)}}" class="form-control">
            @if ($errors && $errors->has('title')) <div class="text-danger">{{$errors->first('title')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right">本文 <label class="badge badge-danger">必須</label></label>
        <div class="col-md-10">
            <textarea name="body" class="form-control" rows=2>{!!old('body', $post->body)!!}</textarea>
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
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    <button type="button" class="btn btn-info mr-2" onclick="javascript:save_action();"><i class="far fa-save"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 一時保存</span></button>
                    <input type="hidden" name="bucket_id" value="">
                    @if (empty($post->id))
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 登録確定</button>
                    @else
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更確定</button>
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
@endsection
