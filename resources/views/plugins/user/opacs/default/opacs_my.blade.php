{{--
 * MyOpacテンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Opacプラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

{{-- メッセージエリア --}}

@if ($errors)
<div class="alert alert-danger" role="alert">
    <i class="fas fa-exclamation-circle"></i> エラーがあります。詳しくは各項目のメッセージを参照してください。
</div>
@endif

@if ($messages)
<div class="alert alert-primary" role="alert">
    @foreach($messages as $message)
        {{$message}}<br />
    @endforeach
</div>
@endif

{{-- モデレータ専用画面 --}}

@can("role_article")
<div class="card mb-3">
    <div class="card-header"><a href="{{url('/')}}/plugin/opacs/lentlist/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}">貸出中一覧はこちら（モデレータ権限用）</a></div>
</div>
@endcan

{{-- マイページ画面 --}}

<div class="card mb-3">
    <div class="card-header">ログインしているユーザーID:{{$user->userid}}</div>
</div>


{{-- WEB上から借りる/返すは、一般ユーザーは行わない。モデレータは貸出中一覧から行うのでここからは行わない。その為非表示とする
<form action="{{url('/')}}/plugin/opacs/lent/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" id="form_lent" name="form_lent" method="POST">
    {{ csrf_field() }}

    <div class="card mb-3">
        <div class="card-header">書籍を借りる</div>
        <div class="card-body">
        @if ($lent_count_ok)
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">バーコード <label class="badge badge-danger">必須</label></label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input type="text" name="barcode" value="{{old('barcode')}}" class="form-control" placeholder="バーコードエリア">
                    <small class="text-muted">上のバーコードエリアにカーソルを合わせて、バーコードリーダーで読み込んでください。</small>
                    @if ($errors && $errors->has('barcode')) <div class="text-danger">{{$errors->first('barcode')}}</div> @endif
                </div>
            </div>

            @can("role_article")
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">貸出期限 <label class="badge badge-danger">必須</label></label>
                <div class="{{$frame->getSettingInputClass()}}">

                    <div class="input-group date" id="return_scheduled" data-target-input="nearest">
                        <input type="text" name="return_scheduled" value="{{old('return_scheduled')}}" class="form-control datetimepicker-input" data-target="#return_scheduled"/>
                        <div class="input-group-append" data-target="#return_scheduled" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="far fa-clock"></i></div>
                        </div>
                    </div>
                    <small class="text-muted">モデレータの場合は貸出期限を入力してください。</small>
                    @if ($errors && $errors->has('return_scheduled')) <div class="text-danger">{{$errors->first('return_scheduled')}}</div> @endif
                    <script type="text/javascript">
                        $(function () {
                            $('#return_scheduled').datetimepicker({
                                locale: 'ja',
                                dayViewHeaderFormat: 'YYYY年 M月',
                                format: 'YYYY/MM/DD'
                            });
                        });
                    </script>

                </div>
            </div>
            @else
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">貸出期限</label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <div class="card p-2">{{$lent_max_date}}</div>
                </div>
            </div>
            @endcan

            @can("role_article")
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">ログインID <label class="badge badge-danger">必須</label></label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input type="text" name="student_no" value="{{old('student_no')}}" class="form-control">
                    <small class="text-muted">モデレータの場合はログインID（学籍番号/教職員番号）を入力してください。</small>
                    @if ($errors && $errors->has('student_no')) <div class="text-danger">{{$errors->first('student_no')}}</div> @endif
                </div>
            </div>
            @endcan

            <!-- Submitボタン -->
            <div class="form-group text-center mt-3 mb-0">
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                            貸し出し
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle"></i> 貸出可能な最大冊数を貸し出し中です。
            </div>
        @endif
        </div>
    </div>
</form>

<form action="{{url('/')}}/plugin/opacs/returnLent/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}" id="form_lent" name="form_lent" method="POST">
    {{ csrf_field() }}
    <div class="card mb-3">
        <div class="card-header">書籍を返す</div>
        <div class="card-body">
        @if ($lent_return_ok)
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">バーコード <label class="badge badge-danger">必須</label></label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input type="text" name="return_barcode" value="{{old('return_barcode')}}" class="form-control" placeholder="バーコードエリア">
                    <small class="text-muted">上のバーコードエリアにカーソルを合わせて、バーコードリーダーで読み込んでください。</small>
                    @if ($errors && $errors->has('return_barcode')) <div class="text-danger">{{$errors->first('return_barcode')}}</div> @endif
                </div>
            </div>

            @can("role_article")
            <div class="form-group row">
                <label class="{{$frame->getSettingLabelClass()}}">ログインID <label class="badge badge-danger">必須</label></label>
                <div class="{{$frame->getSettingInputClass()}}">
                    <input type="text" name="return_student_no" value="{{old('return_student_no')}}" class="form-control">
                    <small class="text-muted">モデレータの場合はログインID（学籍番号/教職員番号）を入力してください。</small>
                    @if ($errors && $errors->has('return_student_no')) <div class="text-danger">{{$errors->first('return_student_no')}}</div> @endif
                </div>
            </div>
            @endcan

            <!-- Submitボタン -->
            <div class="form-group text-center mt-3 mb-0">
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i>
                            返却
                        </button>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header">貸出中の書籍はありません。</div>
            </div>
        @endif
        </div>
    </div>
</form>
--}}

@if (session('lent_errors'))
    <div class="alert alert-danger" style="margin-top: 10px;" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('lent_errors') }}
    </div>
@endif

<div class="card">
    <div class="card-header" id="frame-{{$frame->id}}-lentlist">現在借りている／郵送リクエストしている書籍</div>
    @if (isset($lents) && count($lents) > 0)
        <ul class="list-group list-group-flush">
        @foreach($lents as $lent)
            <li class="list-group-item">
                <div style="float: left;">
                タイトル：{{$lent->title}}<br />
                @if ($lent->lent_flag !=2)
                返却期限：{!!$lent->getFormatRreturnScheduled()!!}<br />
                @endif
                貸出区分：{{$lent->getLentStr()}}
                </div>
                @if ($lent->lent_flag ==2)
                <div class="form-group text-right mt-3 mb-0" style="float: right;">
                    <div class="text-center">
                        <!-- 貸出リクエストキャンセル -->
                        <form action="{{url('/')}}/plugin/opacs/destroyRequest/{{$page->id}}/{{$frame_id}}/{{$lent->opacs_books_id}}#frame-{{$frame->id}}-lentlist" method="POST">
                            {{csrf_field()}}
                            <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('貸し出しリクエストを取り消します。\nよろしいですか？')"><span class="glyphicon glyphicon-ok"></span><i class="fas fa-trash-alt"></i> 貸し出しリクエストを取り消す</button>
                        </form>
                    </div>
                </div>
                @endif
            </li>
        @endforeach
        </ul>
    @else
        <ul class="list-group list-group-flush">
            <li class="list-group-item">現在、借りている書籍はありません。</li>
        </ul>
    @endif
</div>

@endsection
