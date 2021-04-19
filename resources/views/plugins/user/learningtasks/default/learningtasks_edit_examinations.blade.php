{{--
 * 課題管理記事登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
--}}
@extends('core.cms_frame_base')

{{-- 編集画面側のフレームメニュー --}}
@include('plugins.user.learningtasks.learningtasks_setting_edit_tab')

@section("plugin_contents_$frame->id")

@include('common.errors_form_line')

{{-- 試験設定フォーム --}}
@if (empty($learningtasks_posts->id))
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">課題データを作成してから、試験の設定をしてください。</p>
        </div>
    </div>
@else
<form action="{{url('/')}}/redirect/plugin/learningtasks/saveExaminations/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_learningtasks_posts" enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/editExaminations/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}">

    <div class="card mb-3 border-danger">
        <div class="card-body">
            <h5 class="mb-0">{!!$learningtasks_posts->post_title!!}</h5>
        </div>
    </div>

    <h5><span class="badge badge-secondary">使用項目の設定</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">試験提出機能</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if(empty(old("post_examination_setting", $tool->getFunction('post_examination_setting', true))))
                    <input type="radio" value="" id="examination_null" name="post_examination_setting" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_post_examination.show">
                @else
                    <input type="radio" value="" id="examination_null" name="post_examination_setting" class="custom-control-input" data-toggle="collapse" data-target="#collapse_post_examination.show">
                @endif
                <label class="custom-control-label" for="examination_null">課題管理設定に従う</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if(old("post_examination_setting", $tool->getFunction('post_examination_setting', true)) == 'off')
                    <input type="radio" value="off" id="use_examination_off" name="post_examination_setting" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_post_examination.show">
                @else
                    <input type="radio" value="off" id="use_examination_off" name="post_examination_setting" class="custom-control-input" data-toggle="collapse" data-target="#collapse_post_examination.show">
                @endif
                <label class="custom-control-label" for="use_examination_off">使用しない</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if(old("post_examination_setting", $tool->getFunction('post_examination_setting', true)) == 'on')
                    <input type="radio" value="on" id="use_examination_on" name="post_examination_setting" class="custom-control-input" checked="checked" data-toggle="collapse" data-target="#collapse_post_examination:not(.show)" aria-expanded="true" aria-controls="collapse_post_examination">
                @else
                    <input type="radio" value="on" id="use_examination_on" name="post_examination_setting" class="custom-control-input" data-toggle="collapse" data-target="#collapse_post_examination:not(.show)" aria-expanded="true" aria-controls="collapse_post_examination">
                @endif
                <label class="custom-control-label" for="use_examination_on">この課題独自に設定する</label>
            </div>
        </div>
    </div>

    {{-- 独自設定の場合のみ表示、その他は隠す --}}
    <div class="collapse {{$tool->getSettingShowstr("post_examination_setting")}} collapse_post_examination" id="collapse_post_examination">
        <h5><span class="badge badge-secondary">課題独自の項目設定</span></h5>
        <div class="form-group row mb-0">
            <label class="col-md-3 text-md-right">試験提出機能</label>
            <div class="col-md-9 d-md-flex">

                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_examination]" value="on" class="custom-control-input" id="use_examination" @if(old("use_examination", $tool->getFunction('use_examination', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_examination">提出</label>
                </div>
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_examination_evaluate]" value="on" class="custom-control-input" id="use_examination_evaluate" @if(old("use_examination_evaluate", $tool->getFunction('use_examination_evaluate', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_examination_evaluate">評価</label>
                </div>
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_examination_reference]" value="on" class="custom-control-input" id="use_examination_reference" @if(old("use_examination_reference", $tool->getFunction('use_examination_reference', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_examination_reference">教員から参考資料</label>
                </div>
            </div>
        </div>

        <div class="form-group row mb-0">
            <label class="col-md-3 text-md-right">提出</label>
            <div class="col-md-9 d-md-flex">

                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_examination_file]" value="on" class="custom-control-input" id="use_examination_file" @if(old("use_examination_file", $tool->getFunction('use_examination_file', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_examination_file">アップロード</label>
                </div>
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_examination_comment]" value="on" class="custom-control-input" id="use_examination_comment" @if(old("use_examination_comment", $tool->getFunction('use_examination_comment', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_examination_comment">本文入力</label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="post_settings[use_examination_mail]" value="on" class="custom-control-input" id="use_examination_mail" @if(old("use_examination_mail", $tool->getFunction('use_examination_mail', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_examination_mail">メール送信（教員宛）</label>
                </div>
            </div>
        </div>

        <div class="form-group row mb-0">
            <label class="col-md-3 text-md-right">評価</label>
            <div class="col-md-9 d-md-flex">
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_examination_evaluate_file]" value="on" class="custom-control-input" id="use_examination_evaluate_file" @if(old("use_examination_evaluate_file", $tool->getFunction('use_examination_evaluate_file', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_examination_evaluate_file">アップロード</label>
                </div>
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_examination_evaluate_comment]" value="on" class="custom-control-input" id="use_examination_evaluate_comment" @if(old("use_examination_evaluate_comment", $tool->getFunction('use_examination_evaluate_comment', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_examination_evaluate_comment">コメント入力</label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="post_settings[use_examination_evaluate_mail]" value="on" class="custom-control-input" id="use_examination_evaluate_mail" @if(old("use_examination_evaluate_mail", $tool->getFunction('use_examination_evaluate_mail', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_examination_evaluate_mail">メール送信（受講者宛）</label>
                </div>
            </div>
        </div>

        <div class="form-group row mb-0">
            <label class="col-md-3 text-md-right">教員から参考資料</label>
            <div class="col-md-9 d-md-flex">
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_examination_reference_file]" value="on" class="custom-control-input" id="use_examination_reference_file" @if(old("use_examination_reference_file", $tool->getFunction('use_examination_reference_file', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_examination_reference_file">アップロード</label>
                </div>
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_examination_reference_comment]" value="on" class="custom-control-input" id="use_examination_reference_comment" @if(old("use_examination_reference_comment", $tool->getFunction('use_examination_reference_comment', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_examination_reference_comment">コメント入力</label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="post_settings[use_examination_reference_mail]" value="on" class="custom-control-input" id="use_examination_reference_mail" @if(old("use_examination_reference_mail", $tool->getFunction('use_examination_reference_mail', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_examination_reference_mail">メール送信（受講者宛）</label>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-md-3 text-md-right">表示方法</label>
            <div class="col-md-9 d-md-flex">
                <div class="custom-control custom-checkbox mr-3">
                    <input type="checkbox" name="post_settings[use_examination_status_collapse]" value="on" class="custom-control-input" id="use_examination_status_collapse" @if(old("use_examination_status_collapse", $tool->getFunction('use_examination_status_collapse', true)) == 'on') checked=checked @endif>
                    <label class="custom-control-label" for="use_examination_status_collapse">履歴を開閉する</label>
                </div>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">申し込み設定</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">申し込み可能判定</label>
        <div class="col-md-9">
            <div class="custom-control custom-radio custom-control-inline">
                @if(empty(old("post_examination_timing", $tool->getFunction('post_examination_timing', true))))
                    <input type="radio" value="" id="post_examination_timing_null" name="post_examination_timing" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="" id="post_examination_timing_null" name="post_examination_timing" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="post_examination_timing_null">レポートが合格してから</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if(old("post_examination_timing", $tool->getFunction('post_examination_timing', true)) == 'one')
                    <input type="radio" value="one" id="post_examination_timing_one" name="post_examination_timing" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="one" id="post_examination_timing_one" name="post_examination_timing" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="post_examination_timing_one">レポートが1回でも提出済みなら（合否のチェックはしない）</label>
            </div><br />
            <div class="custom-control custom-radio custom-control-inline">
                @if(old("post_examination_timing", $tool->getFunction('post_examination_timing', true)) == 'no_fail')
                    <input type="radio" value="no_fail" id="examination_timing_no_fail" name="post_examination_timing" class="custom-control-input" checked="checked">
                @else
                    <input type="radio" value="no_fail" id="examination_timing_no_fail" name="post_examination_timing" class="custom-control-input">
                @endif
                <label class="custom-control-label" for="examination_timing_no_fail">レポートが提出済み＆最新が不合格ではない（合格のチェックはしない）</label>
            </div>
        </div>
    </div>

    <h5><span class="badge badge-secondary">日時設定</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">試験日時一覧</label>
        <div class="col-md-9">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th nowrap>削除</th>
                            <th nowrap>試験開始</th>
                            <th nowrap>試験終了</th>
                            <th nowrap>申込終了</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($examinations as $examination)
                            <tr>
                                <td nowrap class="align-middle text-center">
                                    <input type="hidden" value="{{$examination->id}}" name="examinations_id[{{$examination->id}}]">

                                    <div class="custom-control custom-checkbox">
                                        {{-- チェック外した場合にも値を飛ばす対応 --}}
                                        <input type="hidden" value="0" name="del_examinations[{{$examination->id}}]">

                                        <input type="checkbox" name="del_examinations[{{$examination->id}}]" value="1" class="custom-control-input" id="del_examinations[{{$examination->id}}]" @if(old("del_examination.$examination->id")) checked=checked @endif>
                                        <label class="custom-control-label" for="del_examinations[{{$examination->id}}]"></label>
                                    </div>
                                </td>
                                <td nowrap class="align-middle">{{$examination->start_at->format('Y-m-d H:i')}}</td>
                                <td nowrap class="align-middle">{{$examination->end_at->format('Y-m-d H:i')}}</td>
                                <td nowrap class="align-middle">@if ($examination->entry_end_at) {{$examination->entry_end_at->format('Y-m-d H:i')}} @endif</td>
                            </tr>
                        @empty
                            <tr>
                                <td nowrap class="align-middle" colspan="4">※ 設定されている試験がありません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <small class="text-muted">
                    ※ 削除する場合はチェックします。<br />
                    ※ 例えば「申込終了日時」を 4/19 00:00 と設定した場合、4/18 23:59まで申込可能になります。<br />
                    ※ 「申込終了日時」を設定しない場合、「試験終了日時」まで申込可能になります。<br />
                </small>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 text-md-right">試験日時追加</label>
        <div class="col-md">
            <div class="row">
                <div class="col-md-5">
                    <div class="input-group date" id="start_at" data-target-input="nearest">
                        <input type="text" name="start_at" value="{{old('start_at')}}" class="form-control datetimepicker-input" data-target="#start_at" placeholder="開始日時">
                        <div class="input-group-append" data-target="#start_at" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                    @if ($errors && $errors->has('start_at')) <div class="text-danger">{{$errors->first('start_at')}}</div> @endif
                </div>
                <div class="col-md-5">
                    <div class="input-group date" id="end_at" data-target-input="nearest">
                        <input type="text" name="end_at" value="{{old('end_at')}}" class="form-control datetimepicker-input" data-target="#end_at" placeholder="終了日時">
                        <div class="input-group-append" data-target="#end_at" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                    @if ($errors && $errors->has('end_at')) <div class="text-danger">{{$errors->first('end_at')}}</div> @endif
                </div>
                <div class="col-md-5"></div>
                <div class="col-md-5">
                    <div class="input-group date" id="entry_end_at" data-target-input="nearest">
                        <input type="text" name="entry_end_at" value="{{old('entry_end_at')}}" class="form-control datetimepicker-input" data-target="#entry_end_at" placeholder="申込終了日時">
                        <div class="input-group-append" data-target="#entry_end_at" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                    @if ($errors && $errors->has('entry_end_at')) <div class="text-danger">{{$errors->first('entry_end_at')}}</div> @endif
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(function () {
            $('#start_at').datetimepicker({
                locale: 'ja',
                sideBySide: true,
                dayViewHeaderFormat: 'YYYY年 M月',
                format: 'YYYY-MM-DD HH:mm'
            });
            $('#end_at').datetimepicker({
                locale: 'ja',
                sideBySide: true,
                dayViewHeaderFormat: 'YYYY年 M月',
                format: 'YYYY-MM-DD HH:mm'
            });
            $('#entry_end_at').datetimepicker({
                locale: 'ja',
                sideBySide: true,
                dayViewHeaderFormat: 'YYYY年 M月',
                format: 'YYYY-MM-DD HH:mm'
            });
        });
    </script>

    <h5><span class="badge badge-secondary">問題設定</span></h5>
    <div class="form-group row">
        <label class="col-md-3 text-md-right">ファイル一覧</label>
        <div class="col-md-9">
            <div class="card p-2">
            @isset($post_files)
            @foreach($post_files as $examination_file)
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="del_task_file[{{$examination_file->id}}]" value="1" class="custom-control-input" id="del_task_file[{{$examination_file->id}}]" @if(old("del_task_file.$examination_file->id")) checked=checked @endif>
                    <label class="custom-control-label" for="del_task_file[{{$examination_file->id}}]"><a href="{{url('/')}}/file/{{$examination_file->upload_id}}" target="_blank" rel="noopener">{{$examination_file->client_original_name}}</a></label>
                </div>
            @endforeach
            @else
                <div class="card-body p-0">
                    試験関係のファイルは添付されていません。
                </div>
            @endisset
            </div>
            <small class="text-muted">削除する場合はチェックします。</small>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 text-md-right" for="add_task_file">試験問題など</label>
        <div class="col-md-9">
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="add_task_file" name="add_task_file">
                <label class="custom-file-label" for="add_task_file" data-browse="参照">PDF もしくは ワード形式。</label>
                @if ($errors && $errors->has('add_task_file')) <div class="text-danger">{{$errors->first('add_task_file')}}</div> @endif
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
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}'">
                        <i class="fas fa-angle-left"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> 詳細へ</span>
                    </button>
                    <button type="button" class="btn btn-secondary mr-2" onclick="location.reload()">
                        {{-- <i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span> --}}
                        <i class="fas fa-redo-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span>
                    </button>
                    <input type="hidden" name="bucket_id" value="">
                    {{-- change: 課題管理の試験設定は、登録・更新時に確認ダイアログを表示しない（試験日時登録で何度も確定ボタン押すため）
                    @if (empty($learningtasks_posts->id))
                        <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('更新します。\nよろしいですか？')"><i class="fas fa-check"></i> 登録確定</button>
                    @else
                        <button type="submit" class="btn btn-primary" onclick="javascript:return confirm('更新します。\nよろしいですか？')"><i class="fas fa-check"></i> 変更確定</button>
                    @endif
                    --}}
                    @if (empty($learningtasks_posts->id))
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 登録確定</button>
                    @else
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更確定</button>
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
