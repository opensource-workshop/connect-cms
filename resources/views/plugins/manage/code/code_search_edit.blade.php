{{--
 * 検索条件登録・更新画面のテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コード管理
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
<div class="card-header p-0">

{{-- 機能選択タブ --}}
@include('plugins.manage.code.code_manage_tab')

{{-- ボタンによってアクション切替 --}}
<script type="text/javascript">
    function submitAction(url) {
        form_code.action = url;
        form_code.submit();
    }
    function submitActionConfirm(url, message = '削除します。\nよろしいですか？') {
        if (confirm(message)) {
            form_code.action = url;
            form_code.submit();
        }
    }
</script>

</div>
<div class="card-body">

    <form name="form_code" action="" method="POST" class="form-horizontal">
        {{ csrf_field() }}
        <input name="page" value="{{$paginate_page}}" type="hidden">

        @if ($codes_search->id)
        <div class="form-group form-row">
            <label class="col-md-3 col-form-label text-md-right">コピーして登録画面へ</label>
            <div class="col-md-9 d-sm-flex align-items-center">
                <button type="button" class="btn btn-outline-primary form-horizontal" onclick="submitAction('{{url('/')}}/manage/code/searchRegist')">
                    <i class="fas fa-copy "></i> コピー
                </button>
            </div>
        </div>
        @endif

        <div class="form-group form-row">
            <label for="name" class="col-md-3 col-form-label text-md-right">検索ラベル名 <label class="badge badge-danger">必須</label></label>
            <div class="col-md-9">
                <input type="text" name="name" id="name" value="{{old('name', $codes_search->name)}}" class="form-control">
                @if ($errors && $errors->has('name')) <div class="text-danger">{{$errors->first('name')}}</div> @endif
            </div>
        </div>
        <div class="form-group form-row">
            <label for="search_words" class="col-md-3 col-form-label text-md-right">
                検索条件
                <label class="badge badge-danger">必須</label>
            </label>
            <div class="col-md-9 input-group">
                <input type="text" name="search_words" id="search_words" value="{{old('search_words', $codes_search->search_words)}}" class="form-control" aria-describedby="basic-addon2">
                <div class="ml-2">
                    <a data-toggle="collapse" href="#collapse-search-help">
                        <span class="btn btn-light"><i class="fas fa-question-circle"></i></span>
                    </a>
                </div>
                @if ($errors && $errors->has('search_words')) <div class="text-danger">{{$errors->first('search_words')}}</div> @endif
            </div>
        </div>

        {{-- 検索条件の補足 --}}
        @include('plugins.manage.code.search_help')

        <div class="form-group form-row">
            <label for="display_sequence" class="col-md-3 col-form-label text-md-right">表示順</label>
            <div class="col-md-9">
                <input type="text" name="display_sequence" id="display_sequence" value="{{old('display_sequence', $codes_search->display_sequence)}}" class="form-control">
                <div class="text-muted">{{$codes_help_message->display_sequence_help_message}}</div>
            </div>
        </div>

        <!-- Add or Update code Button -->
        <div class="form-group form-row">
            <div class="offset-sm-3 col-sm-6">
                <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/manage/code/searches?page={{$paginate_page}}'"><i class="fas fa-times"></i> キャンセル</button>
                @if ($codes_search->id)
                <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="submitAction('{{url('/')}}/manage/code/searchUpdate/{{$codes_search->id}}')">
                    <i class="fas fa-check"></i> 更新
                </button>
                @else
                <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="submitAction('{{url('/')}}/manage/code/searchStore')">
                    <i class="fas fa-check"></i> 登録
                </button>
                @endif
            </div>
            @if ($codes_search->id)
            <div class="col-sm-3 pull-right text-right">
                <button type="button" class="btn btn-danger form-horizontal" onclick="submitActionConfirm('{{url('/')}}/manage/code/searchDestroy/{{$codes_search->id}}')">
                    <i class="fas fa-trash-alt"></i> 削除
                </button>
            </div>
            @endif
        </div>
    </form>

</div>
</div>

@endsection
