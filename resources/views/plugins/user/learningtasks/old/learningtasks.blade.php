{{--
 * 課題管理画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<style>
.option{
    width: 250px;
}
.color > a{
    display: inline-block;
    width: 20px;
    height: 20px;
}
.black{
    background-color: #000000;
}
.red{
    background-color: #ff0000;
}
.blue{
    background-color: #0000ff;
}
</style>

<script>
    // 変数宣言
    const cnvWidth = 500;
    const cnvHeight = 200;
    var cnvColor = "255, 0, 0, 1";  // 線の色
    var cnvBold = 5;  // 線の太さ
    var clickFlg = 0;  // クリック中の判定 1:クリック開始 2:クリック中
    var bgColor = "rgb(255,255,255)";
    var cnvs = [];
    var ctx = [];

    // 描画処理
    function canvas_init(id) {
        // canvas
        var canvas_id = "canvas"+id;
        var clear_id = "clear"+id;

        cnvs[id] = document.getElementById(canvas_id);
        ctx[id] = cnvs[id].getContext('2d');

        // canvasの背景色を設定(指定がない場合にjpeg保存すると背景が黒になる)
        setBgColor(id);

        // canvas上でのイベント
        $("#canvas"+id).mousedown(function(){
          clickFlg = 1; // マウス押下開始
        }).mouseup(function(){
          clickFlg = 0; // マウス押下終了
        }).mousemove(function(e){
          // マウス移動処理
          if(!clickFlg) return false;
          draw(id, e.offsetX, e.offsetY);
        });

        // 色の変更
        $(".color"+id+" a").click(function(){
          cnvColor = $(this).data("color");
          return false;
        });

        // 線の太さ変更
        $(".bold"+id+" a").click(function(){
          cnvBold = $(this).data("bold");
          return false;
        });

        // 描画クリア
        $("#clear"+id).click(function(){
            ctx[id].clearRect(0,0,cnvs[id].width,cnvs[id].height);
            setBgColor(id);
        });

        // canvasを画像で保存
        $("#download"+id).click(function(){
            var base64 = cnvs[id].toDataURL("image/jpeg");
            // ダウンロード時は配列のCanvasコンテキストではなく、画面上のオブジェクトを指定する必要がある。
            document.getElementById("download"+id).href = base64;
        });
    }

    // 描画処理
    function draw(id, x, y) {
        ctx[id].lineWidth = cnvBold;
        ctx[id].strokeStyle = 'rgba('+cnvColor+')';
        // 初回処理の判定
        if (clickFlg == "1") {
            clickFlg = "2";
            ctx[id].beginPath();
            ctx[id].lineCap = "round";  //　線を角丸にする
            ctx[id].moveTo(x, y);
        } else {
            ctx[id].lineTo(x, y);
        }
        ctx[id].stroke();
    };

    // 背景色の設定
    function setBgColor(id){
        // canvasの背景色を設定(指定がない場合にjpeg保存すると背景が黒になる)
        ctx[id].fillStyle = bgColor;
        ctx[id].fillRect(0,0,cnvs[id].width,cnvs[id].height);
    }

    // 終了
    function submitCompletion(id){
        if (!confirm('修了しましたか？')) {
            return false;
        }

        var canvas = document.getElementById("canvas"+id) ;
        var image_data = canvas.toDataURL("image/png");
        image_data = image_data.replace(/^.*,/, '');
        $('#handwriting'+id).val(image_data);
        $('#form_status'+id).submit();
    }

    // 取り消し
    function submitRevoke(id){
        if (!confirm('修了を取り消しますか？')) {
            return false;
        }
        $('#form_status'+id).submit();
    }
</script>

{{-- RSS --}}
@if (isset($learningtasks_frame->rss) && $learningtasks_frame->rss == 1)
<div class="row">
    <p class="text-left col-6">
        <a href="{{url('/')}}/redirect/plugin/learningtasks/rss/{{$page->id}}/{{$frame_id}}/"><span class="badge badge-info">RSS2.0</span></a>
    </p>
</div>
@endif

{{-- 新規登録 --}}
@can('posts.create',[[null, 'learningtasks', $buckets]])
    @if (isset($frame) && $frame->bucket_id)
        <div class="row">
            <p class="text-right col-12">
                {{-- 新規登録ボタン --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/learningtasks/create/{{$page->id}}/{{$frame_id}}'"><i class="far fa-edit"></i> 新規登録</button>
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

{{-- 課題管理表示 --}}
@if (isset($learningtasks_posts))
    @foreach($categories_and_posts as $category_id => $categories_and_post)
    <div class="accordion @if (!$loop->first) mt-3 @endif" id="accordionLearningTask{{$frame_id}}_{{$category_id}}">
        <span class="badge" style="color:{{$categories[$category_id]->category_color}};background-color:{{$categories[$category_id]->category_background_color}};">{{$categories[$category_id]->category}}</span>
    @foreach($categories_and_post as $post)
        <div class="card">
            <button class="btn btn-link p-0 text-left" type="button" data-toggle="collapse" data-target="#collapseLearningTask{{$post->id}}" aria-expanded="true" aria-controls="collapseLearningTask{{$post->id}}">
                <div class="card-header learningtasks-list-title row" id="headingLearningTask{{$post->id}}">
                    @auth
                    <div class="col-sm-1">
                    @if($post->user_task_status)
                        <span class="badge badge-pill badge-primary">修了</span>
                    @endif
                    </div>
                    @endauth

                    @auth
                    <div class="col-sm-11">
                    @else
                    <div class="col-sm-12">
                    @endauth
                    {{-- タイトル --}}
                    {!!$post->getNobrPostTitle()!!}
                    </div>
               </div>
            </button>

            <div id="collapseLearningTask{{$post->id}}" class="collapse" aria-labelledby="headingLearningTask{{$post->id}}" data-parent="#accordionLearningTask{{$frame_id}}_{{$category_id}}">
                <div class="card-body">

                {{-- 記事本文 --}}
                <article class="">
                    {{-- 記事本文 --}}
                    {!!$post->post_text!!}

                    {{-- 課題ファイル --}}
                    @if ($post->task_files)
                        @foreach($post->task_files as $task_file)
                        <p>
                            <a href="{{url('/')}}/file/{{$task_file->task_file_uploads_id}}" target="_blank" rel="noopener">{{$task_file->client_original_name}}</a>
                            <span class="ml-2"><span><i class="fas fa-arrow-circle-down"></i> {{$task_file->download_count}}
                        </p>
                        @endforeach
                    @endif

                    {{-- 重要記事 --}}
                    @if($post->important == 1)
                        <span class="badge badge-danger">重要</span>
                    @endif

                    {{-- タグ --}}
                    @isset($post->tags)
                        @foreach($post->tags as $tag)
                            <span class="badge badge-secondary">{{$tag}}</span>
                        @endforeach
                    @endisset
                </article>

                {{-- 修了チェック --}}
                @auth
                <div class="card p-3 m-3">
                    <form action="{{url('/')}}/plugin/learningtasks/changeStatus/{{$page->id}}/{{$frame_id}}/{{$post->contents_id}}" method="post" name="form_status{{$frame_id}}_{{$post->id}}" id="form_status{{$frame_id}}_{{$post->id}}" class="d-inline">
                        {{ csrf_field() }}
                        <input type="hidden" name="handwriting" id="handwriting{{$frame_id}}_{{$post->id}}" value="">
                        @if ($post->user_task_status == 0)
                            <p>修了したら下の「修了」ボタンをクリックしてください。</p>

                            {{-- キャンバス --}}
                            <canvas id="canvas{{$frame_id}}_{{$post->id}}" width="500" height="200" style="border: solid 1px #000;box-sizing: border-box;"></canvas>
                            <div class="option">
                                <div class="color color{{$frame_id}}_{{$post->id}}">
                                    色：
                                    <a href="#" class="black" data-color="0, 0, 0, 1"></a>
                                    <a href="#" class="red" data-color="255, 0, 0, 1"></a>
                                    <a href="#" class="blue" data-color="0, 0, 255, 1"></a>
                                </div>
                                <div class="bold bold{{$frame_id}}_{{$post->id}}">
                                    太さ：
                                    <a href="#" class="small" data-bold="1">小</a>
                                    <a href="#" class="middle" data-bold="5">中</a>
                                    <a href="#" class="large" data-bold="10">大</a>
                                </div>
                            </div>
                            <input type="button" value="clear" id="clear{{$frame_id}}_{{$post->id}}">
                            <a id="download{{$frame_id}}_{{$post->id}}" href="#" download="canvas.jpg">ダウンロード</a>
                            <script>canvas_init("{{$frame_id}}_{{$post->id}}");</script>


                            <input type="hidden" name="task_status" value="1">
                            <button type="button" class="btn btn-primary btn-sm" onclick="submitCompletion('{{$frame_id}}_{{$post->id}}');">
                                <i class="fas fa-check"></i> 修了
                            </button>
                        @else
                            <span class="badge badge-primary">回答内容</span><br />
                            @if ($post->canvas_answer_file_id)
                                <img src="{{url('/')}}/file/{{$post->canvas_answer_file_id}}">
                            @endif
                            <p>修了を取り消す場合は下の「修了取り消し」ボタンをクリックしてください。</p>

                            <input type="hidden" name="task_status" value="0">
                            <button type="button" class="btn btn-primary btn-sm" onclick="submitRevoke('{{$frame_id}}_{{$post->id}}');">
                                <i class="fas fa-check"></i> 修了取り消し
                            </button>
                        @endif
                    </form>
                </div>
                @endauth

                {{-- 投稿日時 --}}
                公開日時：{{$post->posted_at->format('Y年n月j日 H時i分')}}

                {{-- 詳細画面 --}}
                <a href="{{url('/')}}/plugin/learningtasks/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}"><i class="fas fa-expand-alt"></i></a>

                {{-- post データは以下のように2重配列で渡す（Laravelが配列の0番目のみ使用するので） --}}
                <div class="row">
                    <div class="col-12 text-right mb-1">
                    {{-- delete: 承認機能なし
                    @if ($post->status == 2)
                        @can('role_update_or_approval',[[$post, 'learningtasks', $buckets]])
                            <span class="badge badge-warning align-bottom">承認待ち</span>
                        @endcan
                        @can('posts.approval',[[$post, 'learningtasks', $buckets]])
                            <form action="{{url('/')}}/plugin/learningtasks/approval/{{$page->id}}/{{$frame_id}}/{{$post->id}}" method="post" name="form_approval" class="d-inline">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                                    <i class="fas fa-check"></i> <span class="hidden-xs">承認</span>
                                </button>
                            </form>
                        @endcan
                    @endif
                    --}}
                    @can('posts.update',[[$post, 'learningtasks', $buckets]])
                        {{-- delete: 一時保存機能なし
                        @if ($post->status == 1)
                            <span class="badge badge-warning align-bottom">一時保存</span>
                        @endif
                        --}}
                        <a href="{{url('/')}}/plugin/learningtasks/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}">
                            <span class="btn btn-success btn-sm"><i class="far fa-edit"></i> <span class="hidden-xs">編集</span></span>
                        </a>
                    @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    </div>
    @endforeach

    {{-- ページング処理 --}}
    @include('plugins.common.user_paginate', ['posts' => $posts, 'frame' => $frame, 'aria_label_name' => $learningtask->learningtasks_name, 'class' => 'mt-3'])
@endif
@endsection
