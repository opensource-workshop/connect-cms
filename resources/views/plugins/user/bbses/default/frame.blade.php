{{--
 * フレーム表示設定編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 掲示板プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.bbses.bbses_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('common.errors_form_line')

@if (empty($bbs->id) && $action != 'createBuckets')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-circle"></i>
        選択画面から、使用する掲示板を選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i>
        フレームごとの表示設定を変更します。
    </div>

    <form action="{{url('/')}}/redirect/plugin/bbses/saveView/{{$page->id}}/{{$frame_id}}/{{$bbs->id}}#frame-{{$frame->id}}" method="POST" class="">
        {{ csrf_field() }}
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/bbses/editView/{{$page->id}}/{{$frame_id}}/{{$bbs->bucket_id}}#frame-{{$frame_id}}">

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">表示形式</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                <div class="custom-control custom-radio custom-control-inline">
                    @if($bbs_frame->view_format == 0)
                        <input type="radio" value="0" id="view_format_0" name="view_format" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="view_format_0" name="view_format" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="view_format_0">フラット形式</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if($bbs_frame->view_format == 1)
                        <input type="radio" value="1" id="view_format_1" name="view_format" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="view_format_1" name="view_format" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="view_format_1">スレッド形式</label>（※ 準備中）
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">根記事の表示順</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                <div class="custom-control custom-radio custom-control-inline">
                    @if($bbs_frame->thread_sort_flag == 0)
                        <input type="radio" value="0" id="thread_sort_flag_0" name="thread_sort_flag" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="thread_sort_flag_0" name="thread_sort_flag" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="thread_sort_flag_0">スレッド内の新しい更新日時順</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if($bbs_frame->thread_sort_flag == 1)
                        <input type="radio" value="1" id="thread_sort_flag_1" name="thread_sort_flag" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="thread_sort_flag_1" name="thread_sort_flag" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="thread_sort_flag_1">根記事の新しい日時順</label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">一覧での展開方法</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                <div class="custom-control custom-radio custom-control-inline">
                    @if($bbs_frame->list_format == 0)
                        <input type="radio" value="0" id="list_format_0" name="list_format" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="list_format_0" name="list_format" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="list_format_0">すべて展開</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if($bbs_frame->list_format == 1)
                        <input type="radio" value="1" id="list_format_1" name="list_format" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="list_format_1" name="list_format" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="list_format_1">根記事のみ展開</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if($bbs_frame->list_format == 2)
                        <input type="radio" value="2" id="list_format_2" name="list_format" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="2" id="list_format_2" name="list_format" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="list_format_2">すべて閉じておく</label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">スレッドの記事一覧の下線</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                <div class="custom-control custom-radio custom-control-inline">
                    @if($bbs_frame->list_underline == 0)
                        <input type="radio" value="0" id="list_underline_0" name="list_underline" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="list_underline_0" name="list_underline" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="list_underline_0">表示しない</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if($bbs_frame->list_underline == 1)
                        <input type="radio" value="1" id="list_underline_1" name="list_underline" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="list_underline_1" name="list_underline" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="list_underline_1">表示する</label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">スレッド記事の枠のタイトル</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="thread_caption" value="{{old('thread_caption', $bbs_frame->thread_caption)}}" class="form-control">
                @if ($errors && $errors->has('thread_caption')) <div class="text-danger">{{$errors->first('thread_caption')}}</div> @endif
                <small class="text-muted">※ 根記事のみ展開の場合のもの。空の場合はタイトル枠が非表示になります。</small>
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass(true)}}">詳細でのスレッド記事の展開方法</label>
            <div class="{{$frame->getSettingInputClass(true)}}">
                <div class="custom-control custom-radio custom-control-inline">
                    @if($bbs_frame->thread_format == 0)
                        <input type="radio" value="0" id="thread_format_0" name="thread_format" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="0" id="thread_format_0" name="thread_format" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="thread_format_0">すべて展開</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if($bbs_frame->thread_format == 1)
                        <input type="radio" value="1" id="thread_format_1" name="thread_format" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="1" id="thread_format_1" name="thread_format" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="thread_format_1">詳細表示している記事のみ展開</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    @if($bbs_frame->thread_format == 2)
                        <input type="radio" value="2" id="thread_format_2" name="thread_format" class="custom-control-input" checked="checked">
                    @else
                        <input type="radio" value="2" id="thread_format_2" name="thread_format" class="custom-control-input">
                    @endif
                    <label class="custom-control-label" for="thread_format_2">すべて閉じておく</label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="{{$frame->getSettingLabelClass()}}">表示件数</label>
            <div class="{{$frame->getSettingInputClass()}}">
                <input type="text" name="view_count" value="{{old('view_count', $bbs_frame->view_count)}}" class="form-control">
                @if ($errors && $errors->has('view_count')) <div class="text-danger">{{$errors->first('view_count')}}</div> @endif
                <small class="text-muted">※ 未設定時は10件</small>
            </div>
        </div>

        {{-- Submitボタン --}}
        <div class="form-group text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
            </button>
            <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 
                <span class="{{$frame->getSettingButtonCaptionClass()}}">
                    変更確定
                </span>
            </button>
        </div>
    </form>
@endif
@endsection
