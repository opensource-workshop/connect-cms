{{--
 * 書誌データ登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
<script type="text/javascript">
    {{-- 項目追加のsubmit JavaScript --}}
    function submit_book_search() {
        form_opac_book.action = "{{url('/')}}/plugin/opacs/getBookInfo/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}";
        form_opac_book.submit();
    }
</script>

{{-- メッセージ表示 --}}
@if (isset($input_error_message) && $input_error_message)
    <div class="alert alert-danger" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        {{$input_error_message}}
    </div>
@endif
<!-- フラッシュメッセージ -->
@if (session('save_opacs'))
    <div class="alert alert-info" style="margin-top: 10px;">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('save_opacs') }}
    </div>
@endif

{{-- 登録用フォーム --}}
@if (empty($opacs_books->id))
    <form action="{{url('/')}}/redirect/plugin/opacs/save/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" id="form_opac_book" name="form_opac_book" class="">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/opacs/create/{{$page->id}}/{{$frame_id}}#frame-{{$frame_id}}">
@else
    <form action="{{url('/')}}/redirect/plugin/opacs/save/{{$page->id}}/{{$frame_id}}/{{$opacs_books->id}}#frame-{{$frame->id}}" id="form_opac_book" name="form_opac_book" method="POST" class="">
        <input type="hidden" name="redirect_path" value="{{url('/')}}/plugin/opacs/edit/{{$page->id}}/{{$frame_id}}/{{$opacs_books->id}}#frame-{{$frame_id}}">
