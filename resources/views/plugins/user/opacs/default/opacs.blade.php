{{--
 * Opac画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Opac・プラグイン
 --}}

{{-- OPAC表示 --}}
@if (isset($opacs_books))


<div class="form-group">
    <div class="row">
        <div class="col-sm-6">
    <div class="input-group date" data-provide="datepicker">
                <input type="text" name="return_date" value="" class="form-control datepicker" placeholder="キーワード検索">

	<span class="input-group-btn">
		<button type="button" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span></button>
	</span>
    </div>
        </div>
    </div>
</div>



{{--
    <div class="panel panel-primary">
        <!-- Default panel contents -->
        <div class="panel-heading">{{$opac_frame->opac_name}}</div>
        <!-- Table -->
--}}
        <div class="table-responsive">
        <table class="table">
        <thead>
            <tr>
                <th nowrap>詳細</th>
                <th nowrap>貸</th>
                <th nowrap>ISBN等</th>
                <th nowrap>タイトル</th>
                <th nowrap>請求記号</th>
                <th nowrap>著者</th>
                <th nowrap>出版者</th>
            </tr>
        </thead>
        <tbody>
        @foreach($opacs_books as $book)
            <tr>
                <td>
                    <a href="{{url('/')}}/plugin/opacs/detail/{{$page->id}}/{{$frame_id}}/{{$book->id}}">
                        <span class="label label-primary">詳細</span>
                    </a>
                </td>
                <td>@if ($book->lent_flag != 0) <span style="color: red;"><span class="glyphicon glyphicon-user"></span></span> @endif</td>
                <td nowrap>
                    @auth
                    <a href="{{url('/')}}/plugin/opacs/edit/{{$page->id}}/{{$frame_id}}/{{$book->id}}">
                        <span class="glyphicon glyphicon-edit"></span>
                    </a>
                    @endauth
                    {{$book->isbn}}
                </td>
                <td>{{$book->title}}</td>
                <td>{{$book->ndc}}</td>
                <td>{{$book->creator}}</td>
                <td>{{$book->publisher}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
        </div>
        <div class="panel-footer">貸出、返却、リクエストは各書籍の詳細画面から操作できます。</div>
{{--
    </div>
--}}

    {{-- ページング処理 --}}
    <div class="text-center">
        {{ $opacs_books->links() }}
    </div>
@endif

{{-- 新規登録 --}}
@auth
    @if (isset($frame) && $frame->bucket_id)
        <p class="text-center" style="margin-top: 16px;">
            {{-- 新規登録ボタン --}}
            <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/opacs/create/{{$page->id}}/{{$frame_id}}'"><span class="glyphicon glyphicon-plus"></span> 新規登録</button>
        </p>
    @else
        <div class="panel panel-default">
            <div class="panel-body bg-danger">
                <p class="text-center cc_margin_bottom_0">フレームの設定画面から、使用するOPACを選択するか、作成してください。</p>
            </div>
        </div>
    @endif
@endauth

