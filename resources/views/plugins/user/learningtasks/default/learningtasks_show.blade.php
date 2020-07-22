{{--
 * 課題管理記事詳細画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
{{-- タイトル --}}
<h2>{!!$post->post_title!!}</h2>

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
                    <a href="{{url('/')}}/file/{{$post_file->task_file_uploads_id}}" target="_blank" rel="noopener">{{$post_file->client_original_name}}</a>
                </p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- レポート --}}
    <h5 class="mb-1"><span class="badge badge-secondary mt-3">レポート</span></h5>
    <div class="card">
        <div class="card-body">

            <h5><span class="badge badge-secondary">履歴</span></h5>
            <ul class="mb-3">
                <li>2020-07-01<br />
                    提出 - <a href="#">発達臨床実践特論（R2認定通信）レポート_永原_0620.docx</a>
                <li>2020-07-02<br />
                    <span class="text-danger font-weight-bold">評価：D</span><br />
                    添削ファイル - <a href="#">発達臨床実践特論（R2認定通信）レポート_教員添削_0702.docx</a><br />
                    コメント<br />
                    <div class="card">
                        <div class="card-body py-2">
                            レポートに対するコメントです。
                        </div>
                    </div>

                <li>2020-07-05<br />
                    再提出 - <a href="#">発達臨床実践特論（R2認定通信）レポート_永原_0702.docx</a><br />
                    <span class="text-danger font-weight-bold">評価：A</span><br />
                    コメント<br />
                    <div class="card">
                        <div class="card-body py-2">
                            よくできました。
                        </div>
                    </div>
            </ul>

            <form action="" method="POST" class="" name="form_report_posts" enctype="multipart/form-data">
                <div class="form-group">
                    <h5 class="mb-1"><span class="badge badge-secondary" for="report">提出</span></h5>
                    <input type="file" name="report" class="form-control-file mb-2" id="report">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-check"></i> <span class="hidden-xs">レポート提出</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- 試験 --}}
    <h5 class="mb-1"><span class="badge badge-secondary mt-3">試験</span></h5>
    <div class="card">
        <div class="card-body">

            <h5><span class="badge badge-secondary">試験申し込み</span></h5>
                <div class="form-group ml-3">
                    <div class="custom-control custom-radio">
                        <input type="radio" id="customRadio1" name="customRadio" class="custom-control-input">
                        <label class="custom-control-label" for="customRadio1">2020年7月10日（金）10:00 - 11:00</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="customRadio2" name="customRadio" class="custom-control-input">
                        <label class="custom-control-label" for="customRadio2">2020年7月11日（土）10:00 - 11:00</label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm mt-2">
                        <i class="fas fa-check"></i> <span class="hidden-xs">試験申し込み</span>
                    </button>
                </div>

            <h5><span class="badge badge-secondary">試験問題・解答用ファイル</span></h5>
            <ul class="mb-3">
                <li><a href="#">発達臨床実践特論（R2認定通信）試験問題.pdf</a>
                <li><a href="#">発達臨床実践特論（R2認定通信）試験解答用ファイル.docx</a>
            </ul>

            <h5><span class="badge badge-secondary">履歴</span></h5>
            <ul class="mb-3">
                <li>2020-07-10<br />
                    提出 - <a href="#">発達臨床実践特論（R2認定通信）試験_永原_0620.docx</a>
                <li>2020-07-11<br />
                    <span class="text-danger font-weight-bold">評価：D</span><br />
                    添削ファイル - <a href="#">発達臨床実践特論（R2認定通信）試験_教員添削_0702.docx</a><br />
                    コメント<br />
                    <div class="card">
                        <div class="card-body py-2">
                            試験に対するコメントです。
                        </div>
                    </div>

                <li>2020-07-12<br />
                    再提出 - <a href="#">発達臨床実践特論（R2認定通信）試験_永原_0712.docx</a><br />
                    <span class="text-danger font-weight-bold">評価：A</span><br />
                    コメント<br />
                    <div class="card">
                        <div class="card-body py-2">
                            よくできました。
                        </div>
                    </div>
            </ul>

            <form action="" method="POST" class="" name="form_report_posts" enctype="multipart/form-data">
                <div class="form-group">
                    <h5 class="mb-1"><span class="badge badge-secondary" for="report">提出</span></h5>
                    <input type="file" name="report" class="form-control-file mb-2" id="report">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-check"></i> <span class="hidden-xs">レポート提出</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

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
            @isset($post_tags)
                @foreach($post_tags as $tags)
                    <span class="badge badge-secondary">{{$tags->tags}}</span>
                @endforeach
            @endisset
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
@endsection
