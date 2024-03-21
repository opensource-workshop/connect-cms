{{--
 * RSS設定画面
 *
 * @author horiguchi@opensource-workshop.jp
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category RSS・プラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")

    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.rsses.rsses_frame_edit_tab')

@endsection

@section("plugin_setting_$frame->id")

    @include('plugins.common.errors_form_line')

    @if (($rss && $rss->id) || $is_create)

        <div class="alert alert-info mt-2"><i class="fas fa-exclamation-circle"></i>
            @if ($message)
                {!!$message!!}
            @else
                {{ empty($rss) || $is_create ? '新しいRSS設定を登録します。' : 'RSS設定を変更します。' }}
            @endif
        </div>

    @else

        <div class="alert alert-warning mt-2">
            <i class="fas fa-exclamation-circle"></i> {!! nl2br(e('RSS選択から選択するか、RSS作成で作成してください。')) !!}<br>
        </div>

    @endif

    @if (($rss && $rss->id) || $is_create)

        @if ($is_create)
            {{-- 新規 --}}
            <form action="{{url('/')}}/plugin/rsses/saveBuckets/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST" class="">
        @else
            {{-- 更新 --}}
            <form action="{{url('/')}}/plugin/rsses/saveBuckets/{{$page->id}}/{{$frame_id}}/{{$rss->id}}#frame-{{$frame_id}}" method="POST" class="">
        @endif
        {{ csrf_field() }}

            {{-- RSS名 --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">
                    RSS名 <label class="badge badge-danger">必須</label>
                </label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input
                        type="text"
                        name="rsses_name"
                        value="{{old('rsses_name', $rss->rsses_name)}}"
                        class="form-control @if ($errors && $errors->has('rsses_name')) border-danger @endif"
                        required
                    >
                    @if ($errors && $errors->has('rsses_name')) <div class="text-danger">{{$errors->first('rsses_name')}}</div> @endif
                </div>
            </div>

            {{-- 再取得時間 --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">
                    再取得時間(分) <i class="fas fa-question-circle mr-2" data-toggle="tooltip" title="取得先の負荷軽減のための処置です。設定した時間を経過しないと再取得しません。"></i><label class="badge badge-danger">必須</label>
                </label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <select class="form-control" name="cache_interval">
                        <option value="0" @if(old('cache_interval', $rss->cache_interval) == 0) selected @endif >都度取得</option>
                        <option value="5" @if(old('cache_interval', $rss->cache_interval) == 5) selected @endif >5分</option>
                        <option value="10" @if(old('cache_interval', $rss->cache_interval) == 10) selected @endif >10分</option>
                        <option value="30" @if(old('cache_interval', $rss->cache_interval) == 30) selected @endif >30分</option>
                        <option value="60" @if(old('cache_interval', $rss->cache_interval) == 60) selected @endif >60分</option>
                    </select>
                @if ($errors && $errors->has('cache_interval')) <div class="text-danger">{{$errors->first('cache_interval')}}</div> @endif
                </div>
            </div>

            {{-- まとめて表示 --}}
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass(true)}}">まとめて表示</label>
                <div class="{{$frame->getSettingInputClass(true)}}">
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="0" id="mergesort_flag_0" name="mergesort_flag" class="custom-control-input" @if(old('mergesort_flag', $rss->mergesort_flag) == 0) checked="checked" @endif >
                        <label class="custom-control-label" for="mergesort_flag_0" id="label_mergesort_flag_0">表示しない</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="1" id="mergesort_flag_1" name="mergesort_flag" class="custom-control-input" @if(old('mergesort_flag', $rss->mergesort_flag) == 1) checked="checked" @endif >
                        <label class="custom-control-label" for="mergesort_flag_1" id="label_mergesort_flag_1">表示する</label>
                    </div>
                </div>
            </div>
            {{-- まとめて表示する数 --}}
            <div class="form-group row">
                @php
                    if (!$rss->mergesort_count) {
                        $rss->mergesort_count = 10;
                    }
                @endphp

                <label class="{{$frame->getSettingLabelClass()}}">
                まとめ表示数
                </label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input
                        type="number"
                        name="mergesort_count"
                        value="{{old('mergesort_count', $rss->mergesort_count)}}"
                        class="form-control @if ($errors && $errors->has('mergesort_count')) border-danger @endif"
                    >
                    @if ($errors && $errors->has('mergesort_count')) <div class="text-danger">{{$errors->first('mergesort_count')}}</div> @endif
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
                                {{ empty($rss) || $is_create ? '登録確定' : '変更確定' }}
                            </span>
                        </button>
                    </div>

                    {{-- 既存RSSの場合は削除処理のボタンも表示 --}}
                    @if (!$is_create)
                        <div class="col-3 text-right">
                            <a data-toggle="collapse" href="#collapse{{$rss_frame->id}}">
                                <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </form>

        <div id="collapse{{$rss_frame->id}}" class="collapse" style="margin-top: 8px;">
            <div class="card border-danger">
                <div class="card-body">
                    <span class="text-danger">RSSを削除します。<br>このRSSに登録された内容も削除され、元に戻すことはできないため、よく確認して実行してください。</span>

                    <div class="text-center">
                        {{-- 削除ボタン --}}
                        <form action="{{url('/')}}/redirect/plugin/rsses/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$rss->id}}#frame-{{$frame_id}}" method="POST">
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