@endif
    {{ csrf_field() }}
    <input type="hidden" name="opacs_id" value="{{$opac_frame->opacs_id}}">
    <input type="hidden" name="book_search" value="0">

    <div class="form-group row">
        <label class="col-sm-2 control-label">ISBN等 </label>
        <div class="col-sm-5">
            <input type="text" name="isbn" value="{{old('isbn', $opacs_books->isbn)}}" class="form-control">
            @if ($errors && $errors->has('isbn')) <div class="text-danger">{{$errors->first('isbn')}}</div> @endif
            <small class="form-text text-muted">※ ハイフンなしで10桁もしくは13桁の英数値で入力してください。</small>
        </div>
        <div class="col-sm-5">
            <button type="buton" class="btn btn-success" onclick="javascript:submit_book_search();return false;"><i class="fas fa-search"></i> 書誌データ取得</button>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">タイトル <label class="badge badge-danger">必須</label></label>
        <div class="col-md-10">
            @if (isset($book_search) && $book_search)
                <input type="text" name="title" value="{{$opacs_books->title}}" class="form-control">
            @else
                <input type="text" name="title" value="{{old('title', $opacs_books->title)}}" class="form-control">
                @if ($errors && $errors->has('title')) <div class="text-danger">{{$errors->first('title')}}</div> @endif
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">請求記号</label>
        <div class="col-md-10">
            @if (isset($book_search) && $book_search)
                <input type="text" name="ndc" value="{{$opacs_books->ndc}}" class="form-control">
            @else
                <input type="text" name="ndc" value="{{old('ndc', $opacs_books->ndc)}}" class="form-control">
                @if ($errors && $errors->has('ndc')) <div class="text-danger">{{$errors->first('ndc')}}</div> @endif
            @endif
            <small class="form-text text-muted">※ [分類]をもととした図書館独自の値を設定します。</small>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">著者</label>
        <div class="col-md-10">
            @if (isset($book_search) && $book_search)
                <input type="text" name="creator" value="{{$opacs_books->creator}}" class="form-control">
            @else
                <input type="text" name="creator" value="{{old('creator', $opacs_books->creator)}}" class="form-control">
                @if ($errors && $errors->has('creator')) <div class="text-danger">{{$errors->first('creator')}}</div> @endif
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">出版者</label>
        <div class="col-md-10">
            @if (isset($book_search) && $book_search)
                <input type="text" name="publisher" value="{{$opacs_books->publisher}}" class="form-control">
            @else
                <input type="text" name="publisher" value="{{old('publisher', $opacs_books->publisher)}}" class="form-control">
                @if ($errors && $errors->has('publisher')) <div class="text-danger">{{$errors->first('publisher')}}</div> @endif
            @endif
        </div>
    </div>

    <h4><span class="badge badge-primary">管理用項目（一般利用者には見えません）</span></h4>

    {{--
    <div class="form-group row">
        <label class="col-md-2 control-label">ICタグUID</label>
        <div class="col-md-10">
            <input type="text" name="rf_uid" value="" class="form-control">
        </div>
    </div>
    --}}

    <div class="form-group row">
        <label class="col-md-2 control-label">バーコード <label class="badge badge-danger">必須</label></label>
        <div class="col-md-10">
            <input type="text" name="barcode" value="{{old('barcode', $opacs_books->barcode)}}" class="form-control">
            @if ($errors && $errors->has('barcode')) <div class="text-danger">{{$errors->first('barcode')}}</div> @endif
            <small class="form-text text-muted">※ ハイフンなし、チェックデジットあり。</small>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">タイトルヨミ</label>
        <div class="col-md-10">
            @if (isset($book_search) && $book_search)
                <input type="text" name="title_read" value="{{$opacs_books->title_read}}" class="form-control">
            @else
                <input type="text" name="title_read" value="{{old('title_read', $opacs_books->title_read)}}" class="form-control">
                @if ($errors && $errors->has('title_read')) <div class="text-danger">{{$errors->first('title_read')}}</div> @endif
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">サブタイトル</label>
        <div class="col-md-10">
            <input type="text" name="subtitle" value="{{old('subtitle', $opacs_books->subtitle)}}" class="form-control">
            @if ($errors && $errors->has('subtitle')) <div class="text-danger">{{$errors->first('subtitle')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">シリーズ</label>
        <div class="col-md-10">
            @if (isset($book_search) && $book_search)
                <input type="text" name="series" value="{{$opacs_books->series}}" class="form-control">
            @else
                <input type="text" name="series" value="{{old('series', $opacs_books->series)}}" class="form-control">
                @if ($errors && $errors->has('series')) <div class="text-danger">{{$errors->first('series')}}</div> @endif
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">出版年</label>
        <div class="col-md-10">
            @if (isset($book_search) && $book_search)
                <input type="text" name="publication_year" value="{{$opacs_books->publication_year}}" class="form-control">
            @else
                <input type="text" name="publication_year" value="{{old('publication_year', $opacs_books->publication_year)}}" class="form-control">
                @if ($errors && $errors->has('publication_year')) <div class="text-danger">{{$errors->first('publication_year')}}</div> @endif
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">分類</label>
        <div class="col-md-10">
            @if (isset($book_search) && $book_search)
                <input type="text" name="class" value="{{$opacs_books->class}}" class="form-control">
            @else
                <input type="text" name="class" value="{{old('class', $opacs_books->class)}}" class="form-control">
                @if ($errors && $errors->has('class')) <div class="text-danger">{{$errors->first('class')}}</div> @endif
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">大きさ</label>
        <div class="col-md-10">
            @if (isset($book_search) && $book_search)
                <input type="text" name="size" value="{{$opacs_books->size}}" class="form-control">
            @else
                <input type="text" name="size" value="{{old('size', $opacs_books->size)}}" class="form-control">
                @if ($errors && $errors->has('size')) <div class="text-danger">{{$errors->first('size')}}</div> @endif
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">頁数</label>
        <div class="col-md-10">
            @if (isset($book_search) && $book_search)
                <input type="text" name="page_number" value="{{$opacs_books->page_number}}" class="form-control">
            @else
                <input type="text" name="page_number" value="{{old('page_number', $opacs_books->page_number)}}" class="form-control">
                @if ($errors && $errors->has('page_number')) <div class="text-danger">{{$errors->first('page_number')}}</div> @endif
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">MARC NO</label>
        <div class="col-md-10">
            @if (isset($book_search) && $book_search)
                <input type="text" name="marc" value="{{$opacs_books->marc}}" class="form-control">
            @else
                <input type="text" name="marc" value="{{old('marc', $opacs_books->marc)}}" class="form-control">
                @if ($errors && $errors->has('marc')) <div class="text-danger">{{$errors->first('marc')}}</div> @endif
            @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">資料区分</label>
        <div class="col-md-10">
            <select class="form-control" name="type" class="form-control">
                <option value=""></option>
                <option value="1" @if(old('type', $opacs_books->type)=="1") selected @endif>1:一般書</option>
                <option value="3" @if(old('type', $opacs_books->type)=="3") selected @endif>3:雑誌</option>
                <option value="4" @if(old('type', $opacs_books->type)=="4") selected @endif>4:ＡＶ</option>
            </select>
            @if ($errors && $errors->has('type')) <div class="text-danger">{{$errors->first('type')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">配架区分</label>
        <div class="col-md-10">
            <select class="form-control" name="shelf" class="form-control">
                <option value=""></option>
                <option value="1" @if(old('shelf', $opacs_books->shelf)=="1") selected @endif>1:開架</option>
                <option value="2" @if(old('shelf', $opacs_books->shelf)=="2") selected @endif>2:閉架</option>
                <option value="3" @if(old('shelf', $opacs_books->shelf)=="3") selected @endif>3:研究室</option>
                <option value="4" @if(old('shelf', $opacs_books->shelf)=="4") selected @endif>4:その他</option>
            </select>
            @if ($errors && $errors->has('shelf')) <div class="text-danger">{{$errors->first('shelf')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">貸出区分</label>
        <div class="col-md-10">
            <select class="form-control" name="lend_flag" class="form-control">
                <option value=""></option>
                <option value="0" @if(old('lend_flag', $opacs_books->lend_flag)=="0") selected @endif>0:一般</option>
                <option value="1" @if(old('lend_flag', $opacs_books->lend_flag)=="1") selected @endif>1:</option>
                <option value="2" @if(old('lend_flag', $opacs_books->lend_flag)=="2") selected @endif>2:館内</option>
                <option value="9" @if(old('lend_flag', $opacs_books->lend_flag)=="9") selected @endif>9:禁帯出</option>
            </select>
            @if ($errors && $errors->has('lend_flag')) <div class="text-danger">{{$errors->first('lend_flag')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">受入区分</label>
        <div class="col-md-10">
            <select class="form-control" name="accept_flag" class="form-control">
                <option value=""></option>
                <option value="0" @if(old('accept_flag', $opacs_books->accept_flag)=="0") selected @endif>0:購入</option>
                <option value="1" @if(old('accept_flag', $opacs_books->accept_flag)=="1") selected @endif>1:寄贈</option>
            </select>
            @if ($errors && $errors->has('accept_flag')) <div class="text-danger">{{$errors->first('accept_flag')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">受入日付</label>
        <div class="col-md-10">
            <div class="input-group date" id="accept_date" data-target-input="nearest">
                <input type="text" name="accept_date" value="{{old('accept_date', $opacs_books->accept_date)}}" class="form-control datetimepicker-input" data-target="#accept_date"/>
                <div class="input-group-append" data-target="#accept_date" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                </div>
            </div>
            @if ($errors && $errors->has('accept_date')) <div class="text-danger">{{$errors->first('accept_date')}}</div> @endif
            <script type="text/javascript">
                $(function () {
                    $('#accept_date').datetimepicker({
                        locale: 'ja',
                        dayViewHeaderFormat: 'YYYY年 M月',
                        format: 'YYYY/MM/DD'
                    });
                });
            </script>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">受入金額</label>
        <div class="col-md-10">
            @if (isset($book_search) && $book_search)
                <input type="text" name="accept_price" value="{{$opacs_books->accept_price}}" class="form-control">
            @else
                <input type="text" name="accept_price" value="{{old('accept_price', $opacs_books->accept_price)}}" class="form-control">
                @if ($errors && $errors->has('accept_price')) <div class="text-danger">{{$errors->first('accept_price')}}</div> @endif
            @endif
            <small class="form-text text-muted">※ "円"や"￥"の単位指定は不要。数値で入力してください。</small>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">保存期限</label>
        <div class="col-md-10">
            <div class="input-group date" id="storage_life" data-target-input="nearest">
                <input type="text" name="storage_life" value="{{old('storage_life', $opacs_books->storage_life)}}" class="form-control datetimepicker-input" data-target="#storage_life"/>
                <div class="input-group-append" data-target="#storage_life" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                </div>
            </div>
            @if ($errors && $errors->has('storage_life')) <div class="text-danger">{{$errors->first('storage_life')}}</div> @endif
            <script type="text/javascript">
                $(function () {
                    $('#storage_life').datetimepicker({
                        locale: 'ja',
                        dayViewHeaderFormat: 'YYYY年 M月',
                        format: 'YYYY/MM/DD'
                    });
                });
            </script>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">除籍区分</label>
        <div class="col-md-10">
            <select class="form-control" name="remove_flag" class="form-control">
                <option value=""></option>
                <option value="1" @if(old('remove_flag', $opacs_books->remove_flag)=="1") selected @endif>1:廃棄</option>
                <option value="3" @if(old('remove_flag', $opacs_books->remove_flag)=="3") selected @endif>3:不明</option>
                <option value="4" @if(old('remove_flag', $opacs_books->remove_flag)=="4") selected @endif>4:曝書不明</option>
                <option value="5" @if(old('remove_flag', $opacs_books->remove_flag)=="5") selected @endif>5:その他</option>
            </select>
            @if ($errors && $errors->has('remove_flag')) <div class="text-danger">{{$errors->first('remove_flag')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">除籍日付</label>
        <div class="col-md-10">
            <div class="input-group date" id="remove_date" data-target-input="nearest">
                <input type="text" name="remove_date" value="{{old('remove_date', $opacs_books->remove_date)}}" class="form-control datetimepicker-input" data-target="#remove_date"/>
                <div class="input-group-append" data-target="#remove_date" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                </div>
            </div>
            @if ($errors && $errors->has('remove_date')) <div class="text-danger">{{$errors->first('remove_date')}}</div> @endif
            <script type="text/javascript">
                $(function () {
                    $('#remove_date').datetimepicker({
                        locale: 'ja',
                        dayViewHeaderFormat: 'YYYY年 M月',
                        format: 'YYYY/MM/DD'
                    });
                });
            </script>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">状態</label>
        <div class="col-md-10">
            <select class="form-control" name="possession" class="form-control">
                <option value=""></option>
                <option value="1" @if(old('possession', $opacs_books->possession)=="1") selected @endif>1:所蔵</option>
                <option value="2" @if(old('possession', $opacs_books->possession)=="2") selected @endif>2:受入整理中</option>
                <option value="3" @if(old('possession', $opacs_books->possession)=="3") selected @endif>3:貸出</option>
                <option value="4" @if(old('possession', $opacs_books->possession)=="4") selected @endif>4:除籍</option>
                <option value="5" @if(old('possession', $opacs_books->possession)=="5") selected @endif>5:不明本</option>
            </select>
            @if ($errors && $errors->has('possession')) <div class="text-danger">{{$errors->first('possession')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">所在館</label>
        <div class="col-md-10">
            <select class="form-control" name="library" class="form-control">
                <option value=""></option>
                <option value="1" @if(old('library', $opacs_books->library)=="1") selected @endif>1:横浜CP図書館</option>
                <option value="2" @if(old('library', $opacs_books->library)=="2") selected @endif>2:箱根CP図書館</option>
            </select>
            @if ($errors && $errors->has('library')) <div class="text-danger">{{$errors->first('library')}}</div> @endif
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">最終貸出日</label>
        <div class="col-md-10">
            <div class="input-group date" id="last_lending_date" data-target-input="nearest">
                <input type="text" name="last_lending_date" value="{{old('last_lending_date', $opacs_books->last_lending_date)}}" class="form-control datetimepicker-input" data-target="#last_lending_date"/>
                <div class="input-group-append" data-target="#last_lending_date" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                </div>
            </div>
            @if ($errors && $errors->has('last_lending_date')) <div class="text-danger">{{$errors->first('last_lending_date')}}</div> @endif
            <script type="text/javascript">
                $(function () {
                    $('#last_lending_date').datetimepicker({
                        locale: 'ja',
                        dayViewHeaderFormat: 'YYYY年 M月',
                        format: 'YYYY/MM/DD'
                    });
                });
            </script>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-2 control-label">貸出累計</label>
        <div class="col-md-10">
            <input type="text" name="total_lends" value="{{old('total_lends', $opacs_books->total_lends)}}" class="form-control">
            @if ($errors && $errors->has('total_lends')) <div class="text-danger">{{$errors->first('total_lends')}}</div> @endif
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-sm-3"></div>
            <div class="col-sm-6">
                <div class="text-center">
                    <input type="hidden" name="bucket_id" value="">
                    <button type="button" class="btn btn-secondary mr-3" onclick="location.href='{{URL::to($page->permanent_link)}}#frame-{{$frame->id}}'"><i class="fas fa-times"></i> キャンセル</button>
                    @if (empty($opacs_books->id))
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 登録確定</button>
                    @else
                        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 変更確定</button>
                    @endif
                </div>
            </div>
            <div class="col-sm-3 pull-right text-right">
                @if (!empty($opacs_books->id))
                    <a data-toggle="collapse" href="#collapse{{$opacs_books->id}}">
                        <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> 削除</span></span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</form>

<div id="collapse{{$opacs_books->id}}" class="collapse">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/plugin/opacs/destroy/{{$page->id}}/{{$frame_id}}/{{$opacs_books->id}}#frame-{{$frame->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
