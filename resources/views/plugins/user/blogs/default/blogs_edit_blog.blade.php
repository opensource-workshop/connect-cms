{{--
 * ブログ編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.blogs.blogs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if (!$blog->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        設定画面から、使用するブログを選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>

        @if ($message)
            {{$message}}
        @else
            @if (empty($blog) || $create_flag)
                新しいブログ設定を登録します。
            @else
                ブログ設定を変更します。
            @endif
        @endif
    </div>
@endif

@if (!$blog->id && !$create_flag)
@else
<form action="/plugin/blogs/saveBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- create_flag がtrue の場合、新規作成するためにblogs_id を空にする --}}
    @if ($create_flag)
        <input type="hidden" name="blogs_id" value="">
    @else
        <input type="hidden" name="blogs_id" value="{{$blog->id}}">
    @endif

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">ブログ名 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="blog_name" value="{{old('blog_name', $blog->blog_name)}}" class="form-control">
            @if ($errors && $errors->has('blog_name')) <div class="text-danger">{{$errors->first('blog_name')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">表示件数 <label class="badge badge-danger">必須</label></label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="view_count" value="{{old('view_count', $blog->view_count)}}" class="form-control col-sm-3">
            @if ($errors && $errors->has('view_count')) <div class="text-danger">{{$errors->first('view_count')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass(true)}}">RSS</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if($blog->rss == 1)
                    <input type="radio" value="1" id="rss_off" name="rss" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="rss_off" name="rss" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="rss_off">表示する</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($blog->rss == 0)
                    <input type="radio" value="0" id="rss_on" name="rss" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="rss_on" name="rss" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="rss_on">表示しない</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">RSS件数</label>
        <div class="{{$frame->getSettingInputClass()}}">
            <input type="text" name="rss_count" value="{{old('rss_count', isset($blog->rss_count) ? $blog->rss_count : 0)}}" class="form-control col-sm-3">
            @if ($errors && $errors->has('rss_count')) <div class="text-danger">{{$errors->first('rss_count')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">表示条件</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <div class="custom-control custom-radio custom-control-inline">
                @if($blog->scope == '')
                    <input type="radio" value="" id="scope_all" name="scope" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="scope_all" name="scope" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="scope_all">全て</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($blog->scope == 'year')
                    <input type="radio" value="year" id="scope_year" name="scope" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="year" id="scope_year" name="scope" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="scope_year">年</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                @if($blog->scope == 'fiscal')
                    <input type="radio" value="fiscal" id="scope_fiscal" name="scope" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="fiscal" id="scope_fiscal" name="scope" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="scope_fiscal">年度</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="{{$frame->getSettingLabelClass()}}">指定年</label>
        <div class="{{$frame->getSettingInputClass(true)}}">
            <input type="text" name="scope_value" value="{{old('scope_value', $blog->scope_value)}}" class="form-control col-sm-3">
            @if ($errors && $errors->has('scope_value')) <div class="text-danger">{{$errors->first('scope_value')}}</div> @endif
        </div>
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div class="row">
            <div class="col-3"></div>
            <div class="col-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                    <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                </button>
                <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 
                    <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    @if (empty($blog) || $create_flag)
                        登録確定
                    @else
                        変更確定
                    @endif
                    </span>
                </button>
            </div>

            {{-- 既存ブログの場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
            <div class="col-3 text-right">
                <a data-toggle="collapse" href="#collapse{{$blog_frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$blog_frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">ブログを削除します。<br>このブログに記載した記事も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/blogs/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$blog->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endif
@endsection
