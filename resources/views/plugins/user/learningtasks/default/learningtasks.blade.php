{{--
 * 課題管理画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- 新規登録 --}}
@can('posts.create',[[null, 'learningtasks', $buckets]])
    @if (isset($frame) && $frame->bucket_id)
        <div class="row">
            <p class="text-right col-12">
                {{-- 新規登録ボタン --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/learningtasks/create/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}'"><i class="far fa-edit"></i> 新規登録</button>
            </p>
        </div>
    @else
        <div class="card border-danger">
            <div class="card-body">
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用する課題管理を選択するか、作成してください。</p>
            </div>
        </div>
    @endif
@endcan

{{-- 要処理一覧：教員機能 --}}
{{--
<h5><span class="badge badge-secondary">教員用：処理待ち一覧</span></h5>
<table class="table table-bordered table-sm">
    <thead class="bg-light">
    <tr>
        <th scope="col" class="text-nowrap">受講者</th>
        <th scope="col" class="text-nowrap">必要処理</th>
        <th scope="col" class="text-nowrap">レポート</th>
        <th scope="col" class="text-nowrap">試験</th>
        <th scope="col" class="text-nowrap">受講者最終アクション</th>
    </tr>
    <tbody>
    <tr>
        <td>A20K0001 - 永原　篤</td>
        <td><a href="#">レポート評価</a></td>
        <td>-</td>
        <td>-</td>
        <td>2020-07-05 22:30:40 - レポート提出</td>
    </tr>
    <tr>
        <td>A20K0002 - 伊藤　博文</td>
        <td><a href="#">レポート再評価</a></td>
        <td>D</td>
        <td>-</td>
        <td>2020-07-06 09:10:20 - レポート再提出</td>
    </tr>
    <tr>
        <td>A20K0003 - 黑田　清隆</td>
        <td><a href="#">試験評価</a></td>
        <td>A</td>
        <td>-</td>
        <td>2020-07-10 09:10:20 - 解答提出</td>
    </tr>
    <tr>
        <td>B20L0012 - 大隈　重信</td>
        <td><a href="#">試験再評価</a></td>
        <td>B</td>
        <td>D</td>
        <td>2020-07-10 12:20:40 - 解答再提出</td>
    </tr>
    </tbody>
</table>
--}}

{{-- 課題管理表示 --}}
@if (isset($posts))  {{-- 課題があるか --}}
    @foreach($categories_and_posts as $category_id => $categories_and_post)  {{-- カテゴリのループ --}}
        <div class="accordion @if (!$loop->first) mt-3 @endif" id="accordionLearningTask{{$frame_id}}_{{$category_id}}">
            <span class="badge" style="color:{{$categories[$category_id]->category_color}};background-color:{{$categories[$category_id]->category_background_color}};">{{$categories[$category_id]->category}}</span>

            @if ($tool->isTeacher())
                <h5><span class="badge badge-secondary">教員用　必要な処理一覧</span></h5>
                @if ($teacher_tasks)
                    <table class="table table-bordered">
                        <thead>
                            <tr class="bg-light">
                                <th>科目名</th>
                                <th>受講者</th>
                                <th>必要な評価</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teacher_tasks as $teacher_task)
                                <tr>
                                    <td>{{$teacher_task->post_title}}</td>
                                    <td>
                                        <a href="{{url('/')}}/redirect/plugin/learningtasks/switchUserUrl/{{$page->id}}/{{$frame_id}}/{{$teacher_task->post_id}}?student_id={{$teacher_task->user_id}}#frame-{{$frame_id}}">
                                            {{$teacher_task->user_name}}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($teacher_task->task_status == 1)
                                            レポート
                                        @elseif ($teacher_task->task_status == 5)
                                            試験
                                        @elseif ($teacher_task->task_status == 8)
                                            {{-- 8 は本来、総合評価済の場合に付くが、ここでは判定用に getTeacherTasks() でセットしたもので判定する --}}
                                            総合
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif

            <h5><span class="badge badge-secondary">課題一覧</span></h5>
            <table class="table table-bordered">
                <thead class="bg-light">
                    <tr>
                        <th scope="col" class="text-nowrap">科目名</th>
                        @if (Auth::check() && $learningtask->useReport())
                            <th scope="col" class="text-nowrap">レポート</th>
                        @endif
                        @if (Auth::check() && $learningtask->useExamination())
                            <th scope="col" class="text-nowrap">試験日時</th>
                            <th scope="col" class="text-nowrap">試験評価</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories_and_post as $post)  {{-- 課題のループ --}}
                        <tr>
                            <th>
                                @can('posts.update',[[$post, 'learningtasks', 'preview_off']])
                                    <a href="{{url('/')}}/plugin/learningtasks/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">
                                        <i class="far fa-edit"></i>
                                    </a>
                                @endcan
                                {{-- タイトル --}}
                                <a href="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">{!!$post->getNobrPostTitle()!!}</a>
                            </th>
                            @if (Auth::check() && $learningtask->useReport())
                                <td>{{$tool->getReportStatus($post->id)}}</td>
                            @endif
                            @if (Auth::check() && $learningtask->useExamination())
                                <td>{{$tool->getApplyingExaminationDate($post->id)}}</td>
                                <td>{{$tool->getExaminationStatus($post->id)}}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
    {{-- ページング処理 --}}
    <div class="text-center">
        {{ $posts->fragment('frame-' . $frame_id)->links() }}
    </div>
@endif

@endsection
