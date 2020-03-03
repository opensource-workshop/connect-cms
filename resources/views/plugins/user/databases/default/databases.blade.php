{{--
 * 登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if (empty($setting_error_messages))

    {{-- 新規登録 --}}
    @can('posts.create',[[null, 'databases', $buckets]])
        <div class="row">
            <p class="text-right col-12">
                {{-- 新規登録ボタン --}}
                <button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}'">
                    <i class="far fa-edit"></i> 新規登録
                </button>
            </p>
        </div>
    @endcan

    {{-- データのループ --}}
    <ul>
    @foreach($inputs as $input)

        @php

        // タイトル
        $title_value = '［無題］';

        // タイトルカラム（display_sequence = 1 のものをタイトルとする）
        $title_columns_id = 0;
        $title_columns = $columns->where('display_sequence', 1)->first();
        if (!empty($title_columns)) {
            $title_columns_id = $title_columns->id;
        }

        // タイトルを探す
        $title_col = $input_cols->where('databases_inputs_id', $input->id)->where('databases_columns_id', $title_columns_id)->first();
        if (!empty($title_col)) {
            $title_value = $title_col->value;
        }

        @endphp

        {{-- タイトルの一覧表示 --}}
        <li><a href="{{url('/')}}/plugin/databases/detail/{{$page->id}}/{{$frame_id}}/{{$input->id}}">{{$title_value}}</a></li>

    @endforeach
    </ul>

@else
    {{-- フレームに紐づくコンテンツがない場合等、表示に支障がある場合は、データ登録を促す等のメッセージを表示 --}}
    <div class="card border-danger">
        <div class="card-body">
            @foreach ($setting_error_messages as $setting_error_message)
                <p class="text-center cc_margin_bottom_0">{{ $setting_error_message }}</p>
            @endforeach
        </div>
    </div>
@endif
@endsection
