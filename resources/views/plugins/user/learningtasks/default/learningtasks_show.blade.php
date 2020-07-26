{{--
 * 課題管理記事詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

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

{{-- タイトル --}}
<h2>{!!$post->post_title!!}</h2>

{{-- 受講者選択：教員機能 --}}
{{--
<h5><span class="badge badge-secondary">受講者選択（教員用）</span></h5>
<div class="form-group row">
    <label class="col-sm-3 control-label text-sm-right">評価する受講者</label>
    <div class="col-sm-9">
        <select class="form-control mb-1">
            <option>評価する受講者を選んでください。</option>
            <option>A20K0001 - 永原　篤</option>
            <option>A20K0002 - 伊藤　博文</option>
            <option>A20K0003 - 黑田　清隆</option>
            <option>A20K0004 - 山縣　有朋</option>
            <option>B20L0011 - 松方　正義</option>
            <option>B20L0012 - 大隈　重信</option>
            <option>B20L0013 - 桂　太郎</option>
        </select>
    </div>
</div>
--}}

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
    @if ($learningtask->useReport())
    <h5 class="mb-1"><span class="badge badge-secondary mt-3">レポート</span></h5>
    <div class="card">
        <div class="card-body">

            <h5><span class="badge badge-secondary">履歴</span></h5>
            <ol class="mb-3">
                @forelse($learningtask_user->getReportStatuses($post->id) as $report_status)
                <li>{{$report_status->getStstusName()}}
                <table class="table table-bordered table-sm report_table">
                <tbody>
                    <tr>
                        <th>{{$report_status->getStstusPostTimeName()}}</th>
                        <td>{{$report_status->created_at}}</td>
                    </tr>
                    <tr>
                        <th>{{$report_status->getUploadFileName()}}</th>
                        @if (empty($report_status->upload_id))
                        <td>なし</td>
                        @else
                        <td><a href="{{url('/')}}/file/{{$report_status->upload_id}}" target="_blank">{{$report_status->upload->client_original_name}}</a></td>
                        @endif
                    </tr>
                    @if ($report_status->hasGrade())
                    <tr>
                        <th>評価</th>
                        <td><span class="text-danger font-weight-bold">{{$report_status->grade}}</span></td>
                    </tr>
                    @endif
                    @if ($report_status->hasComment())
                    <tr>
                        <th>コメント</th>
                        <td>{!!nl2br(e($report_status->comment))!!}</td>
                    </tr>
                    @endif
                </tbody>
                </table>
                @empty
                    <div class="card">
                        <div class="card-body p-3">
                            まだ履歴がありません。
                        </div>
                    </div>
                @endforelse
            </ol>

            <form action="{{url('/')}}/plugin/learningtasks/changeStatus1/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status1" enctype="multipart/form-data">
                {{ csrf_field() }}
                <h5 class="mb-1"><span class="badge badge-secondary" for="status1">提出</span></h5>

                @if ($learningtask_user->canReportUpload($post->id))
                <div class="form-group row mb-1">

                    <label class="col-sm-3 control-label text-sm-right">提出レポート <label class="badge badge-danger">必須</label></label>
                    <div class="col-sm-9">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="report_file" name="upload_file">
                            <label class="custom-file-label" for="report_file" data-browse="参照">レポートファイルを選んでください。</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row mb-1">
                    <label class="col-sm-3 control-label text-right"></label>
                    <div class="col-sm-9">
                        <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('レポートを提出します。\nよろしいですか？');">
                            <i class="fas fa-check"></i> <span class="hidden-xs">レポート提出</span>
                        </button>
                    </div>
                </div>
                @else
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            {{$learningtask_user->getReportUploadMessage($post->id)}}
                        </div>
                    </div>
                @endif
            </form>

            <form action="{{url('/')}}/plugin/learningtasks/changeStatus2/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status2" enctype="multipart/form-data">
                {{ csrf_field() }}
                <h5 class="mb-1"><span class="badge badge-secondary" for="status2">評価・添削（教員用）</span></h5>
                <div class="form-group row mb-1">
                    <label class="col-sm-3 control-label text-sm-right">添削・参考ファイル</label>
                    <div class="col-sm-9">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="status2_file" name="upload_file">
                            <label class="custom-file-label" for="status2_file" data-browse="参照">添削したファイルや参考ファイル（任意）</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row mb-1">
                    <label class="col-sm-3 control-label text-sm-right">コメント</label>
                    <div class="col-sm-9">
                        <textarea class="form-control mb-1" name="comment" rows="3"></textarea>
                    </div>
                </div>

                <div class="form-group row mb-1">
                    <label class="col-sm-3 control-label text-sm-right">評価 <label class="badge badge-danger">必須</label></label>
                    <div class="col-sm-9">
                        <select class="form-control mb-1" name="grade">
                            <option>評価を選んでください。</option>
                            <option value="A">Ａ</option>
                            <option value="B">Ｂ</option>
                            <option value="C">Ｃ</option>
                            <option value="D">Ｄ</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 control-label text-right"></label>
                    <div class="col-sm-9">
                        <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('評価を登録します。\nよろしいですか？');">
                            <i class="fas fa-check"></i> <span class="hidden-xs">評価・添削確定</span>
                        </button>
                    </div>
                </div>
            </form>

            <form action="{{url('/')}}/plugin/learningtasks/changeStatus3/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status3" enctype="multipart/form-data">
                {{ csrf_field() }}
                <h5 class="mb-1"><span class="badge badge-secondary" for="status3">受講生へのコメント（教員用）</span></h5>
                <div class="form-group row mb-1">
                    <label class="col-sm-3 control-label text-sm-right">参考ファイル</label>
                    <div class="col-sm-9">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="status9_file" name="upload_file">
                            <label class="custom-file-label" for="status9_file" data-browse="参照">参考ファイル（任意）</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row mb-1">
                    <label class="col-sm-3 control-label text-sm-right">コメント</label>
                    <div class="col-sm-9">
                        <textarea class="form-control mb-1" name="comment" rows="3"></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 control-label text-right"></label>
                    <div class="col-sm-9">
                        <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('コメントを登録します。\nよろしいですか？');">
                            <i class="fas fa-check"></i> <span class="hidden-xs">コメントを登録する</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- 試験 --}}
    @if ($learningtask->useExamination())
    <h5 class="mb-1"><span class="badge badge-secondary mt-3">試験</span></h5>
    <div class="card">
        <div class="card-body">
            {{-- 試験に合格済み --}}
            @if ($learningtask_user->isPassExamination($post->id))
                <div class="card mb-3">
                    <div class="card-body">
                        試験に合格済みです。
                    </div>
                </div>
            {{-- 試験に申し込み済み --}}
            @elseif ($learningtask_user->getApplyingExamination($post->id))
                <h5><span class="badge badge-secondary">申し込み済の試験日</span></h5>
                <div class="card mb-3">
                    <div class="card-body">
                        試験日時は <span class="font-weight-bold">{{$learningtask_user->getApplyingExaminationDate($post->id)}}</span> です。
                    </div>
                </div>
            {{-- 試験に申し込みまだ --}}
            @else
                <h5><span class="badge badge-secondary">試験申し込み</span></h5>
                @if ($learningtask_user->canExamination($post->id))
                    <form action="{{url('/')}}/plugin/learningtasks/changeStatus4/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status4">
                        {{ csrf_field() }}
                        <div class="form-group row mb-3">
                            <label class="col-sm-3 control-label text-sm-right">試験日</label>
                            <div class="col-sm-9">
                                @if (count($examinations) > 0)
                                    @foreach ($examinations as $examination)
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="examination_{{$loop->index}}" name="examination_id" class="custom-control-input" value="{{$examination->id}}">
                                        <label class="custom-control-label" for="examination_{{$loop->index}}">{{$learningtask_user->getViewDate($examination)}}</label>
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
                            試験に申し込む条件が不足しています。<br />
                            {{$learningtask_user->reasonExamination($post->id)}}
                        </div>
                    </div>
                @endif
            @endif

            {{-- 試験前 --}}
            @if ($learningtask_user->isApplyingExamination($post->id))
                <h5><span class="badge badge-secondary">試験問題・解答用ファイル</span></h5>
                <div class="card border-danger mb-3">
                    <div class="card-body">
                        試験日時は <span class="font-weight-bold">{{$learningtask_user->getApplyingExaminationDate($post->id)}}</span> です。<br />
                        開始時間以降にこのページを開くと、ここに試験ファイルのリンクが表示され、ダウンロードできるようになります。<br />
                        ※ 時間になっても、ダウンロードが表示されない場合は、画面を再読み込みしてみてください。
                    </div>
                </div>
            @endif

            {{-- 試験中 --}}
            @if ($learningtask_user->isNowExamination($post->id))
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
            <ol class="mb-3">

                @forelse($learningtask_user->getExaminationStatuses($post->id) as $examination_status)
                <li>{{$examination_status->getStstusName()}}
                <table class="table table-bordered table-sm report_table">
                <tbody>
                    <tr>
                        <th>{{$examination_status->getStstusPostTimeName()}}</th>
                        <td>{{$examination_status->created_at}}</td>
                    </tr>
                    @if ($examination_status->hasFile())
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
                        <td>{{$learningtask_user->getViewDate($examination_status)}}</td>
                    </tr>
                    @endif
                    @if ($examination_status->hasGrade())
                    <tr>
                        <th>評価</th>
                        <td><span class="text-danger font-weight-bold">{{$examination_status->grade}}</span></td>
                    </tr>
                    @endif
                    @if ($examination_status->hasComment())
                    <tr>
                        <th>コメント</th>
                        <td>{!!nl2br(e($examination_status->comment))!!}</td>
                    </tr>
                    @endif
                </tbody>
                </table>
                @empty
                    <div class="card">
                        <div class="card-body p-3">
                            まだ履歴がありません。
                        </div>
                    </div>
                @endforelse
            </ol>

            <form action="{{url('/')}}/plugin/learningtasks/changeStatus5/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status5" enctype="multipart/form-data">
                {{ csrf_field() }}
                <h5 class="mb-1"><span class="badge badge-secondary" for="status5">解答</span></h5>
                <div class="form-group row mb-1">

                    <label class="col-sm-3 control-label text-sm-right">解答ファイル <label class="badge badge-danger">必須</label></label>
                    <div class="col-sm-9">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="status5_file" name="upload_file">
                            <label class="custom-file-label" for="status5_file" data-browse="参照">試験の回答ファイルを選んでください。</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row mb-1">
                    <label class="col-sm-3 control-label text-right"></label>
                    <div class="col-sm-9">
                        <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('試験の解答を提出します。\nよろしいですか？');">
                            <i class="fas fa-check"></i> <span class="hidden-xs">試験の解答提出</span>
                        </button>
                    </div>
                </div>
            </form>

            <form action="{{url('/')}}/plugin/learningtasks/changeStatus6/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status6" enctype="multipart/form-data">
                {{ csrf_field() }}
                <h5 class="mb-1"><span class="badge badge-secondary" for="status6">評価・添削（教員用）</span></h5>
                <div class="form-group row mb-1">
                    <label class="col-sm-3 control-label text-sm-right">添削・参考ファイル</label>
                    <div class="col-sm-9">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="status6_file" name="upload_file">
                            <label class="custom-file-label" for="status6_file" data-browse="参照">添削したファイルや参考ファイル（任意）</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row mb-1">
                    <label class="col-sm-3 control-label text-sm-right">コメント</label>
                    <div class="col-sm-9">
                        <textarea class="form-control mb-1" name="comment" rows="3"></textarea>
                    </div>
                </div>

                <div class="form-group row mb-1">
                    <label class="col-sm-3 control-label text-sm-right">評価 <label class="badge badge-danger">必須</label></label>
                    <div class="col-sm-9">
                        <select class="form-control mb-1" name="grade">
                            <option>評価を選んでください。</option>
                            <option value="A">Ａ</option>
                            <option value="B">Ｂ</option>
                            <option value="C">Ｃ</option>
                            <option value="D">Ｄ</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 control-label text-right"></label>
                    <div class="col-sm-9">
                        <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('評価を登録します。\nよろしいですか？');">
                            <i class="fas fa-check"></i> <span class="hidden-xs">評価・添削確定</span>
                        </button>
                    </div>
                </div>
            </form>

            <form action="{{url('/')}}/plugin/learningtasks/changeStatus7/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}" method="POST" class="" name="form_status7" enctype="multipart/form-data">
                {{ csrf_field() }}
                <h5 class="mb-1"><span class="badge badge-secondary" for="status7">受講生へのコメント（教員用）</span></h5>
                <div class="form-group row mb-1">
                    <label class="col-sm-3 control-label text-sm-right">参考ファイル</label>
                    <div class="col-sm-9">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="status6_file" name="upload_file">
                            <label class="custom-file-label" for="status6_file" data-browse="参照">参考ファイル（任意）</label>
                        </div>
                    </div>
                </div>

                <div class="form-group row mb-1">
                    <label class="col-sm-3 control-label text-sm-right">コメント</label>
                    <div class="col-sm-9">
                        <textarea class="form-control mb-1" name="comment" rows="3"></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 control-label text-right"></label>
                    <div class="col-sm-9">
                        <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('コメントを登録します。\nよろしいですか？');">
                            <i class="fas fa-check"></i> <span class="hidden-xs">コメントを登録する</span>
                        </button>
                    </div>
                </div>
            </form>
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
        @if ($post->status == 2)
            @can('preview',[[null, 'learningtasks', 'preview_off']])
                <span class="badge badge-warning align-bottom">承認待ち</span>
            @endcan
            @can('posts.approval',[[$post, 'learningtasks', 'preview_off']])
                <form action="{{url('/')}}/plugin/learningtasks/approval/{{$page->id}}/{{$frame_id}}/{{$post->id}}" method="post" name="form_approval" class="d-inline">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                        <i class="fas fa-check"></i> <span class="hidden-xs">承認</span>
                    </button>
                </form>
            @endcan
        @endif
        @can('posts.update',[[$post, 'learningtasks', 'preview_off']])
            @if ($post->status == 1)
                @can('preview',[[$post, 'learningtasks', 'preview_off']])
                    <span class="badge badge-warning align-bottom">一時保存</span>
                @endcan
            @endif
            <a href="{{url('/')}}/plugin/learningtasks/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}">
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
        <a href="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$before_post->id}}" class="mr-1">
            <span class="btn btn-info"><i class="fas fa-chevron-left"></i> <span class="hidden-xs">前へ</span></span>
        </a>
        @endif
        --}}
        <a href="{{url('/')}}{{$page->getLinkUrl()}}">
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">一覧へ</span></span>
        </a>
        {{--
        @if (isset($after_post))
        <a href="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$after_post->id}}" class="mr-1">
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
@endsection
