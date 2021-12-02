{{--
 * 施設登録・変更画面のテンプレート
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設管理
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">

        {{-- 機能選択タブ --}}
        @include('plugins.manage.reservation.reservation_manage_tab')

        {{-- ボタンによってアクション切替 --}}
        <script type="text/javascript">
            function submitAction(url) {
                form_reservation.action = url;
                form_reservation.submit();
            }
            // ツールチップ
            $(function () {
                // 有効化
                $('[data-toggle="tooltip"]').tooltip()
            })
        </script>
    </div>
    <div class="card-body">

        {{-- 共通エラーメッセージ 呼び出し --}}
        @include('plugins.common.errors_form_line')

        <form name="form_reservation" action="" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            @if ($facility->id)
                <div class="form-group form-row">
                    <label class="col-md-3 col-form-label text-md-right">コピーして登録画面へ</label>
                    <div class="col-md-9 d-sm-flex align-items-center">
                        <button type="button" class="btn btn-outline-primary form-horizontal" onclick="submitAction('{{url('/')}}/manage/reservation/copy')">
                            <i class="fas fa-copy "></i> コピー
                        </button>
                    </div>
                </div>
            @endif

            @php
            if ($facility->id) {
                // 更新画面 再表示
                $view_action_url = url('/') . '/manage/reservation/edit/' . $facility->id;
            } else {
                // 登録画面 再表示
                $view_action_url = url('/') . '/manage/reservation/regist';
            }
            @endphp

            <div class="form-group form-row">
                <label class="col-md-3 col-form-label text-md-right pt-0">表示 <span class="fas fa-info-circle" data-toggle="tooltip" title="施設予約カレンダーから当施設を表示するかしないか設定します。"> <span class="badge badge-danger">必須</span></label>
                <div class="col-md-9">
                    {{-- 初期値(空入力)は結果的に 0:表示する --}}
                    @foreach (NotShowType::getMembers() as $enum_value => $enum_label)
                        <div class="custom-control custom-radio custom-control-inline">
                            @if (old('hide_flag', $facility->hide_flag) == $enum_value)
                                <input type="radio" value="{{$enum_value}}" id="hide_flag_{{$enum_value}}" name="hide_flag" class="custom-control-input" checked="checked">
                            @else
                                <input type="radio" value="{{$enum_value}}" id="hide_flag_{{$enum_value}}" name="hide_flag" class="custom-control-input">
                            @endif
                            <label class="custom-control-label" for="hide_flag_{{$enum_value}}">{{$enum_label}}</label>
                        </div>
                    @endforeach
                    @include('plugins.common.errors_inline', ['name' => 'hide_flag'])
                </div>
            </div>

            <div class="form-group form-row">
                <label for="facility_name" class="col-md-3 col-form-label text-md-right">施設名 <span class="badge badge-danger">必須</span></label>
                <div class="col-md-9">
                    <input type="text" name="facility_name" id="facility_name" value="{{old('facility_name', $facility->facility_name)}}" class="form-control @if ($errors->has('facility_name')) border-danger @endif">
                    @include('plugins.common.errors_inline', ['name' => 'facility_name'])
                </div>
            </div>

            <div class="form-group form-row">
                <label for="reservations_categories_id" class="col-md-3 col-form-label text-md-right">施設カテゴリ <span class="badge badge-danger">必須</span></label>
                <div class="col-md-9">
                    <select name="reservations_categories_id" id="reservations_categories_id" class="form-control @if ($errors->has('reservations_categories_id')) border-danger @endif">
                        @foreach ($categories as $category)
                            <option value="{{$category->id}}" @if (old('reservations_categories_id', $facility->reservations_categories_id) == $category->id) selected="selected" @endif>{{$category->category}}</option>
                        @endforeach
                    </select>
                    @include('plugins.common.errors_inline', ['name' => 'reservations_categories_id'])
                </div>
            </div>

            <div class="form-group form-row">
                <label for="columns_set_id" class="col-md-3 col-form-label text-md-right">項目セット <span class="badge badge-danger">必須</span></label>
                <div class="col-md-9">
                    <select name="columns_set_id" id="columns_set_id" class="form-control @if ($errors->has('columns_set_id')) border-danger @endif">
                        <option value=""></option>
                        @foreach ($columns_sets as $columns_set)
                            <option value="{{$columns_set->id}}" @if (old('columns_set_id', $facility->columns_set_id) == $columns_set->id) selected="selected" @endif>{{$columns_set->name}}</option>
                        @endforeach
                    </select>
                    @include('plugins.common.errors_inline', ['name' => 'columns_set_id'])
                    <small class="text-muted">※ 施設予約時に登録する項目セットを選択します。</small>
                </div>
            </div>

            <div class="form-group form-row">
                <label class="col-md-3 col-form-label text-md-right pt-0">重複予約 <span class="badge badge-danger">必須</span></label>
                <div class="col-md-9">
                    {{-- 初期値(空入力)は結果的に 0:許可しない --}}
                    @foreach (PermissionType::getMembers() as $enum_value => $enum_label)
                        <div class="custom-control custom-radio custom-control-inline">
                            @if (old('is_allow_duplicate', $facility->is_allow_duplicate) == $enum_value)
                                <input type="radio" value="{{$enum_value}}" id="is_allow_duplicate_{{$enum_value}}" name="is_allow_duplicate" class="custom-control-input" checked="checked">
                            @else
                                <input type="radio" value="{{$enum_value}}" id="is_allow_duplicate_{{$enum_value}}" name="is_allow_duplicate" class="custom-control-input">
                            @endif
                            <label class="custom-control-label" for="is_allow_duplicate_{{$enum_value}}">{{$enum_label}}</label>
                        </div>
                    @endforeach
                    @include('plugins.common.errors_inline', ['name' => 'is_allow_duplicate'])
                    <div><small class="text-muted">※ 「許可する」を設定した場合、予約時間が重なっていても予約可能になります。</small></div>
                </div>
            </div>

            <div class="form-group form-row">
                <label for="display_sequence" class="col-md-3 col-form-label text-md-right">表示順</label>
                <div class="col-md-9">
                    <input type="text" name="display_sequence" id="display_sequence" value="{{old('display_sequence', $facility->display_sequence)}}" class="form-control @if ($errors->has('display_sequence')) border-danger @endif">
                    @include('plugins.common.errors_inline', ['name' => 'display_sequence'])
                    <small class="text-muted">※ 未指定時は最後に表示されるように自動登録します。</small>
                </div>
            </div>

            <!-- Add or Update reservation Button -->
            <div class="form-group text-center">
                <div class="form-row">
                    <div class="offset-xl-3 col-9 col-xl-6">
                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/')}}/manage/reservation?page={{$paginate_page}}&search_words={{$search_words}}'"><i class="fas fa-times"></i> キャンセル</button>
                        @if ($facility->id)
                        <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="submitAction('{{url('/')}}/manage/reservation/update/{{$facility->id}}')">
                            <i class="fas fa-check"></i> 変更確定
                        </button>
                        @else
                        <button type="button" class="btn btn-primary form-horizontal mr-2" onclick="submitAction('{{url('/')}}/manage/reservation/store')">
                            <i class="fas fa-check"></i> 登録確定
                        </button>
                        @endif
                    </div>

                    @if ($facility->id)
                        <div class="col-3 col-xl-3 text-right">
                            <a data-toggle="collapse" href="#collapse{{$facility->id}}">
                                <span class="btn btn-danger"><i class="fas fa-trash-alt"></i> 削除</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </form>

        <div id="collapse{{$facility->id}}" class="collapse">
            <div class="card border-danger">
                <div class="card-body">
                    <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

                    <div class="text-center">
                        {{-- 削除ボタン --}}
                        <form action="{{url('/')}}/manage/reservation/destroy/{{$facility->id}}" method="POST">
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
