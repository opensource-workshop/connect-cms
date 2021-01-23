{{--
 * 祝日管理の編集画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 祝日管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.holiday.holiday_tab')
    </div>

    <div class="card-body">
        <form name="form_edit" method="post" action="{{url('/')}}/manage/holiday/update">
            {{ csrf_field() }}
            <input type="hidden" name="post_id" value="{{$post->id}}">
            <input type="hidden" name="status" value="0">

            {{-- 日付 --}}
            <div class="form-group row">
                <label class="col-md-2 control-label text-md-right">日付 <label class="badge badge-danger">必須</label></label>
                <div class="col-md-3">
                    <div class="input-group date" id="holiday_date" data-target-input="nearest">
                        <input type="text" name="holiday_date" value="{{old('holiday_date', $post->holiday_date)}}" class="form-control datetimepicker-input" data-target="#holiday_date"/>
                        <div class="input-group-append" data-target="#holiday_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                    @if ($errors && $errors->has('holiday_date')) <div class="text-danger">{{$errors->first('holiday_date')}}</div> @endif
                    <script type="text/javascript">
                    $(function () {
                        $('#holiday_date').datetimepicker({
                            locale: 'ja',
                            dayViewHeaderFormat: 'YYYY年 M月',
                            format: 'YYYY-MM-DD'
                        });
                    });
                    </script>
                </div>
            </div>

            {{-- 祝日名 --}}
            <div class="form-group row">
                <label class="col-md-2 control-label text-md-right">祝日名 <label class="badge badge-danger">必須</label></label>
                <div class="col-md-10">
                    <div class="input-group date" id="holiday_name">
                        <input type="text" name="holiday_name" value="{{old('holiday_name', $post->holiday_name)}}" class="form-control" />
                    </div>
                    @if ($errors && $errors->has('holiday_name')) <div class="text-danger">{{$errors->first('holiday_name')}}</div> @endif
                </div>
            </div>

            {{-- 更新ボタン --}}
            <div class="form-group row">
                @if (empty($post->id))
                <div class="col-12">
                @else
                <div class="col-3 d-none d-xl-block"></div>
                <div class="col-9 col-xl-6">
                @endif
                    <div class="text-center">
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/holiday')}}'"><i class="fas fa-times"></i> キャンセル</button>
                        @if (empty($post->id))
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 登録</button>
                        @else
                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更</button>
                        @endif
                    </div>
                </div>
                @if (!empty($post->id))
                <div class="col-3 col-xl-3 text-right">
                    <a data-toggle="collapse" href="#collapse_holiday_del">
                        <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> 削除</span>
                    </a>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>

<div id="collapse_holiday_del" class="collapse">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>
            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/manage/holiday/delete/{{$post->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
