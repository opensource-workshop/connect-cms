{{--
 * 編集画面(フレーム設定)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Blogプラグイン
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.blogs.blogs_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

@if (empty($blog_frame) || empty($blog_frame->blogs_id))
    <div class="alert alert-warning text-center">
        <i class="fas fa-exclamation-circle"></i>
        表示するコンテンツを選択するか、新規作成してください。
    </div>
@else

    {{-- 共通エラーメッセージ 呼び出し --}}
    @include('common.errors_form_line')

    <div class="alert alert-info">
        <i class="fas fa-exclamation-circle"></i>
        フレーム毎の表示条件が設定できます。
    </div>

    <form action="{{url('/')}}/redirect/plugin/blogs/saveBlogFrame/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" method="POST">
        {{ csrf_field() }}

        <div id="app_{{ $frame->id }}">
            <input type="hidden" name="blogs_id" value="{{$blog_frame->blogs_id}}">
            <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/blogs/settingBlogFrame/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">

            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">表示条件</label>
                <div class="{{$frame->getSettingInputClass(true)}}">
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="" id="scope_all" name="scope" class="custom-control-input" @if (old('scope', $blog_frame_setting->scope) == '') checked @endif v-model="v_scope_radio">
                        <label class="custom-control-label" for="scope_all">全て</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="year" id="scope_year" name="scope" class="custom-control-input" @if (old('scope', $blog_frame_setting->scope) == 'year') checked @endif v-model="v_scope_radio">
                        <label class="custom-control-label" for="scope_year">年</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="fiscal" id="scope_fiscal" name="scope" class="custom-control-input" @if (old('scope', $blog_frame_setting->scope) == 'fiscal') checked @endif v-model="v_scope_radio">
                        <label class="custom-control-label" for="scope_fiscal">年度</label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">指定年</label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input type="text" name="scope_value" value="{{old('scope_value', $blog_frame_setting->scope_value)}}" class="form-control col-sm-3 @if ($errors->has('scope_value')) border-danger @endif" v-model="v_scope_value">
                    @include('common.errors_inline', ['name' => 'scope_value'])
                    <small class="text-muted">※ 表示条件と指定年の組み合わせで投稿日時を参照し、ブログ一覧に表示します。</small><br>
                    <small class="text-muted">※ 表示範囲：@{{ showTargetYmd }}</small>
                </div>
            </div>

            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">重要記事の扱い</label><br />
                <div class="{{$frame->getSettingInputClass()}}">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if (old('important_view', $blog_frame_setting->important_view) == "")
                            <input type="radio" value="" id="important_view_0" name="important_view" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="" id="important_view_0" name="important_view" class="custom-control-input">
                        @endif
                        <label class="custom-control-label text-nowrap" for="important_view_0">区別しない</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        @if (old('important_view', $blog_frame_setting->important_view) == "top")
                            <input type="radio" value="top" id="important_view_1" name="important_view" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="top" id="important_view_1" name="important_view" class="custom-control-input">
                        @endif
                        <label class="custom-control-label text-nowrap" for="important_view_1">上に表示する</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        @if (old('important_view', $blog_frame_setting->important_view) == "important_only")
                            <input type="radio" value="important_only" id="important_view_2" name="important_view" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="important_only" id="important_view_2" name="important_view" class="custom-control-input">
                        @endif
                        <label class="custom-control-label text-nowrap" for="important_view_2">重要記事のみ表示する</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        @if (old('important_view', $blog_frame_setting->important_view) == "not_important")
                            <input type="radio" value="not_important" id="important_view_3" name="important_view" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="not_important" id="important_view_3" name="important_view" class="custom-control-input">
                        @endif
                        <label class="custom-control-label text-nowrap" for="important_view_3">重要記事を表示しない</label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">{{BlogFrameConfig::getDescription('blog_display_created_name')}}</label>
                <div class="{{$frame->getSettingInputClass(true)}}">
                    <div class="custom-control custom-radio custom-control-inline">
                        @if (FrameConfig::getConfigValueAndOld($frame_configs, BlogFrameConfig::blog_display_created_name) === '' ||
                            FrameConfig::getConfigValueAndOld($frame_configs, BlogFrameConfig::blog_display_created_name) === BlogDisplayCreatedName::none)
                            <input type="radio" value="{{BlogDisplayCreatedName::none}}" id="{{BlogFrameConfig::blog_display_created_name}}_0" name="{{BlogFrameConfig::blog_display_created_name}}" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="{{BlogDisplayCreatedName::none}}" id="{{BlogFrameConfig::blog_display_created_name}}_0" name="{{BlogFrameConfig::blog_display_created_name}}" class="custom-control-input">
                        @endif
                        <label class="custom-control-label text-nowrap" for="{{BlogFrameConfig::blog_display_created_name}}_0">{{BlogDisplayCreatedName::getDescription('none')}}</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        @if (FrameConfig::getConfigValueAndOld($frame_configs, BlogFrameConfig::blog_display_created_name) === BlogDisplayCreatedName::display)
                            <input type="radio" value="{{BlogDisplayCreatedName::display}}" id="{{BlogFrameConfig::blog_display_created_name}}_1" name="{{BlogFrameConfig::blog_display_created_name}}" class="custom-control-input" checked="checked">
                        @else
                            <input type="radio" value="{{BlogDisplayCreatedName::display}}" id="{{BlogFrameConfig::blog_display_created_name}}_1" name="{{BlogFrameConfig::blog_display_created_name}}" class="custom-control-input">
                        @endif
                        <label class="custom-control-label text-nowrap" for="{{BlogFrameConfig::blog_display_created_name}}_1">{{BlogDisplayCreatedName::getDescription('display')}}</label>
                    </div>
                </div>
            </div>

            {{-- Submitボタン --}}
            <div class="form-group text-center mt-3">
                <div class="row">
                    <div class="col-12">
                        <a class="btn btn-secondary mr-2" href="{{URL::to($page->permanent_link)}}">
                            <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> キャンセル</span>
                        </a>
                        <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                            <span class="{{$frame->getSettingButtonCaptionClass()}}">
                                設定変更
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        new Vue({
          el: "#app_{{ $frame->id }}",
          data: {
            // 表示条件
            v_scope_radio: '{{ old('scope', $blog_frame_setting->scope) }}',
            // 指定年
            v_scope_value: '{{ old('scope_value', $blog_frame_setting->scope_value) }}'
          },
          computed: {
            // 表示条件と指定年に応じた抽出範囲のテキストを返す
            showTargetYmd: function () {
                let target_range_text = '-';
                if(this.v_scope_radio == ""){
                    target_range_text = '全件';
                }else if(this.v_scope_radio == "year" && this.isNumber(this.v_scope_value) && this.v_scope_value.length == 4){
                    target_range_text = this.v_scope_value + '年1月1日 00:00:00 ~ ' + this.v_scope_value + '年12月31日 23:59:59';
                }else if(this.v_scope_radio == "fiscal" && this.isNumber(this.v_scope_value) && this.v_scope_value.length == 4){
                    target_range_text = this.v_scope_value + '年4月1日 00:00:00 ~ ' + (Number(this.v_scope_value) + 1) + '年3月31日 23:59:59';
                }
              return target_range_text;
            }
          },
          methods: {
            // 数値チェック
            isNumber: function (value) {
              var regex = new RegExp(/^[0-9]+$/);
              return regex.test(value);
            }
          }
        })
    </script>
@endif
@endsection
