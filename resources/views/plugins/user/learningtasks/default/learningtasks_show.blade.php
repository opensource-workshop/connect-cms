{{--
 * 課題管理記事詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if ($tool->canPostView())

@include('common.errors_form_line')

<style>
.custom-file {
    overflow: hidden;
}
.custom-file-label {
    white-space: nowrap;
}
.report_table th {
    width: 25%;
    font-weight: normal;
}
</style>

@if (session('plugin_errors'))
    <div class="card mb-3 border-danger">
        <div class="card-body">
            <h3 class="mb-0">{!!session('plugin_errors')!!}</h3>
        </div>
    </div>
@endif

{{-- タイトル --}}
<h2>{!!$post->post_title!!}</h2>

{{-- 受講者選択：教員機能 --}}
@if ($tool->isTeacher())
    <h5><span class="badge badge-warning">評価中の受講者</span></h5>
    <div class="card mb-3 border-danger">
        <div class="card-body">
            <h3 class="mb-0">{{$tool->getStudent('受講者を選んでください。')}}</h3>
        </div>
    </div>

    <h5><span class="badge badge-secondary">受講者選択（教員用）</span></h5>
    <div class="form-group row">
        <label class="col-sm-3 text-sm-right">評価する受講者</label>
        <div class="col-sm-9">
            <form action="{{url('/')}}/redirect/plugin/learningtasks/switchUser/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST">
                {{ csrf_field() }}
                <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">
                <select class="form-control mb-1" name="student_id" onchange="javascript:submit(this.form);">
                    <option value="">評価する受講者を選んでください。</option>
                    @foreach ($tool->getStudents() as $student)
                    <option value="{{$student->id}}"@if ($tool->getStudentId() == $student->id) selected @endif>{{$student->name}}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
@endif

<article>
    {{-- 課題 --}}
    <h5 class="mb-1"><span class="badge badge-secondary">課題</span></h5>
    <div class="card">
        <div class="card-body pb-0">
            {!! $post->post_text !!}
        </div>
    </div>

    {{-- 課題ファイル --}}
    @if ($post_files)
        <h5 class="mb-1"><span class="badge badge-secondary mt-3">課題ファイル</span></h5>
        <div class="card">
            <div class="card-body pb-0">
                @foreach($post_files as $post_file)
                <p>
                    <a href="{{url('/')}}/file/{{$post_file->upload_id}}" target="_blank" rel="noopener">{{$post_file->client_original_name}}</a>
                </p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- レポート --}}
    @if ($tool->checkFunction('use_report') && $tool->canReportView($post->id))
    <h5 class="mb-1"><span class="badge badge-secondary mt-3">レポート</span></h5>
    <div class="card">
        <div class="card-body">

            <h5><span class="badge badge-secondary">履歴</span></h5>

            @if ($tool->checkFunction('use_report_status_collapse') && $tool->countReportStatuses($post->id) > 1)
                <button class="btn btn-primary btn-sm ml-4 mb-1" type="button" data-toggle="collapse" data-target=".multi-collapse" aria-expanded="false" aria-controls="{{$tool->getReportCollapseAriaControls()}}">履歴の開閉</button>
            @endif

            @if ($tool->hasReportStatuses($post->id))
                <ol class="mb-3">
                    @foreach($tool->getReportStatuses($post->id) as $report_status)
                    @if (!$loop->last)
                    <div class="collapse multi-collapse" id="multiCollapseReport{{$loop->iteration}}">
                    @endif
                        <li value="{{$loop->iteration}}">{{$report_status->getStstusName($tool->getStudentId())}}
                        <table class="table table-bordered table-sm report_table">
                        <tbody>
                            <tr>
                                <th>{{$report_status->getStstusPostTimeName()}}</th>
                                <td>{{$report_status->created_at}}</td>
                            </tr>
                            @if ($tool->isUseFunction($report_status->task_status, 'file'))
                            <tr>
                                <th>{{$report_status->getUploadFileName()}}</th>
                                @if (empty($report_status->upload_id))
                                <td>なし</td>
                                @else
                                <td><a href="{{url('/')}}/file/{{$report_status->upload_id}}" target="_blank">{{$report_status->upload->client_original_name}}</a></td>
                                @endif
                            </tr>
                            @endif
                            @if ($report_status->hasGrade())
                            <tr>
                                <th>評価</th>
                                <td><span class="text-danger font-weight-bold">{{$report_status->grade}}</span></td>
                            </tr>
                            @endif
                            @if ($tool->isUseFunction($report_status->task_status, 'comment'))
                            <tr>
                                <th>コメント</th>
                                <td>{!!nl2br(e($report_status->comment))!!}</td>
                            </tr>
                            @endif
                        </tbody>
                        </table>
                    @if (!$loop->last)
                    </div>
                    @endif
                    @endforeach
                </ol>
            @else
                <div class="card mb-3">
                    <div class="card-body p-3">
                        まだ履歴がありません。
                    </div>
                </div>
            @endif

            @if ($tool->isStudent())
                @if ($tool->canReportUpload($post->id))
                    @if ($tool->checkFunction(LearningtaskUseFunction::use_report_file) || $tool->checkFunction(LearningtaskUseFunction::use_report_comment))

                        <h5 class="mb-1"><span class="badge badge-secondary" for="status1">提出</span></h5>
                        <form action="{{url('/')}}/redirect/plugin/learningtasks/changeStatus1/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" name="form_status1" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">

                            @if ($tool->checkFunction(LearningtaskUseFunction::use_report_file))
                                <div class="form-group row mb-1">
                                    <label class="col-sm-3 text-sm-right">提出レポート <label class="badge badge-danger">必須</label></label>
                                    <div class="col-sm-9">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="report_file" name="upload_file">
                                            <label class="custom-file-label" for="report_file" data-browse="参照">レポートファイルを選んでください。</label>
                                        </div>
                                        @if ($errors && $errors->has('upload_file')) <div class="text-danger">{{$errors->first('upload_file')}}</div> @endif
                                    </div>
                                </div>
                            @endif

                            @if ($tool->checkFunction(LearningtaskUseFunction::use_report_comment))
                                <div class="form-group row mb-1">
                                    <label class="col-sm-3 text-sm-right">本文</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control mb-1" name="comment" rows="3"></textarea>
                                    </div>
                                </div>
                            @endif

                            @if ($tool->checkFunction(LearningtaskUseFunction::use_report_end))
                                <div class="form-group row mb-1">
                                    <label class="col-sm-3 text-sm-right">提出期限</label>
                                    <div class="col-sm-9">
                                        {{$tool->getFunctionBoth(LearningtaskUseFunction::report_end_at)->format('Y年n月j日 H時i分')}}
                                    </div>
                                </div>
                            @endif

                            <div class="form-group row mb-1">
                                <label class="col-sm-3 text-right"></label>
                                <div class="col-sm-9">
                                    <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('レポートを提出します。\nよろしいですか？');">
                                        <i class="fas fa-check"></i> <span class="hidden-xs">レポート提出</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                @else
                    <h5 class="mb-1"><span class="badge badge-secondary" for="status1">提出</span></h5>
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            {{$tool->getReportUploadMessage($post->id)}}
                        </div>
                    </div>
                @endif
            @endif

            @if ($tool->checkFunction('use_report_evaluate') && $tool->isTeacher())
                <h5 class="mb-1"><span class="badge badge-secondary" for="status2">評価・添削（教員用）</span></h5>
                @if ($tool->canReportEvaluate($post))
                    <form action="{{url('/')}}/redirect/plugin/learningtasks/changeStatus2/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status2" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">

                        @if ($tool->checkFunction('use_report_evaluate_file'))
                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">添削・参考ファイル</label>
                            <div class="col-sm-9">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="status2_file" name="upload_file">
                                    <label class="custom-file-label" for="status2_file" data-browse="参照">添削したファイルや参考ファイル（任意）</label>
                                </div>
                                @if ($errors && $errors->has('upload_file')) <div class="text-danger">{{$errors->first('upload_file')}}</div> @endif
                            </div>
                        </div>
                        @endif

                        @if ($tool->checkFunction('use_report_evaluate_comment'))
                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">コメント</label>
                            <div class="col-sm-9">
                                <textarea class="form-control mb-1" name="comment" rows="3"></textarea>
                            </div>
                        </div>
                        @endif

                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">評価 <label class="badge badge-danger">必須</label></label>
                            <div class="col-sm-9">
                                <select class="form-control mb-1" name="grade">
                                    <option value="">評価を選んでください。</option>
                                    <option value="A">Ａ</option>
                                    <option value="B">Ｂ</option>
                                    <option value="C">Ｃ</option>
                                    <option value="D">Ｄ</option>
                                </select>
                                @if ($errors && $errors->has('grade')) <div class="text-danger">{{$errors->first('grade')}}</div> @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 text-right"></label>
                            <div class="col-sm-9">
                                <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('評価を登録します。\nよろしいですか？');">
                                    <i class="fas fa-check"></i> <span class="hidden-xs">評価・添削確定</span>
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            評価を登録できるレポートがありません。
                        </div>
                    </div>
                @endif
            @endif

            @if ($tool->checkFunction('use_report_reference') && $tool->isTeacher())
                <h5 class="mb-1"><span class="badge badge-secondary" for="status3">受講生へのコメント（教員用）</span></h5>
                @if ($tool->canReportComment($post->id))
                    <form action="{{url('/')}}/redirect/plugin/learningtasks/changeStatus3/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status3" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">

                        @if ($tool->checkFunction('use_report_reference_file'))
                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">参考ファイル</label>
                            <div class="col-sm-9">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="status9_file" name="upload_file">
                                    <label class="custom-file-label" for="status9_file" data-browse="参照">参考ファイル（任意）</label>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if ($tool->checkFunction('use_report_reference_comment'))
                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">コメント</label>
                            <div class="col-sm-9">
                                <textarea class="form-control mb-1" name="comment" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 text-right"></label>
                            <div class="col-sm-9">
                                <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('コメントを登録します。\nよろしいですか？');">
                                    <i class="fas fa-check"></i> <span class="hidden-xs">コメントを登録する</span>
                                </button>
                            </div>
                        </div>
                        @endif
                    </form>
                @else
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            コメントできるレポートがありません。
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
    @endif

    {{-- 試験 --}}
    @if ($tool->checkFunction('use_examination') && $tool->canExaminationView($post))
    <h5 class="mb-1"><span class="badge badge-secondary mt-3">試験</span></h5>
    <div class="card">
        <div class="card-body">
            {{-- 試験に合格済み --}}
            @if ($tool->isPassExamination($post->id))
                <div class="card mb-3">
                    <div class="card-body">
                        試験に合格済みです。
                    </div>
                </div>
            {{-- 試験に申し込み済み --}}
            @elseif ($tool->getApplyingExamination($post->id))
                <h5><span class="badge badge-secondary">申し込み済の試験日</span></h5>
                <div class="card mb-3">
                    <div class="card-body">
                        試験日時は <span class="font-weight-bold">{{$tool->getApplyingExaminationDate($post->id)}}</span> です。
                    </div>
                </div>
            {{-- 試験に申し込みまだ --}}
            @else
                @if ($tool->isStudent())
                    <h5><span class="badge badge-secondary">試験申し込み</span></h5>
                    @if ($tool->canExamination($post))
                        <form action="{{url('/')}}/redirect/plugin/learningtasks/changeStatus4/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status4">
                            {{ csrf_field() }}
                            <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">
                            <div class="form-group row mb-3">
                                <label class="col-sm-3 text-sm-right">試験日</label>
                                <div class="col-sm-9">
                                    @if (count($examinations) > 0)
                                        @foreach ($examinations as $examination)
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="examination_{{$loop->index}}" name="examination_id" class="custom-control-input" value="{{$examination->id}}">
                                            <label class="custom-control-label" for="examination_{{$loop->index}}">{{$tool->getViewDate($examination)}}</label>
                                        </div>
                                        @endforeach

                                        <button type="submit" class="btn btn-primary btn-sm mt-2" onclick="javascript:return confirm('試験日を登録します。\nよろしいですか？');">
                                            <i class="fas fa-check"></i> <span class="hidden-xs">試験申し込み</span>
                                        </button>
                                    @else
                                        <div class="card border-danger mb-3">
                                            <div class="card-body">
                                                この科目には、もう申し込める試験がありません。
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="card border-danger mb-3">
                            <div class="card-body">
                                {!!$tool->reasonExamination($post)!!}
                            </div>
                        </div>
                    @endif
                @endif
            @endif

            {{-- 試験前 --}}
            @if ($tool->isApplyingExamination($post->id))
                <h5><span class="badge badge-secondary">試験問題・解答用ファイル</span></h5>
                <div class="card border-danger mb-3">
                    <div class="card-body">
                        試験日時は <span class="font-weight-bold">{{$tool->getApplyingExaminationDate($post->id)}}</span> です。<br />
                        開始時間以降にこのページを開くと、ここに試験ファイルのリンクが表示され、ダウンロードできるようになります。<br />
                        ※ 時間になっても、ダウンロードが表示されない場合は、画面を再読み込みしてみてください。
                    </div>
                </div>
            @endif

            {{-- 試験中 --}}
            @if ($tool->canViewExaminationFile($post->id))
                <h5><span class="badge badge-secondary">試験問題・解答用ファイル</span></h5>

                {{-- 試験用ファイル --}}
                @if (count($examination_files) > 0)
                    <div class="card mb-3">
                        <div class="card-body pb-0 pl-0">
                            <ul class="mb-3">
                            @foreach($examination_files as $examination_file)
                                <li><a href="{{url('/')}}/file/{{$examination_file->upload_id}}" target="_blank" rel="noopener">{{$examination_file->client_original_name}}</a></li>
                            @endforeach
                            </ul>
                        </div>
                        @if ($tool->isTeacher())
                            <h5><span class="badge badge-warning ml-4">※ 受講生は試験時間内のみ参照できます。</span></h5>
                        @endif
                    </div>
                @else
                    <div class="card mb-3">
                        <div class="card-body">
                            試験用のファイルはありません。
                        </div>
                    </div>
                @endif
            @endif

            <h5><span class="badge badge-secondary">履歴</span></h5>

            @if ($tool->checkFunction('use_examination_status_collapse') && $tool->countExaminationStatuses($post->id) > 1)
                <button class="btn btn-primary btn-sm ml-4 mb-1" type="button" data-toggle="collapse" data-target=".multi-collapse-examination" aria-expanded="false" aria-controls="{{$tool->getExaminationCollapseAriaControls()}}">履歴の開閉</button>
            @endif

            @if ($tool->hasExaminationStatuses($post->id))
                <ol class="mb-3">
                    @foreach($tool->getExaminationStatuses($post->id) as $examination_status)
                        @if (!$loop->last)
                        <div class="collapse multi-collapse-examination" id="multiCollapseExamination{{$loop->iteration}}">
                        @endif
                            <li value="{{$loop->iteration}}">{{$examination_status->getStstusName($tool->getStudentId())}}
                            <table class="table table-bordered table-sm report_table">
                            <tbody>
                                <tr>
                                    <th>{{$examination_status->getStstusPostTimeName()}}</th>
                                    <td>{{$examination_status->created_at}}</td>
                                </tr>
                                @if ($tool->isUseFunction($examination_status->task_status, 'file'))
                                <tr>
                                    <th>{{$examination_status->getUploadFileName()}}</th>
                                    @if (empty($examination_status->upload_id))
                                    <td>なし</td>
                                    @else
                                    <td><a href="{{url('/')}}/file/{{$examination_status->upload_id}}" target="_blank">{{$examination_status->upload->client_original_name}}</a></td>
                                    @endif
                                </tr>
                                @endif
                                @if ($examination_status->hasExamination())
                                <tr>
                                    <th>試験日時</th>
                                    <td>{{$tool->getViewDate($examination_status)}}</td>
                                </tr>
                                @endif
                                @if ($examination_status->hasGrade())
                                <tr>
                                    <th>評価</th>
                                    <td><span class="text-danger font-weight-bold">{{$examination_status->grade}}</span></td>
                                </tr>
                                @endif
                                @if ($tool->isUseFunction($examination_status->task_status, 'comment'))
                                <tr>
                                    <th>コメント</th>
                                    <td>{!!nl2br(e($examination_status->comment))!!}</td>
                                </tr>
                                @endif
                            </tbody>
                            </table>
                        @if (!$loop->last)
                        </div>
                        @endif
                    @endforeach
                </ol>
            @else
                <div class="card mb-3">
                    <div class="card-body p-3">
                        まだ履歴がありません。
                    </div>
                </div>
            @endif

            @if ($tool->isStudent())
                <h5 class="mb-1"><span class="badge badge-secondary" for="status5">解答</span></h5>
                @if ($tool->canExaminationUpload($post))
                    <form action="{{url('/')}}/redirect/plugin/learningtasks/changeStatus5/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status5" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">

                        @if ($tool->checkFunction('use_examination_file'))
                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">解答ファイル <label class="badge badge-danger">必須</label></label>
                            <div class="col-sm-9">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="status5_file" name="upload_file">
                                    <label class="custom-file-label" for="status5_file" data-browse="参照">試験の回答ファイルを選んでください。</label>
                                </div>
                                @if ($errors && $errors->has('upload_file')) <div class="text-danger">{{$errors->first('upload_file')}}</div> @endif
                            </div>
                        </div>
                        @endif

                        @if ($tool->checkFunction('use_examination_comment'))
                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">本文</label>
                            <div class="col-sm-9">
                                <textarea class="form-control mb-1" name="comment" rows="3"></textarea>
                            </div>
                        </div>
                        @endif

                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-right"></label>
                            <div class="col-sm-9">
                                <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('試験の解答を提出します。\nよろしいですか？');">
                                    <i class="fas fa-check"></i> <span class="hidden-xs">試験の解答提出</span>
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            現在、試験解答のアップロードはできません。
                        </div>
                    </div>
                @endif
                @if ($errors && $errors->has('examination_time'))
                    <div class="alert alert-danger" role="alert">
                        試験解答のアップロードはできませんでした。
                    </div>
                @endif
            @endif

            @if ($tool->checkFunction('use_examination_evaluate') && $tool->isTeacher())
                <h5 class="mb-1"><span class="badge badge-secondary" for="status6">評価・添削（教員用）</span></h5>

                @if ($tool->canExaminationEvaluate($post))
                    <form action="{{url('/')}}/redirect/plugin/learningtasks/changeStatus6/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status6" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">

                        @if ($tool->checkFunction('use_examination_evaluate_file'))
                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">添削・参考ファイル</label>
                            <div class="col-sm-9">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="status6_file" name="upload_file">
                                    <label class="custom-file-label" for="status6_file" data-browse="参照">添削したファイルや参考ファイル（任意）</label>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if ($tool->checkFunction('use_examination_evaluate_comment'))
                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">コメント</label>
                            <div class="col-sm-9">
                                <textarea class="form-control mb-1" name="comment" rows="3"></textarea>
                            </div>
                        </div>
                        @endif

                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">評価 <label class="badge badge-danger">必須</label></label>
                            <div class="col-sm-9">
                                <select class="form-control mb-1" name="grade">
                                    <option value="">評価を選んでください。</option>
                                    <option value="A">Ａ</option>
                                    <option value="B">Ｂ</option>
                                    <option value="C">Ｃ</option>
                                    <option value="D">Ｄ</option>
                                </select>
                                @if ($errors && $errors->has('grade')) <div class="text-danger">{{$errors->first('grade')}}</div> @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 text-right"></label>
                            <div class="col-sm-9">
                                <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('評価を登録します。\nよろしいですか？');">
                                    <i class="fas fa-check"></i> <span class="hidden-xs">評価・添削確定</span>
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            評価を登録できる試験の解答がありません。
                        </div>
                    </div>
                @endif
            @endif

            @if ($tool->checkFunction('use_examination_reference') && $tool->isTeacher())
                <h5 class="mb-1"><span class="badge badge-secondary" for="status7">受講生へのコメント（教員用）</span></h5>
                @if ($tool->canExaminationComment($post))
                    <form action="{{url('/')}}/redirect/plugin/learningtasks/changeStatus7/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status7" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">

                        @if ($tool->checkFunction('use_examination_reference_file'))
                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">参考ファイル</label>
                            <div class="col-sm-9">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="status6_file" name="upload_file">
                                    <label class="custom-file-label" for="status6_file" data-browse="参照">参考ファイル（任意）</label>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if ($tool->checkFunction('use_examination_reference_comment'))
                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">コメント</label>
                            <div class="col-sm-9">
                                <textarea class="form-control mb-1" name="comment" rows="3"></textarea>
                            </div>
                        </div>
                        @endif

                        <div class="form-group row">
                            <label class="col-sm-3 text-right"></label>
                            <div class="col-sm-9">
                                <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('コメントを登録します。\nよろしいですか？');">
                                    <i class="fas fa-check"></i> <span class="hidden-xs">コメントを登録する</span>
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            コメントできる試験の解答がありません。
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
    @endif

    {{-- 総合評価 --}}
    @if ($tool->checkFunction('use_evaluate'))
    <h5 class="mb-1"><span class="badge badge-secondary mt-3">総合評価</span></h5>
    <div class="card">
        <div class="card-body">

            @foreach($tool->getEvaluateStatuses($post->id) as $evaluate_status)
            <table class="table table-bordered table-sm report_table">
            <tbody>
                <tr>
                    <th>{{$evaluate_status->getStstusPostTimeName()}}</th>
                    <td>{{$evaluate_status->created_at}}</td>
                </tr>
                @if ($tool->isUseFunction($evaluate_status->task_status, 'file'))
                <tr>
                    <th>{{$evaluate_status->getUploadFileName()}}</th>
                    @if (empty($evaluate_status->upload_id))
                    <td>なし</td>
                    @else
                    <td><a href="{{url('/')}}/file/{{$evaluate_status->upload_id}}" target="_blank">{{$evaluate_status->upload->client_original_name}}</a></td>
                    @endif
                </tr>
                @endif
                @if ($evaluate_status->hasExamination())
                <tr>
                    <th>試験日時</th>
                    <td>{{$tool->getViewDate($evaluate_status)}}</td>
                </tr>
                @endif
                @if ($evaluate_status->hasGrade())
                <tr>
                    <th>評価</th>
                    <td><span class="text-danger font-weight-bold">{{$evaluate_status->grade}}</span></td>
                </tr>
                @endif
                @if ($tool->isUseFunction($evaluate_status->task_status, 'comment'))
                <tr>
                    <th>コメント</th>
                    <td>{!!nl2br(e($evaluate_status->comment))!!}</td>
                </tr>
                @endif
            </tbody>
            </table>
            @endforeach

            @if ($tool->checkFunction('use_evaluate') && $tool->isTeacher() && $tool->canEvaluateView($post))
                <h5 class="mb-1"><span class="badge badge-secondary" for="status8">評価</span></h5>

                @if ($tool->canEvaluate($post->id))
                    <form action="{{url('/')}}/redirect/plugin/learningtasks/changeStatus8/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status6" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">

                        @if ($tool->checkFunction('use_evaluate_file'))
                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">添削・参考ファイル</label>
                            <div class="col-sm-9">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="status6_file" name="upload_file">
                                    <label class="custom-file-label" for="status6_file" data-browse="参照">添削したファイルや参考ファイル（任意）</label>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if ($tool->checkFunction('use_evaluate_comment'))
                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">コメント</label>
                            <div class="col-sm-9">
                                <textarea class="form-control mb-1" name="comment" rows="3"></textarea>
                            </div>
                        </div>
                        @endif

                        <div class="form-group row mb-1">
                            <label class="col-sm-3 text-sm-right">評価 <label class="badge badge-danger">必須</label></label>
                            <div class="col-sm-9">
                                <select class="form-control mb-1" name="grade">
                                    <option value="">評価を選んでください。</option>
                                    <option value="A">Ａ</option>
                                    <option value="B">Ｂ</option>
                                    <option value="C">Ｃ</option>
                                    <option value="D">Ｄ</option>
                                </select>
                                @if ($errors && $errors->has('grade')) <div class="text-danger">{{$errors->first('grade')}}</div> @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 text-right"></label>
                            <div class="col-sm-9">
                                <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('評価を登録します。\nよろしいですか？');">
                                    <i class="fas fa-check"></i> <span class="hidden-xs">評価確定</span>
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            評価を登録できる履歴がありません。
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
    @endif

    {{-- 課題 --}}
    <h5 class="mb-1"><span class="badge badge-secondary mt-3">課題情報</span></h5>
    <div class="card">
        <div class="card-body">

            {{-- 投稿日時 --}}
            記載日：{{$post->posted_at->format('Y年n月j日 H時i分')}}

            {{-- 重要記事 --}}
            @if($post->important == 1)<span class="badge badge-danger">重要</span>@endif

            {{-- カテゴリ --}}
            @if($post->category)<span class="badge" style="color:{{$post->category_color}};background-color:{{$post->category_background_color}};">{{$post->category}}</span>@endif

            {{-- タグ --}}
            {{--
            @isset($post_tags)
                @foreach($post_tags as $tags)
                    <span class="badge badge-secondary">{{$tags->tags}}</span>
                @endforeach
            @endisset
            --}}
        </div>
    </div>

    {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
    <div class="row mt-3">
        <div class="col-12 text-right mb-1">
            @can('posts.update',[[$post, 'learningtasks', 'preview_off']])
                <a href="{{url('/')}}/plugin/learningtasks/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}">
                    <span class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span class="hidden-xs">編集</span></span>
                </a>
            @endcan
        </div>
    </div>

</article>


{{-- 一覧へ戻る --}}
<div class="row">
    <div class="col-12 text-center mt-3">
        {{--
        @if (isset($before_post))
        <a href="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$before_post->id}}#frame-{{$frame->id}}" class="mr-1">
            <span class="btn btn-info"><i class="fas fa-chevron-left"></i> <span class="hidden-xs">前へ</span></span>
        </a>
        @endif
        --}}
        <a href="{{url('/')}}{{$page->getLinkUrl()}}#frame-{{$frame->id}}">
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">一覧へ</span></span>
        </a>
        {{--
        @if (isset($after_post))
        <a href="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$after_post->id}}#frame-{{$frame->id}}" class="mr-1">
            <span class="btn btn-info"><i class="fas fa-chevron-right"></i> <span class="hidden-xs">次へ</span></span>
        </a>
        @endif
        --}}
    </div>
</div>
<script>
$('.custom-file-input').on('change',function(){
    $(this).next('.custom-file-label').html($(this)[0].files[0].name);
})
</script>
@else
    <div class="card mb-3 border-danger">
        <div class="card-body">
            <h3 class="mb-0">この課題に対する参照権限がありません。</h3>
        </div>
    </div>
@endif
@endsection
