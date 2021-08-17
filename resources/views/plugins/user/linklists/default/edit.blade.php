{{--
 * リンクリスト記事登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category リンクリスト・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('common.errors_form_line')

{{-- 投稿用フォーム --}}
@if (empty($post->id))
    <form action="{{url('/')}}/redirect/plugin/linklists/save/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="" name="form_post{{$frame_id}}">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/linklists/edit/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
@else
    <form action="{{url('/')}}/redirect/plugin/linklists/save/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" method="POST" class="" name="form_post{{$frame_id}}">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/linklists/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">
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
        <label class="col-md-2 control-label text-md-right">URL</label>
        <div class="col-md-10">
            <input type="text" name="url" value="{{old('url', $post->url)}}" class="form-control">
            @if ($errors && $errors->has('url')) <div class="text-danger">{{$errors->first('url')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right">ターゲット</label>
        <div class="col-md-10">
            <div class="custom-control custom-checkbox">
                @if(old('target_blank_flag', $post->target_blank_flag) || empty($post->id))
                    <input type="checkbox" name="target_blank_flag" value="1" class="custom-control-input" id="target_blank_flag" checked=checked>
                @else
                    <input type="checkbox" name="target_blank_flag" value="1" class="custom-control-input" id="target_blank_flag">
                @endif
                <label class="custom-control-label" for="target_blank_flag">チェックすると、新規ウィンドウで開きます。</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right">説明</label>
        <div class="col-md-10">
            <textarea name="description" class="form-control" rows=2>{!!old('description', $post->description)!!}</textarea>
            @if ($errors && $errors->has('description')) <div class="text-danger">{{$errors->first('description')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right">表示順</label>
        <div class="col-md-10">
            <input type="text" name="display_sequence" value="{{old('display_sequence', $post->display_sequence)}}" class="form-control">
            <small class="text-muted">※ 未指定時は最後に表示されるように自動登録します。</small>
            @if ($errors && $errors->has('display_sequence')) <div class="text-danger">{{$errors->first('display_sequence')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right">カテゴリ</label>
        <div class="col-md-10">
            <select class="form-control" name="categories_id" class="form-control">
                <option value=""></option>
                @foreach($categories as $category)
                <option value="{{$category->id}}" @if(old('category', $post->categories_id)==$category->id) selected="selected" @endif>{{$category->category}}</option>
                @endforeach
            </select>
            @if ($errors && $errors->has('category')) <div class="text-danger">{{$errors->first('category')}}</div> @endif
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
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
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
                <form action="{{url('/')}}/redirect/plugin/linklists/delete/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <input type="hidden" name="redirect_path" value="{{$page->permanent_link}}#frame-{{$frame_id}}">
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
