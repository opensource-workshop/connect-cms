{{--
 * 検索の設定編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 検索プラグイン
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.searchs.searchs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
@if ($errors)
    <div class="alert alert-danger" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        エラーがあります。詳しくは各項目を参照してください。
    </div>
@elseif (isset($searchs) && !$searchs->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        設定画面から、使用する検索設定を選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>

        @if ($message)
            {{$message}}
        @else
            @if (empty($searchs) || $create_flag)
                新しい検索設定を登録します。
            @else
                検索設定を変更します。
            @endif
        @endif
    </div>
@endif

@if (isset($searchs))
    @if (!$searchs->id && !$create_flag)
    @else
        <form action="/plugin/searchs/saveBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
            {{ csrf_field() }}

            {{-- create_flag がtrue の場合、新規作成するためにsearchs_id を空にする --}}
            @if ($create_flag)
                <input type="hidden" name="searchs_id" value="">
            @else
                <input type="hidden" name="searchs_id" value="{{$searchs->id}}">
            @endif

            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">検索設定名 <label class="badge badge-danger">必須</label></label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input type="text" name="search_name" value="{{old('search_name', $searchs->search_name)}}" class="form-control">
                    @if ($errors && $errors->has('search_name')) <div class="text-danger">{{$errors->first('search_name')}}</div> @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">1ページの表示件数 <label class="badge badge-danger">必須</label></label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input type="text" name="count" value="{{old('count', $searchs->count)}}" class="form-control col-sm-4">
                </div>
                @if ($errors && $errors->has('count')) <div class="text-danger">{{$errors->first('count')}}</div> @endif
            </div>

            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">登録者の表示</label>
                <div class="{{$frame->getSettingInputClass(true)}}">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if($searchs->view_posted_name == 1)
                            <input type="radio" value="1" id="view_posted_name_1" name="view_posted_name" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="view_posted_name_1" name="view_posted_name" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="view_posted_name_1">表示する</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        @if($searchs->view_posted_name == 0)
                            <input type="radio" value="0" id="view_posted_name_0" name="view_posted_name" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="view_posted_name_0" name="view_posted_name" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="view_posted_name_0">表示しない</label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">登録日時の表示</label><br />
                <div class="{{$frame->getSettingInputClass(true)}}">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if($searchs->view_posted_at == 1)
                            <input type="radio" value="1" id="view_posted_at_1" name="view_posted_at" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="view_posted_at_1" name="view_posted_at" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="view_posted_at_1">表示する</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        @if($searchs->view_posted_at == 0)
                            <input type="radio" value="0" id="view_posted_at_0" name="view_posted_at" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="view_posted_at_0" name="view_posted_at" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="view_posted_at_0">表示しない</label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">対象プラグイン <label class="badge badge-danger mb-0">必須</label></label>
                <div class="{{$frame->getSettingInputClass(true)}}">
                @foreach($searchs->getTargetPlugins() as $target_plugin => $use_flag)
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="checkbox" name="target_plugin[{{$target_plugin}}]" value="{{$target_plugin}}" class="custom-control-input" id="target_plugin_{{$target_plugin}}" @if(old("target_plugin.$target_plugin", $use_flag)) checked=checked @endif>
                        <label class="custom-control-label" for="target_plugin_{{$target_plugin}}">{{$target_plugin}}</label>
                    </div>
                @endforeach
                </div>
                @if ($errors && $errors->has('target_plugin')) <div class="text-danger">{{$errors->first('target_plugin')}}</div> @endif
            </div>

            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">フレームの選択</label>
                <div class="{{$frame->getSettingInputClass(true)}}">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if($searchs->frame_select == 0)
                            <input type="radio" value="0" id="frame_select_0" name="frame_select" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="0" id="frame_select_0" name="frame_select" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="frame_select_0">全て表示する</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        @if($searchs->frame_select == 1)
                            <input type="radio" value="1" id="frame_select_1" name="frame_select" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="1" id="frame_select_1" name="frame_select" class="custom-control-input">
                        @endif
                        <label class="custom-control-label" for="frame_select_1">選択したものだけ表示する</label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">対象ページ - フレーム</label>
                <div class="card {{$frame->getSettingInputClass(false, true)}}">
                    <div class="card-body py-2 pl-0">

                        @foreach($target_plugins_frames as $target_plugins_frame)
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="target_frame_ids[{{$target_plugins_frame->id}}]" value="{{$target_plugins_frame->id}}" class="custom-control-input" id="target_plugins_frame_{{$target_plugins_frame->id}}" @if(old("target_frame_ids.$target_plugins_frame->id", $searchs->isTargetFrame($target_plugins_frame->id))) checked=checked @endif>
                            <label class="custom-control-label" for="target_plugins_frame_{{$target_plugins_frame->id}}">{{$target_plugins_frame->page_name}} - {{$target_plugins_frame->bucket_name}}</label>
                        </div>
                        @endforeach
                        @if ($errors && $errors->has('target_plugins_frames')) <div class="text-danger">{{$errors->first('target_plugins_frames')}}</div> @endif
                    </div>
                </div>
            </div>

            {{-- Submitボタン --}}
            <div class="form-group text-center">
                <div class="row">
                    <div class="col-3"></div>
                    <div class="col-6">
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}">キャンセル</span>
                        </button>
                        <button type="submit" class="btn btn-primary form-horizontal mr-2"><i class="fas fa-check"></i>
                            <span class="{{$frame->getSettingButtonCaptionClass()}}">
                            @if (empty($searchs) || $create_flag)
                                登録
                            @else
                                変更
                            @endif
                            </span>
                        </button>
                    </div>
                    {{-- 既存の検索設定の場合は削除処理のボタンも表示 --}}
                    @if ($create_flag)
                    @else
                        <div class="col-3 text-right">
                            <a data-toggle="collapse" href="#collapse{{$searchs_frame->frames_id}}">
                                <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </form>

        {{-- 既存の検索設定の場合は削除処理のボタンも表示 --}}
        @if ($create_flag)
        @else
        <div id="collapse{{$searchs_frame->frames_id}}" class="collapse" style="margin-top: 8px;">
            <div class="card border-danger">
                <div class="card-body">
                    <span class="text-danger">検索設定を削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
                    <div class="text-center">
                        {{-- 削除ボタン --}}
                        <form action="{{url('/')}}/redirect/plugin/searchs/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$searchs->id}}" method="POST">
                            {{csrf_field()}}
                            <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
@endif
@endsection
