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
</script>

</div>
<div class="card-body">

    <div class="alert alert-info" role="alert">
        コード一覧の検索条件を記録しておけます。<br>
        記録した検索条件は、コード一覧に検索ボタンとして表示され、押すとその条件で検索します。
    </div>

    @include('common.errors_form_line')

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
                    <a href="https://connect-cms.jp/manual/manager/code#collapse-search-help" target="_brank">
                        <span class="btn btn-light"><i class="fas fa-question-circle" data-toggle="tooltip" title="オンラインマニュアルはこちら"></i></span>
                    </a>
                </div>
            </div>
            @if ($errors && $errors->has('search_words')) <div class="text-danger offset-md-3 col-md-9">{{$errors->first('search_words')}}</div> @endif
        </div>

        <div class="form-group form-row">
            <label for="display_sequence" class="col-md-3 col-form-label text-md-right">表示順</label>
            <div class="col-md-9">
                <input type="text" name="display_sequence" id="display_sequence" value="{{old('display_sequence', $codes_search->display_sequence)}}" class="form-control">
                <div class="text-muted">{{$codes_help_message->display_sequence_help_message}}</div>
            </div>
        </div>

        <!-- Add or Update code Button -->
        <div class="form-group text-center">
            <div class="form-row">
                <div class="offset-xl-3 col-9 col-xl-6">
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
                    <div class="col-3 col-xl-3 text-right">
                        <a data-toggle="collapse" href="#collapse{{$codes_search->id}}">
                            <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> 削除</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </form>

    <div id="collapse{{$codes_search->id}}" class="collapse">
        <div class="card border-danger">
            <div class="card-body">
                <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

                <div class="text-center">
                    {{-- 削除ボタン --}}
                    <form action="{{url('/')}}/manage/code/searchDestroy/{{$codes_search->id}}" method="POST">
                        {{csrf_field()}}
                        <button type="submit" class="btn btn-danger" onclick="return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

</div>
</div>

@endsection
