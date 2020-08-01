{{--
 * 課題管理　総合評価設定画面テンプレート。
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
<form action="{{url('/')}}/redirect/plugin/learningtasks/saveEvaluate/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_learningtasks_posts" enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="/plugin/learningtasks/editEvaluate/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}">

    <div class="card mb-3 border-danger">
        <div class="card-body">
            <h5 class="mb-0">{!!$learningtasks_posts->post_title!!}</h5>
        </div>
    </div>

    <h5><span class="badge badge-secondary">使用設定</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">総合評価機能</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_evaluate === null)
                    <input type="radio" value="" id="use_evaluate_null" name="use_evaluate" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="use_evaluate_null" name="use_evaluate" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_evaluate_null">課題管理設定に従う（XXXXXXXXXXX）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_evaluate === 0)
                    <input type="radio" value="0" id="use_evaluate_0" name="use_evaluate" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="0" id="use_evaluate_0" name="use_evaluate" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_evaluate_0">使用しない</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if($learningtasks_posts->use_evaluate === 1)
                    <input type="radio" value="1" id="use_evaluate_1" name="use_evaluate" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="1" id="use_evaluate_1" name="use_evaluate" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="use_evaluate_1">この課題独自に設定する</label>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">課題独自設定</span></h5>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">評価</label>
        <div class="col-md-9 d-md-flex">
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_evaluate_evaluate_file" value="1" class="custom-control-input" id="use_evaluate_evaluate_file" @if(old("use_evaluate_evaluate_file", $learningtasks_posts->use_evaluate_evaluate_file)) checked=checked @endif>
                <label class="custom-control-label" for="use_evaluate_evaluate_file">アップロード</label>
            </div>
            <div class="custom-control custom-checkbox mr-3">
                <input type="checkbox" name="use_evaluate_evaluate_comment" value="1" class="custom-control-input" id="use_evaluate_evaluate_comment" @if(old("use_evaluate_evaluate_comment", $learningtasks_posts->use_evaluate_evaluate_comment)) checked=checked @endif>
                <label class="custom-control-label" for="use_evaluate_evaluate_comment">コメント入力</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="use_evaluate_evaluate_mail" value="1" class="custom-control-input" id="use_evaluate_evaluate_mail" @if(old("use_evaluate_evaluate_mail", $learningtasks_posts->use_evaluate_evaluate_mail)) checked=checked @endif>
                <label class="custom-control-label" for="use_evaluate_evaluate_mail">メール送信（受講者宛）</label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            @if (empty($learningtasks_posts->id))
            <div class="col-12">
            @else
            <div class="col-3 d-none d-xl-block"></div>
            <div class="col-9 col-xl-6">
            @endif
                <div class="text-center">
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/learningtasks/edit/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span> キャンセル</span></button>
                    <input type="hidden" name="bucket_id" value="">
                    @if (empty($learningtasks_posts->id))
                        <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('更新します。\nよろしいですか？')"><i class="fas fa-check"></i> 登録確定</button>
                    @else
                        <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('更新します。\nよろしいですか？')"><i class="fas fa-check"></i> 変更確定</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>
@endif
<script>
$('.custom-file-input').on('change',function(){
    $(this).next('.custom-file-label').html($(this)[0].files[0].name);
})
</script>
@endsection
