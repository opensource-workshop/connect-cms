{{--
 * 新着情報編集画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 新着情報プラグイン
 --}}

<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.whatsnews.whatsnews_frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

@if (!$whatsnew->id)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        設定画面から、使用する新着情報を選択するか、作成してください。
    </div>
@else
    <div class="alert alert-info" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>

        @if ($message)
            {{$message}}
        @else
            @if (empty($whatsnew) || $create_flag)
                新しい新着情報設定を登録します。
            @else
                新着情報設定を変更します。
            @endif
        @endif
    </div>
@endif

@if (!$whatsnew->id && !$create_flag)
@else
<form action="/plugin/whatsnews/saveBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    {{-- create_flag がtrue の場合、新規作成するためにwhatsnews_id を空にする --}}
    @if ($create_flag)
        <input type="hidden" name="whatsnews_id" value="">
    @else
        <input type="hidden" name="whatsnews_id" value="{{$whatsnew->id}}">
    @endif

    <div class="form-group">
        <label class="control-label">新着情報名 <label class="badge badge-danger">必須</label></label>
        <input type="text" name="whatsnew_name" value="{{old('whatsnew_name', $whatsnew->whatsnew_name)}}" class="form-control">
        @if ($errors && $errors->has('whatsnew_name')) <div class="text-danger">{{$errors->first('whatsnew_name')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="col-form-label">新着の取得方式</label><br />
        <div class="custom-control custom-radio custom-control-inline">
            @if($whatsnew->view_pattern == 0)
                <input type="radio" value="0" id="view_pattern_0" name="view_pattern" class="custom-control-input" checked="checked">
            @else
                <input type="radio" value="0" id="view_pattern_0" name="view_pattern" class="custom-control-input">
            @endif
            <label class="custom-control-label" for="view_pattern_0">件数で表示する。</label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
            @if($whatsnew->view_pattern == 1)
                <input type="radio" value="1" id="view_pattern_1" name="view_pattern" class="custom-control-input" checked="checked">
            @else
                <input type="radio" value="1" id="view_pattern_1" name="view_pattern" class="custom-control-input">
            @endif
            <label class="custom-control-label" for="view_pattern_1">日数で表示する。</label>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label">表示件数</label>
        <input type="text" name="count" value="{{old('count', $whatsnew->count)}}" class="form-control">
        @if ($errors && $errors->has('count')) <div class="text-danger">{{$errors->first('count')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">表示日数</label>
        <input type="text" name="days" value="{{old('days', $whatsnew->days)}}" class="form-control">
        @if ($errors && $errors->has('days')) <div class="text-danger">{{$errors->first('days')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="col-form-label">RSS配信の有無</label>（※ あとで）<br />
        <div class="custom-control custom-radio custom-control-inline">
            @if($whatsnew->rss == 0)
                <input type="radio" value="0" id="rss_0" name="rss" class="custom-control-input" checked="checked">
            @else
                <input type="radio" value="0" id="rss_0" name="rss" class="custom-control-input">
            @endif
            <label class="custom-control-label" for="rss_0">RSS配信しない</label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
            @if($whatsnew->rss == 1)
                <input type="radio" value="1" id="rss_1" name="rss" class="custom-control-input" checked="checked">
            @else
                <input type="radio" value="1" id="rss_1" name="rss" class="custom-control-input">
            @endif
            <label class="custom-control-label" for="rss_1">RSS配信する</label>
        </div>
    </div>

    <div class="form-group">
        <label class="col-form-label">登録者の表示</label>（※ あとで）<br />
        <div class="custom-control custom-radio custom-control-inline">
            @if($whatsnew->view_created_name == 0)
                <input type="radio" value="0" id="view_created_name_0" name="view_created_name" class="custom-control-input" checked="checked">
            @else
                <input type="radio" value="0" id="view_created_name_0" name="view_created_name" class="custom-control-input">
            @endif
            <label class="custom-control-label" for="view_created_name_0">表示しない</label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
            @if($whatsnew->view_created_name == 1)
                <input type="radio" value="1" id="view_created_name_1" name="view_created_name" class="custom-control-input" checked="checked">
            @else
                <input type="radio" value="1" id="view_created_name_1" name="view_created_name" class="custom-control-input">
            @endif
            <label class="custom-control-label" for="view_created_name_1">表示する</label>
        </div>
    </div>

    <div class="form-group">
        <label class="col-form-label">登録日時の表示</label>（※ あとで）<br />
        <div class="custom-control custom-radio custom-control-inline">
            @if($whatsnew->view_created_at == 0)
                <input type="radio" value="0" id="view_created_at_0" name="view_created_at" class="custom-control-input" checked="checked">
            @else
                <input type="radio" value="0" id="view_created_at_0" name="view_created_at" class="custom-control-input">
            @endif
            <label class="custom-control-label" for="view_created_at_0">表示しない</label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
            @if($whatsnew->view_created_at == 1)
                <input type="radio" value="1" id="view_created_at_1" name="view_created_at" class="custom-control-input" checked="checked">
            @else
                <input type="radio" value="1" id="view_created_at_1" name="view_created_at" class="custom-control-input">
            @endif
            <label class="custom-control-label" for="view_created_at_1">表示する</label>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label">対象プラグイン <label class="badge badge-danger">必須</label></label>
        <textarea name="target_plugin" rows="3" class="form-control">{!!old('target_plugin', $whatsnew->target_plugin)!!}</textarea>
        @if ($errors && $errors->has('target_plugin')) <div class="text-danger">{{$errors->first('target_plugin')}}</div> @endif
    </div>

    {{-- Submitボタン --}}
    <div class="form-group text-center">
        <div>
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'">
                <i class="fas fa-times"></i><span class="d-none d-md-inline"> キャンセル</span>
            </button>
            <button type="submit" class="btn btn-primary form-horizontal mr-2"><i class="fas fa-check"></i>
                <span class="d-none d-xl-inline">
                @if (empty($openingcalendar) || $create_flag)
                    登録
                @else
                    変更
                @endif
                </span>
            </button>

            {{-- 既存新着情報設定の場合は削除処理のボタンも表示 --}}
            @if ($create_flag)
            @else
                <a data-toggle="collapse" href="#collapse{{$whatsnew_frame->id}}">
                    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="d-none d-xl-inline"> 削除</span></span>
                </a>
            @endif
        </div>
    </div>
</form>

<div id="collapse{{$whatsnew_frame->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">新着情報設定を削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/redirect/plugin/whatsnews/destroyBuckets/{{$page->id}}/{{$frame_id}}/{{$whatsnew->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
