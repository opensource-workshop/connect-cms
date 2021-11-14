{{--
 * フォトアルバム・アルバム編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('plugins.common.errors_form_line')

{{-- 投稿用フォーム --}}
<form action="{{url('/')}}/redirect/plugin/photoalbums/editFolder/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame->id}}" method="POST" class="" name="form_post{{$frame_id}}">
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/photoalbums/edit/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame_id}}">
    {{ csrf_field() }}

    <div class="form-group row">
        <label class="col-md-3 control-label text-md-right" for="title">アルバム名 <label class="badge badge-danger">必須</label></label>
        <div class="col-md-9">
            <input type="text" name="name[{{$frame_id}}]" value="{{old("name.$frame_id", $photoalbum_content->name)}}" class="form-control @if ($errors->has("name.$frame_id")) border-danger @endif" id="name{{$frame_id}}">
            @if ($errors && $errors->has("name.$frame_id")) 
                <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("name.*")}}</div>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 control-label text-md-right">説明</label>
        <div class="col-md-9">
            <textarea name="description[{{$frame_id}}]" class="form-control @if ($errors->has("description.$frame_id")) border-danger @endif" id="description{{$frame_id}}" rows=2>{!!old("description.$frame_id", $photoalbum_content->description)!!}</textarea>
            @if ($errors && $errors->has("description.$frame_id")) 
                <div class="text-danger"><i class="fas fa-exclamation-triangle"></i> {{$errors->first("description.*")}}</div>
            @endif
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            @if (empty($photoalbum_content->id))
            <div class="col-12">
            @else
            <div class="col-3 d-none d-xl-block"></div>
            <div class="col-9 col-xl-6">
            @endif
                <div class="text-center">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->parent_id}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    <input type="hidden" name="bucket_id[{{$frame_id}}]" value="">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更</button>
                </div>
            </div>
{{--
            @if (!empty($photoalbum_content->id))
            <div class="col-3 col-xl-3 text-right">
                <a data-toggle="collapse" href="#collapse{{$frame_id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> 削除</span></span>
                </a>
            </div>
            @endif
--}}
        </div>
    </div>
</form>

<div id="collapse{{$frame_id}}" class="collapse">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/photoalbums/delete/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <input type="hidden" name="redirect_path" value="{{$page->permanent_link}}#frame-{{$frame_id}}">
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
