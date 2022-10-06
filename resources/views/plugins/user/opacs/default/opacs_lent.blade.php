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
            <script>
                $(function () {
                    /**
                     * カレンダーボタン押下
                     */
                    $('#delivery_request_date{{$frame_id}}').datetimepicker({
                        format: 'YYYY-MM-DD',
                        timepicker:false,
                        dayViewHeaderFormat: 'YYYY MMM',
                    });
                });
            </script>

            <form action="{{url('/')}}/plugin/opacs/requestLent/{{$page->id}}/{{$frame_id}}/{{$opacs_books_id}}#frame-{{$frame_id}}" id="form_requestLent" name="form_requestLent" method="POST">
                {{ csrf_field() }}
                <div class="form-row">

                    {{-- 学籍番号/教職員番号　通常ユーザーは自ログインID固定 --}}
                    <div class="form-group col-md-6">
                        @can("role_article")
                            {{-- 権限ありの場合 --}}
                            <label class="control-label">学籍番号/教職員番号</label> <label class="badge badge-danger">必須</label>
                            <input type="text" name="req_student_no" value="{{old('req_student_no')}}" class="form-control @if ($errors && $errors->has("req_student_no")) border-danger @endif">
                            @include('plugins.common.errors_inline', ['name' => 'req_student_no'])
                        @else
                            {{-- 権限なしの場合 --}}
                            <label class="control-label">学籍番号/教職員番号</label>
                            <input type="hidden" name="req_student_no" value="{{old('req_student_no', Auth::user()->userid)}}">
                            <br /><div class="card p-2" style="background-color: lightgray">{{Auth::user()->userid}}</div>
                        @endcan
                    </div>

                    <div class="form-group col-md-6">
                        <label class="control-label">連絡先電話番号</label> <label class="badge badge-danger">必須</label>
                        <input type="text" name="req_phone_no" value="{{old('req_phone_no')}}" class="form-control @if ($errors && $errors->has("req_phone_no")) border-danger @endif" placeholder="ハイフンなしで半角数値">
                        @include('plugins.common.errors_inline', ['name' => 'req_phone_no'])
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label">連絡先メールアドレス</label> <label class="badge badge-danger">必須</label>
                    <input type="text" name="req_email" value="{{old('req_email')}}" class="form-control @if ($errors && $errors->has("req_email")) border-danger @endif">
                    @include('plugins.common.errors_inline', ['name' => 'req_email'])
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="control-label">郵送先郵便番号</label> <label class="badge badge-danger">必須</label>
                        <input type="text" name="req_postal_code" value="{{old('req_postal_code')}}" class="form-control @if ($errors && $errors->has("req_postal_code")) border-danger @endif" placeholder="ハイフンなしで半角数値">
                        @include('plugins.common.errors_inline', ['name' => 'req_postal_code'])
                    </div>
                    <div class="form-group col-md-8">
                        <label class="control-label">郵送先住所</label> <label class="badge badge-danger">必須</label>
                        <input type="text" name="req_address" value="{{old('req_address')}}" class="form-control @if ($errors && $errors->has("req_address")) border-danger @endif">
                        @include('plugins.common.errors_inline', ['name' => 'req_address'])
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label">郵送先宛名</label>
                    <input type="text" name="req_mailing_name" value="{{old('req_mailing_name')}}" class="form-control @if ($errors && $errors->has("req_mailing_name")) border-danger @endif" placeholder="郵送先の宛名がユーザー名と異なる場合は入力してください。">
                    @include('plugins.common.errors_inline', ['name' => 'req_mailing_name'])
                </div>

                <div class="form-row">
                    <div class="col-12">
                        <label class="control-label">配送希望</label>
                    </div>
                    <div class="form-group col-12">
                        @foreach (DeliveryRequestFlag::getMembers() as $enum_value => $enum_label)
                            <div class="custom-control custom-radio custom-control-inline">
                                @if ($enum_value === DeliveryRequestFlag::no)
                                    <input type="radio" value="{{$enum_value}}" id="delivery_request_flag_{{$enum_value}}" name="delivery_request_flag" class="custom-control-input" data-toggle="collapse" data-target="#collapse_delivery_request_on.show" aria-expanded="false" aria-controls="collapse_delivery_request_on" @if (old('delivery_request_flag') == $enum_value) checked="checked" @endif>
                                @else
                                    <input type="radio" value="{{$enum_value}}" id="delivery_request_flag_{{$enum_value}}" name="delivery_request_flag" class="custom-control-input" data-toggle="collapse" data-target="#collapse_delivery_request_on:not(.show)" aria-expanded="false" aria-controls="collapse_delivery_request_on" @if (old('delivery_request_flag') == $enum_value) checked="checked" @endif>
                                @endif
                                <label class="custom-control-label" for="delivery_request_flag_{{$enum_value}}">{{$enum_label}}</label>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- 配送希望ありの場合のみ表示、他は隠す --}}
                <div class="collapse" id="collapse_delivery_request_on">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="control-label">配送希望日</label> <label class="badge badge-danger">必須</label>
                            <div class="input-group" id="delivery_request_date{{$frame_id}}" data-target-input="nearest">
                                <input class="form-control datetimepicker-input @if ($errors && $errors->has('delivery_request_date')) border-danger @endif" type="text" name="delivery_request_date" value="{{old('delivery_request_date')}}" data-target="#delivery_request_date{{$frame_id}}">
                                <div class="input-group-append" data-target="#delivery_request_date{{$frame_id}}" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="far fa-clock"></i></div>
                                </div>
                            </div>
                            @include('plugins.common.errors_inline', ['name' => 'delivery_request_date'])
                            <small class="text-muted">{{$opacs_configs['delivery_request_date_caption']}}</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="control-label">配送希望時間</label> <label class="badge badge-danger">必須</label>
                            <select name="delivery_request_time" class="custom-select @if ($errors && $errors->has("delivery_request_time")) border-danger @endif">
                                <option value=""></option>
                                @foreach($opacs_configs_selects as $select)
                                    <option value="{{$select->value}}" @if (old('delivery_request_time') == $select->value) selected="selected" @endif>{{$select->value}}</option>
                                @endforeach
                            </select>
                            @include('plugins.common.errors_inline', ['name' => 'delivery_request_time'])
                        </div>
                    </div>
                </div>

                <div class="form-group form-row">
                    <div class="col-12 text-center">
                        <button type="button" class="btn btn-success mr-3" onclick="location.href='{{url('/')}}{{$page->getLinkUrl()}}'"><i class="fas fa-list"></i> 一覧へ戻る</button>
                        <button type="button" class="btn btn-primary" onclick="javascript:form_requestLent.submit();"><i class="fas fa-check"></i> リクエストする</button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    {{-- 初期状態で開くもの --}}
    @if(old("delivery_request_flag") == 1)
        <script>
            $('#collapse_delivery_request_on').collapse('show')
        </script>
    @endif
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
