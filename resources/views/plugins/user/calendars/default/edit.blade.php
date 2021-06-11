{{--
 * カレンダー記事登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category カレンダープラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- 共通エラーメッセージ 呼び出し --}}
@include('common.errors_form_line')

{{-- WYSIWYG 呼び出し --}}
@include('plugins.common.wysiwyg')

{{-- 一時保存ボタンのアクション --}}
<script type="text/javascript">
    function save_action() {
        form_calendars_posts{{$frame_id}}.status.value = "1";
        form_calendars_posts{{$frame_id}}.submit();
    }
</script>

{{-- 全日予定をチェック --}}
<script type="text/javascript">
    function check_allday() {
        if (form_calendars_posts{{$frame_id}}.allday_flag.checked) {
            {{-- 全日予定にする --}}
            form_calendars_posts{{$frame_id}}.start_time.value = '';
            form_calendars_posts{{$frame_id}}.start_time.disabled = true;
            form_calendars_posts{{$frame_id}}.end_time.value = '';
            form_calendars_posts{{$frame_id}}.end_time.disabled = true;
        } else {
            form_calendars_posts{{$frame_id}}.start_time.disabled = false;
            form_calendars_posts{{$frame_id}}.end_time.disabled = false;
        }
    }
</script>

@if ($errors && $errors->has('reply_role_error'))
    <div class="alert alert-danger">
        <span class="font-weight-bold">{{$errors->first('reply_role_error')}}</span>
    </div>
@endif
{{-- 投稿用フォーム --}}
@if (empty($post->id))
    <form action="{{url('/')}}/redirect/plugin/calendars/save/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" class="" name="form_calendars_posts{{$frame_id}}">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/calendars/edit/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
@else
    <form action="{{url('/')}}/redirect/plugin/calendars/save/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" method="POST" class="" name="form_calendars_posts{{$frame_id}}">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/calendars/edit/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}">
