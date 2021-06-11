{{--
 * 書誌データ詳細画面（貸出部分）テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category OPACプラグイン
 --}}

@auth
    <h4><span class="badge badge-primary">郵送貸し出しリクエスト</span></h4>

    <div class="form-group">

        @if ($opacs_books->lend_flag == '9:禁帯出')
            <div class="alert alert-warning mt-2">
                <i class="fas fa-exclamation-circle"></i>
                この書籍は「禁帯出」のため、貸し出しはできません。
            </div>
{{-- ※貸出中＆貸出リクエスト中でも、貸出リクエストは出せる
        @elseif ($opacs_books->lent_flag == 1)
            <div class="alert alert-warning mt-2">
                <i class="fas fa-exclamation-circle"></i>
                この書籍は現在、貸し出し中のため、郵送貸し出しリクエストはできません。
            </div>
        @elseif ($opacs_books->lent_flag == 2)
            <div class="alert alert-warning mt-2">
                <i class="fas fa-exclamation-circle"></i>
                この書籍は現在、貸し出しリクエスト中のため、郵送貸し出しリクエストはできません。
            </div>
--}}
        @elseif ($done_lent > 0)
            <div class="alert alert-warning mt-2">
                <i class="fas fa-exclamation-circle"></i>
                この書籍は既に借りています。
            </div>
        @elseif ($done_requests == true )
            <div class="alert alert-warning mt-2">
                <i class="fas fa-exclamation-circle"></i>
                この書籍は既に郵送リクエスト済みです。
            </div>
        @elseif (!$lent_limit_check)
            <div class="alert alert-danger mt-2">
                <i class="fas fa-exclamation-circle"></i>
                {{$lent_error_message}}
            </div>
        @else
            <form action="{{url('/')}}/plugin/opacs/requestLent/{{$page->id}}/{{$frame_id}}/{{$opacs_books_id}}#frame-{{$frame_id}}" id="form_requestLent" name="form_requestLent" method="POST">
                {{ csrf_field() }}
                <div class="row">
                
                    {{-- 学籍番号/教職員番号　通常ユーザーは自ログインID固定 --}}
                    <div class="col-12 col-sm-12 col-md-6 mt-2">
                        @can("role_article")
                            {{-- 権限ありの場合 --}}
                            <label class="control-label">学籍番号/教職員番号</label> <label class="badge badge-danger">必須</label>
                            <input type="text" name="req_student_no" value="{{old('req_student_no')}}" class="form-control">
                        @else
                            {{-- 権限なしの場合 --}}
                            <label class="control-label">学籍番号/教職員番号</label>
                            <input type="hidden" name="req_student_no" value="{{old('req_student_no', Auth::user()->userid)}}" class="form-control">
                            <br /><div class="card p-2" style="background-color: lightgray">{{Auth::user()->userid}}</div>
                        @endcan

                    </div>

                    <div class="col-12 col-sm-12 col-md-6 mt-2">
                        <label class="control-label">連絡先電話番号</label> <label class="badge badge-danger">必須</label>
                        <input type="text" name="req_phone_no" value="{{old('req_phone_no')}}" class="form-control" placeholder="ハイフンなしで半角数値">
                        @if ($errors && $errors->has('req_phone_no')) <div class="text-danger">{{$errors->first('req_phone_no')}}</div> @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mt-2">
                        <label class="control-label">連絡先メールアドレス</label> <label class="badge badge-danger">必須</label>
                        <input type="text" name="req_email" value="{{old('req_email')}}" class="form-control">
                        @if ($errors && $errors->has('req_email')) <div class="text-danger">{{$errors->first('req_email')}}</div> @endif
                    </div>
                </div>
                    <div class="row">
                    <div class="col-12 col-md-4 mt-2">
                        <label class="control-label">郵送先郵便番号</label> <label class="badge badge-danger">必須</label>
                        <input type="text" name="req_postal_code" value="{{old('req_postal_code')}}" class="form-control" placeholder="ハイフンなしで半角数値">
                        @if ($errors && $errors->has('req_postal_code')) <div class="text-danger">{{$errors->first('req_postal_code')}}</div> @endif
                    </div>
                    <div class="col-12 col-md-8 mt-2">
                        <label class="control-label">郵送先住所</label> <label class="badge badge-danger">必須</label>
                        <input type="text" name="req_address" value="{{old('req_address')}}" class="form-control">
                        @if ($errors && $errors->has('req_address')) <div class="text-danger">{{$errors->first('req_address')}}</div> @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12 mt-2">
                        <label class="control-label">郵送先宛名</label>
                        <input type="text" name="req_mailing_name" value="{{old('req_mailing_name')}}" class="form-control" placeholder="郵送先の宛名がユーザー名と異なる場合は入力してください。">
                        @if ($errors && $errors->has('req_mailing_name')) <div class="text-danger">{{$errors->first('req_mailing_name')}}</div> @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12 text-center">
                        <button type="button" class="btn btn-success mr-3" onclick="location.href='{{url('/')}}{{$page->getLinkUrl()}}'"><i class="fas fa-list"></i> 一覧へ戻る</button>
                        <button type="button" class="btn btn-primary" onclick="javascript:form_requestLent.submit();"><i class="fas fa-check"></i> リクエストする</button>
                    </div>
                </div>
            </form>
        @endif
    </div>
@else
    <div class="alert alert-warning text-center">
        <i class="fas fa-exclamation-circle"></i>
        {{-- 貸し出し操作、返却、貸し出しリクエストはログインすると行えます。 --}}
        貸し出しリクエストはログインすると行えます。
    </div>
    <div class="text-center">
        <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}{{$page->getLinkUrl()}}'"><i class="fas fa-list"></i> 一覧へ戻る</button>
    </div>
@endauth
