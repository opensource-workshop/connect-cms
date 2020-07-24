{{--
 * 課題管理記事登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 --}}
@extends('core.cms_frame_base')

<div class="frame-setting-menu">
    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <span class="d-md-none">編集メニュー</span>
        <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#collapsingNavbarLg" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="collapsingNavbarLg" style="">
            <ul class="navbar-nav">
                <li role="presentation" class="nav-item">
                    <a href="{{url('/')}}/plugin/learningtasks/edit/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}#frame-{{$frame_id}}" class="nav-link">基本項目</a>
                </li>
                <li role="presentation" class="nav-item">
                    <span class="nav-link"><span class="active">試験関係</span></span>
                </li>
            </ul>
        </div>
    </nav>
</div>

@section("plugin_contents_$frame->id")

{{-- 試験設定フォーム --}}
@if (empty($learningtasks_posts->id))
    <div class="card border-danger">
        <div class="card-body">
            <p class="text-center cc_margin_bottom_0">課題データを作成してから、試験の設定をしてください。</p>
        </div>
    </div>
@else
<form action="{{url('/')}}/plugin/learningtasks/saveExaminations/{{$page->id}}/{{$frame_id}}/{{$learningtasks_posts->id}}" method="POST" class="" name="form_learningtasks_posts" enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="hidden" name="learningtasks_id" value="{{$learningtasks_frame->learningtasks_id}}">

    <div class="form-group row">
        <label class="col-md-2 control-label">タイトル</label>
        <div class="col-md-10">{!!$learningtasks_posts->post_title!!}</div>
    </div>

    <div class="form-group row">
        <label class="col-sm-2 control-label">試験日時一覧</label>
        <div class="col-sm-10">
            <div class="card p-2">
            @if ($examinations->count() == 0)
                ※ 設定されている試験がありません。
            @else
            @foreach ($examinations as $examination)
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="del_examinations[{{$examination->id}}]" value="1" class="custom-control-input" id="del_examinations[{{$examination->id}}]" @if(old("del_examination.$examination->id")) checked=checked @endif>
                    <label class="custom-control-label" for="del_examinations[{{$examination->id}}]">
                        {{$examination->start_at->format('Y-m-d H:i')}} ～ {{$examination->end_at->format('Y-m-d H:i')}}
                    </label>
                </div>
            @endforeach
            @endif
            </div>
            <small class="text-muted">削除する場合はチェックします。</small>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">試験日時追加</label>
        <div class="col-md-4">
            <div class="input-group date" id="start_at" data-target-input="nearest">
                <input type="text" name="start_at" value="{{old('start_at')}}" class="form-control datetimepicker-input" data-target="#start_at" placeholder="開始日時">
                <div class="input-group-append" data-target="#start_at" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
            </div>
            @if ($errors && $errors->has('start_at')) <div class="text-danger">{{$errors->first('start_at')}}</div> @endif
        </div>
        <div class="col-md-4">
            <div class="input-group date" id="end_at" data-target-input="nearest">
                <input type="text" name="end_at" value="{{old('end_at')}}" class="form-control datetimepicker-input" data-target="#end_at" placeholder="終了日時">
                <div class="input-group-append" data-target="#end_at" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
            </div>
            @if ($errors && $errors->has('end_at')) <div class="text-danger">{{$errors->first('end_at')}}</div> @endif
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
        });
    </script>

    <div class="form-group row">
        <label class="col-sm-2" for="add_task_file">試験問題など</label>
        <div class="col-sm-10">
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="add_task_file" name="add_task_file">
                <label class="custom-file-label" for="add_task_file" data-browse="参照">試験問題など</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-2 control-label">ファイル一覧</label>
        <div class="col-sm-10">
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