@endif
    {{ csrf_field() }}
    <input type="hidden" name="status" value="0">
    @if (isset($parent_post))
        <input type="hidden" name="parent_id" value="{{$parent_post->id}}">
    @endif
    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right">状態</label>
        <div class="col-md-10">
            @if ($post->status === null)
                <span class="badge badge-info align-bottom">新規</span>
            @elseif ($post->status == 0)
                <span class="badge badge-info align-bottom">公開中</span>
            @elseif ($post->status == 1)
                <span class="badge badge-warning align-bottom">一時保存</span>
            @elseif ($post->status == 2)
                <span class="badge badge-warning align-bottom">承認待ち</span>
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right"><label class="badge badge-danger">必須</label> タイトル</label>
        <div class="col-md-10">
            <input type="text" name="title" value="{{old('title', $post->title)}}" class="form-control">
            @if ($errors && $errors->has('title')) <div class="text-danger">{{$errors->first('title')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right">全日予定</label>
        <div class="col-md-10">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="allday_flag" value="1" class="custom-control-input" id="allday_flag{{$frame_id}}" onclick="check_allday();"@if(old('allday_flag', $post->allday_flag)) checked=checked @endif>
                <label class="custom-control-label" for="allday_flag{{$frame_id}}">チェックすると、全日予定として扱います。</label>
            </div>
        </div>
    </div>

    <div class="form-group row mb-0">
        <label class="col-md-2 control-label text-md-right"><label class="badge badge-danger">必須</label> 開始日時</label>

        <div class="col-md-3">
            <div class="input-group date" id="start_date" data-target-input="nearest">
                <input type="text" name="start_date" value="{{old('start_date', $post->start_date)}}" class="form-control datetimepicker-input" data-target="#start_date"/>
                <div class="input-group-append" data-target="#start_date" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                </div>
            </div>
            <script type="text/javascript">
                $(function () {
                    $('#start_date').datetimepicker({
                        locale: 'ja',
                        dayViewHeaderFormat: 'YYYY年 M月',
                        format: 'YYYY-MM-DD'
                    });
                });
            </script>
        </div>

        <div class="col-md-2">
            <div class="input-group date" id="start_time" data-target-input="nearest">
                @if(old('allday_flag', $post->allday_flag))
                <input type="text" name="start_time" value="" class="form-control datetimepicker-input" data-target="#start_time" disabled />
                @else
                <input type="text" name="start_time" value="{{old('start_time', $post->start_time)}}" class="form-control datetimepicker-input" data-target="#start_time" />
                @endif
                <div class="input-group-append" data-target="#start_time" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fas fa-clock"></i></div>
                </div>
            </div>
            @if ($errors && $errors->has('start_time')) <div class="text-danger">{{$errors->first('start_time')}}</div> @endif
            <script type="text/javascript">
                $(function () {
                    $('#start_time').datetimepicker({
                        locale: 'ja',
                        format: 'HH:mm'
                    });
                });
            </script>
        </div>
    </div>
    <div class="form-group row">
    @if ($errors && $errors->has('start_date'))
        <div class="col-md-2"></div>
        <div class="col-md-10 text-danger">{{$errors->first('start_date')}}</div>
    @endif
    </div>

    <div class="form-group row mb-0">
        <label class="col-md-2 control-label text-md-right">終了日時</label>
        <div class="col-md-3">
            <div class="input-group date" id="end_date" data-target-input="nearest">
                <input type="text" name="end_date" value="{{old('end_date', $post->end_date)}}" class="form-control datetimepicker-input" data-target="#end_date" />
                <div class="input-group-append" data-target="#end_date" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                </div>
            </div>
            <script type="text/javascript">
                $(function () {
                    $('#end_date').datetimepicker({
                        locale: 'ja',
                        dayViewHeaderFormat: 'YYYY年 M月',
                        format: 'YYYY-MM-DD'
                    });
                });
            </script>
        </div>

        <div class="col-md-2">
            <div class="input-group date" id="end_time" data-target-input="nearest">
                @if(old('allday_flag', $post->allday_flag))
                <input type="text" name="end_time" value="" class="form-control datetimepicker-input" data-target="#end_time" disabled />
                @else
                <input type="text" name="end_time" value="{{old('end_time', $post->end_time)}}" class="form-control datetimepicker-input" data-target="#end_time" />
                @endif
                <div class="input-group-append" data-target="#end_time" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fas fa-clock"></i></div>
                </div>
            </div>
            <script type="text/javascript">
                $(function () {
                    $('#end_time').datetimepicker({
                        locale: 'ja',
                        format: 'HH:mm'
                    });
                });
            </script>
        </div>
    </div>

    <div class="form-group row">
    @if ($errors && $errors->has('end_date'))
        <div class="col-md-2"></div>
        <div class="col-md-10 text-danger">{{$errors->first('end_date')}}</div>
    @endif
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label text-md-right"><label class="badge badge-danger">必須</label> 本文</label>
        <div class="col-md-10">
            @if (isset($reply) && $reply == true)
                <textarea name="body" class="form-control" rows=2>{!!old('body', $parent_post->getReplyBody())!!}</textarea>
            @else
                <textarea name="body" class="form-control" rows=2>{!!old('body', $post->body)!!}</textarea>
            @endif
            @if ($errors && $errors->has('body')) <div class="text-danger">{{$errors->first('body')}}</div> @endif
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            @if (empty($post->id))
            <div class="col-12">
            @else
            <div class="col-3 d-none d-xl-block"></div>
            <div class="col-9 col-xl-6">
            @endif
                <div class="text-center">
                    @if (isset($parent_post))
                        {{-- 返信 --}}
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/calendars/show/{{$page->id}}/{{$frame_id}}/{{$parent_post->id}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    @elseif (empty($post->id))
                        {{-- 新規 --}}
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    @else
                        {{-- 編集 --}}
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/plugin/calendars/show/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame_id}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass('lg')}}"> キャンセル</span></button>
                    @endif
                    <button type="button" class="btn btn-info mr-2" onclick="javascript:save_action();"><i class="far fa-save"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 一時保存</span></button>
                    <input type="hidden" name="bucket_id" value="">
                    @if (empty($post->id))
                        @if ($buckets->needApprovalUser(Auth::user()))
                            <button type="submit" class="btn btn-success"><i class="far fa-edit"></i> 登録申請</button>
                        @else
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 登録確定</button>
                        @endif
                    @else
                        @if ($buckets->needApprovalUser(Auth::user()))
                            <button type="submit" class="btn btn-success"><i class="far fa-edit"></i> 変更申請</button>
                        @else
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更確定</button>
                        @endif
                    @endif
                </div>
            </div>
            @if (!empty($post->id))
            <div class="col-3 col-xl-3 text-right">
                <a data-toggle="collapse" href="#collapse{{$frame_id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> 削除</span></span>
                </a>
            </div>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$frame_id}}" class="collapse">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/calendars/delete/{{$page->id}}/{{$frame_id}}/{{$post->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <input type="hidden" name="redirect_path" value="{{$page->permanent_link}}#frame-{{$frame_id}}">
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
