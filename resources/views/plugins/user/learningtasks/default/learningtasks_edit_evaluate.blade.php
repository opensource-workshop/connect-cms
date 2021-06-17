{{--
 * 課題管理・総合評価設定画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
--}}
@extends('core.cms_frame_base')

{{-- 編集画面側のフレームメニュー --}}
@include('plugins.user.learningtasks.learningtasks_setting_edit_tab')

@section("plugin_contents_$frame->id")

{{-- 試験設定フォーム --}}
@if (empty($learningtasks_posts->id))
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">課題データを作成してから、試験の設定をしてください。</p>
        </div>
    </div>
@else
    <form action="{{url('/')}}/redirect/plugin/learningtasks/saveEvaluate/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_learningtasks_posts">
        {{ csrf_field() }}
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/editEvaluate/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}">

        <div class="card mb-3 border-danger">
            <div class="card-body">
                <h5 class="mb-0">{!!$learningtasks_posts->post_title!!}</h5>
            </div>
        </div>

        <h5><span class="badge badge-secondary">使用項目の設定</span></h5>
        <div class="form-group row">
            <label class="col-md-3 text-md-right">総合評価機能</label>
            <div class="col-md-9">
                <div class="custom-control custom-radio custom-control-inline">
                    @if(empty($tool->getFunction('post_evaluate_setting', true)))
                        <input type="radio" value="" id="post_evaluate_setting_null" name="post_evaluate_setting" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_post_evaluate_setting.show">
                    @else
                        <input type="radio" value="" id="post_evaluate_setting_null" name="post_evaluate_setting" class="custom-control-input" data-toggle="collapse" data-target="#collapse_post_evaluate_setting.show">
                    @endif
                    <label class="custom-control-label" for="post_evaluate_setting_null">課題管理設定に従う</label>
                </div><br />
                <div class="custom-control custom-radio custom-control-inline">
                    @if($tool->getFunction('post_evaluate_setting', true) == 'off')
                        <input type="radio" value="off" id="post_evaluate_setting_off" name="post_evaluate_setting" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_post_evaluate_setting.show">
                    @else
                        <input type="radio" value="off" id="post_evaluate_setting_off" name="post_evaluate_setting" class="custom-control-input" data-toggle="collapse" data-target="#collapse_post_evaluate_setting.show">
                    @endif
                    <label class="custom-control-label" for="post_evaluate_setting_off">使用しない</label>
                </div><br />
                <div class="custom-control custom-radio custom-control-inline">
                    @if($tool->getFunction('post_evaluate_setting', true) == 'on')
                        <input type="radio" value="on" id="post_evaluate_setting_on" name="post_evaluate_setting" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_post_evaluate_setting:not(.show)" aria-expanded="true" aria-controls="collapse_post_evaluate_setting">
                    @else
                        <input type="radio" value="on" id="post_evaluate_setting_on" name="post_evaluate_setting" class="custom-control-input" data-toggle="collapse" data-target="#collapse_post_evaluate_setting:not(.show)" aria-expanded="true" aria-controls="collapse_post_evaluate_setting">
                    @endif
                    <label class="custom-control-label" for="post_evaluate_setting_on">この課題独自に設定する</label>
                </div>
            </div>
        </div>

        {{-- 独自設定の場合のみ表示、その他は隠す --}}
        <div class="collapse {{$tool->getSettingShowstr("post_evaluate_setting")}} collapse_post_evaluate_setting" id="collapse_post_evaluate_setting">
            <h5><span class="badge badge-secondary">課題独自の項目設定</span></h5>

            <div class="form-group row mb-0">
                <label class="col-md-3 text-md-right">使用する総合評価機能</label>
                <div class="col-md-9 d-md-flex">
                    <div class="custom-control custom-checkbox mr-3">
                        <input type="checkbox" name="post_settings[use_evaluate]" value="on" class="custom-control-input" id="use_evaluate" data-toggle="collapse" data-target="#collapse_use_evaluate" aria-expanded="false" aria-controls="collapse_use_evaluate" @if(old("post_settings.use_evaluate", $tool->getFunction('use_evaluate', true)) == 'on') checked=checked @endif>
                        <label class="custom-control-label" for="use_evaluate">評価（総合評価機能を使う）</label><br />
                        <small class="text-muted">※ 総合評価は、レポート提出と試験を使用し、両方合格（A～C評価）した場合のみ、入力できます。</small>
                    </div>
                </div>
            </div>

            {{-- 評価（総合評価機能を使う）の場合のみ表示、他は隠す --}}
            <div class="collapse" id="collapse_use_evaluate">

                <div class="form-group row">
                    <label class="col-md-3 text-md-right">総合評価コメント</label>
                    <div class="col-md-9 d-md-flex">
                        <div class="custom-control custom-checkbox mr-3">
                            <input type="checkbox" name="post_settings[use_evaluate_file]" value="on" class="custom-control-input" id="use_evaluate_file" @if(old("post_settings.use_evaluate_file", $tool->getFunction('use_evaluate_file', true)) == 'on') checked=checked @endif>
                            <label class="custom-control-label" for="use_evaluate_file">アップロード</label>
                        </div>
                        <div class="custom-control custom-checkbox mr-3">
                            <input type="checkbox" name="post_settings[use_evaluate_comment]" value="on" class="custom-control-input" id="use_evaluate_comment" @if(old("post_settings.use_evaluate_comment", $tool->getFunction('use_evaluate_comment', true)) == 'on') checked=checked @endif>
                            <label class="custom-control-label" for="use_evaluate_comment">コメント入力</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="post_settings[use_evaluate_mail]" value="on" class="custom-control-input" id="use_evaluate_mail" @if(old("post_settings.use_evaluate_mail", $tool->getFunction('use_evaluate_mail', true)) == 'on') checked=checked @endif>
                            <label class="custom-control-label" for="use_evaluate_mail">メール送信（受講者宛）</label>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}'">
                <i class="fas fa-angle-left"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> 詳細へ</span>
            </button>
            <button type="button" class="btn btn-secondary mr-2" onclick="location.reload()">
                {{-- <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span> --}}
                <i class="fas fa-undo-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span>
            </button>
            <input type="hidden" name="bucket_id" value="">
            @if (empty($learningtasks_posts->id))
                <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('更新します。\nよろしいですか？')"><i class="fas fa-check"></i> 登録確定</button>
            @else
                <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('更新します。\nよろしいですか？')"><i class="fas fa-check"></i> 変更確定</button>
            @endif
        </div>
    </form>

    {{-- 初期状態で開くもの --}}
    @if(old("post_settings.use_evaluate", $tool->getFunction('use_evaluate', true)) == 'on')
        <script>
            $('#collapse_use_evaluate').collapse('show')
        </script>
    @endif

@endif

<script>
    $('.custom-file-input').on('change',function(){
        $(this).next('.custom-file-label').html($(this)[0].files[0].name);
    })
</script>

@endsection
