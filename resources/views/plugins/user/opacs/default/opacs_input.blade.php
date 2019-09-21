{{--
 * 書誌データ登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
 --}}

<script type="text/javascript">
    {{-- 項目追加のsubmit JavaScript --}}
    function submit_book_search() {
        form_opac_book.book_search.value = 1;
        form_opac_book.submit();
    }
</script>

@if (isset($search_error_message) && $search_error_message)
    <div class="alert alert-danger" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        {{$search_error_message}}
    </div>
@endif

{{-- 登録用フォーム --}}
@if (empty($opacs_books->id))
{{--    <form action="/plugin/opacs/save/{{$page->id}}/{{$frame_id}}" method="POST" id="form_opac_book" name="form_opac_book" class="" onsubmit="return false;"> --}}
    <form action="/plugin/opacs/save/{{$page->id}}/{{$frame_id}}" method="POST" id="form_opac_book" name="form_opac_book" class="">
@else
{{--    <form action="/plugin/opacs/save/{{$page->id}}/{{$frame_id}}/{{$opacs_books->id}}" id="form_opac_book" name="form_opac_book" method="POST" class="" onsubmit="return false;"> --}}
    <form action="/plugin/opacs/save/{{$page->id}}/{{$frame_id}}/{{$opacs_books->id}}" id="form_opac_book" name="form_opac_book" method="POST" class="">
@endif
    {{ csrf_field() }}
    <input type="hidden" name="opacs_id" value="{{$opac_frame->opacs_id}}">
    <input type="hidden" name="book_search" value="0">

    <div class="form-group">
        <label class="control-label">ISBN等</label>
        <div class="row">
            <div class="col-sm-4">
                <input type="text" name="isbn" value="{{old('isbn', $opacs_books->isbn)}}" class="form-control">
                @if ($errors && $errors->has('isbn')) <div class="text-danger">{{$errors->first('isbn')}}</div> @endif
            </div>
            <div class="col-sm-3">
                <button type="buton" class="btn btn-success" onclick="javascript:submit_book_search();return false;"><i class="fas fa-search"></i> 書誌データ取得</button>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label">タイトル <span class="label label-danger">必須</span></label>
        @if (isset($book_search) && $book_search)
            <input type="text" name="title" value="{{$opacs_books->title}}" class="form-control">
        @else
            <input type="text" name="title" value="{{old('title', $opacs_books->title)}}" class="form-control">
            @if ($errors && $errors->has('title')) <div class="text-danger">{{$errors->first('title')}}</div> @endif
        @endif
    </div>

    <div class="form-group">
        <label class="control-label">請求記号</label>
        <input type="text" name="ndc" value="{{old('ndc', $opacs_books->ndc)}}" class="form-control">
        @if ($errors && $errors->has('ndc')) <div class="text-danger">{{$errors->first('ndc')}}</div> @endif
    </div>

    <div class="form-group">
        <label class="control-label">著者</label>
        @if (isset($book_search) && $book_search)
            <input type="text" name="creator" value="{{$opacs_books->creator}}" class="form-control">
        @else
            <input type="text" name="creator" value="{{old('creator', $opacs_books->creator)}}" class="form-control">
            @if ($errors && $errors->has('creator')) <div class="text-danger">{{$errors->first('creator')}}</div> @endif
        @endif

    </div>

    <div class="form-group">
        <label class="control-label">出版者</label>
        @if (isset($book_search) && $book_search)
            <input type="text" name="publisher" value="{{$opacs_books->publisher}}" class="form-control">
        @else
            <input type="text" name="publisher" value="{{old('publisher', $opacs_books->publisher)}}" class="form-control">
            @if ($errors && $errors->has('publisher')) <div class="text-danger">{{$errors->first('publisher')}}</div> @endif
        @endif
    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6">
                <div class="text-center">
                    <input type="hidden" name="bucket_id" value="">
                    @if (empty($opacs_books->id))
                        <button type="submit" class="btn btn-primary mr-3"><i class="fas fa-check"></i> 登録確定</button>
                    @else
                        <button type="submit" class="btn btn-primary mr-3"><i class="fas fa-check"></i> 変更確定</button>
                    @endif
                    <button type="button" class="btn btn-secondary" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i> キャンセル</button>
                </div>
            </div>
            <div class="col-sm-3 pull-right text-right">
                @if (!empty($opacs_books->id))
                    <a data-toggle="collapse" href="#collapse{{$opacs_books->id}}">
                        <span class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> <span class="hidden-xs">削除</span></span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</form>

<div id="collapse{{$opacs_books->id}}" class="collapse" style="margin-top: 8px;">
    <div class="panel panel-danger">
        <div class="panel-body">
            <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/plugin/opacs/destroy/{{$page->id}}/{{$frame_id}}/{{$opacs_books->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><span class="glyphicon glyphicon-ok"></span> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>
