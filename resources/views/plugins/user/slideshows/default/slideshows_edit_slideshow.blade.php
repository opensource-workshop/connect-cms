{{--
 * スライドショー設定画面
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スライドショー・プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")

    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.slideshows.slideshows_frame_edit_tab')

@endsection

@section("plugin_setting_$frame->id")

    @include('plugins.common.errors_form_line')

    @if (($slideshow && $slideshow->id) || $is_create)

        <div class="alert alert-info mt-2"><i class="fas fa-exclamation-circle"></i>
            @if ($message)
                {!!$message!!}
            @else
                {{ empty($slideshow) || $is_create ? '新しいスライドショー設定を登録します。' : 'スライドショー設定を変更します。' }}
            @endif
        </div>

    @else

        <div class="alert alert-warning mt-2">
            <i class="fas fa-exclamation-circle"></i> {!! nl2br(e('スライドショー選択から選択するか、スライドショー作成で作成してください。')) !!}<br>
        </div>

    @endif

    @if (($slideshow && $slideshow->id) || $is_create)

        @if ($is_create)
            {{-- 新規 --}}
            <form action="{{url('/')}}/plugin/slideshows/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" class="">
        @else
            {{-- 更新 --}}
            <form action="{{url('/')}}/plugin/slideshows/saveBuckets/{{$page->id}}/{{$frame_id}}/{{$slideshow->id}}#frame-{{$frame_id}}" method="POST" class="">
        @endif
        {{ csrf_field() }}

            {{-- スライドショー名 --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">スライドショー名 <label class="badge badge-danger">必須</label></label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input
                        type="text"
                        name="slideshows_name"
                        value="{{old('slideshows_name', $slideshow->slideshows_name)}}"
                        class="form-control @if ($errors && $errors->has('slideshows_name')) border-danger @endif"
                        required
                    >
                    @if ($errors && $errors->has('slideshows_name')) <div class="text-danger">{{$errors->first('slideshows_name')}}</div> @endif
                </div>
            </div>

            {{-- コントロールの表示 --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass(true)}}">コントロールの表示</label>
                <div class="{{$frame->getSettingInputClass(true)}}">
                    @foreach (ShowType::enum as $key => $value)
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                type="radio"
                                value="{{ $key }}"
                                id="{{ "control_display_flag_${key}" }}"
                                name="control_display_flag"
                                class="custom-control-input"
                                {{ $slideshow->control_display_flag == $key ? 'checked' : '' }}
                            >
                            <label class="custom-control-label" for="{{ "control_display_flag_${key}" }}" id="{{ "label_control_display_flag_${key}" }}">
                                {{ $value }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- インジケータの表示 --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass(true)}}">インジケータの表示</label>
                <div class="{{$frame->getSettingInputClass(true)}}">
                    @foreach (ShowType::enum as $key => $value)
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                type="radio"
                                value="{{ $key }}"
                                id="{{ "indicators_display_flag_${key}" }}"
                                name="indicators_display_flag"
                                class="custom-control-input"
                                {{ $slideshow->indicators_display_flag == $key ? 'checked' : '' }}
                            >
                            <label class="custom-control-label" for="{{ "indicators_display_flag_${key}" }}" id="{{ "label_indicators_display_flag_${key}" }}">
                                {{ $value }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- フェードの使用 --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass(true)}}">フェードの使用</label>
                <div class="{{$frame->getSettingInputClass(true)}}">
                    @foreach (UseType::enum as $key => $value)
                        <div class="custom-control custom-radio custom-control-inline">
                            <input
                                type="radio"
                                value="{{ $key }}"
                                id="{{ "fade_use_flag_${key}" }}"
                                name="fade_use_flag"
                                class="custom-control-input"
                                {{ $slideshow->fade_use_flag == $key ? 'checked' : '' }}
                            >
                            <label class="custom-control-label" for="{{ "fade_use_flag_${key}" }}" id="{{ "label_fade_use_flag_${key}" }}">
                                {{ $value }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 画像の静止時間 --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">
                    画像の静止時間（ミリ秒） <i class="fas fa-question-circle mr-2" data-toggle="tooltip" title="1000ミリ秒 = 1秒"></i>
                    <br><label class="badge badge-danger">必須</label></label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input
                        type="number"
                        name="image_interval"
                        value="{{old('image_interval', isset($slideshow->image_interval) ? $slideshow->image_interval : 5000)}}"
                        class="form-control @if ($errors && $errors->has('image_interval')) border-danger @endif"
                        required
                    >
                    @if ($errors && $errors->has('image_interval')) <div class="text-danger">{{$errors->first('image_interval')}}</div> @endif
                </div>
            </div>

            {{-- スライドショーの高さ --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">
                    高さ（px）
                </label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input
                        type="number"
                        name="height"
                        value="{{old('height', isset($slideshow->height) ? $slideshow->height : '')}}"
                        class="form-control @if ($errors && $errors->has('height')) border-danger @endif"
                    >
                    @if ($errors && $errors->has('height')) <div class="text-danger">{{$errors->first('height')}}</div> @endif
                    <small class="text-muted">未指定の場合、フレーム幅に合わせた画像の高さで表示されます。</small>
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
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i>
                            <span class="{{$frame->getSettingButtonCaptionClass()}}">
                                {{ empty($slideshow) || $is_create ? '登録確定' : '変更確定' }}
                            </span>
                        </button>
                    </div>

                    {{-- 既存スライドショーの場合は削除処理のボタンも表示 --}}
                    @if (!$is_create)
                        <div class="col-3 text-right">
                            <a data-toggle="collapse" href="#collapse{{$slideshow_frame->id}}">
                                <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </form>

        <div id="collapse{{$slideshow_frame->id}}" class="collapse" style="margin-top: 8px;">
            <div class="card border-danger">
                <div class="card-body">
                    <span class="text-danger">スライドショーを削除します。<br>このスライドショーに登録された内容も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

                    <div class="text-center">
                        {{-- 削除ボタン --}}
                        <form action="{{url('/')}}/redirect/plugin/slideshows/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$slideshow->id}}#frame-{{$frame_id}}" method="POST">
                            {{csrf_field()}}
                            <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    @endif
    <script>
        /**
        * ツールチップ
        */
        $(function () {
            // 有効化
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@endsection
