{{--
 * 一覧画面テンプレート（tableテンプレートをベース）
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if (empty($setting_error_messages))

    {{-- ヘッダー部分 --}}
    @include('plugins.user.databases.default.databases_include_ctrl_head')

    @if ($default_hide_list)
    @else
        {{-- データのループ --}}
        <table class="table table-bordered">
            <caption class="sr-only">{{$database_frame->databases_name}}</caption>
            <thead class="thead-light">
            <tr>
            @foreach($columns as $column)
                @if($column->list_hide_flag == 0)
                <th>{{$column->column_name}}</th>
                @endif
            @endforeach
            </tr>
            </thead>

            <tbody>
            @foreach($inputs as $input)
            <tr>
                @php
                // bugfix: $loop->firstだと1つ目の項目が、一覧非表示の場合、詳細画面に飛べなくなるため、フラグで対応する
                $is_first = true;
                @endphp

                @foreach($columns as $column)
                    @if($column->list_hide_flag == 0)
                        @if($is_first)
                            <td class="{{$column->classname}}">
                                <a href="{{url('/')}}/plugin/databases/detail/{{$page->id}}/{{$frame_id}}/{{$input->id}}#frame-{{$frame_id}}">
                                    @include('plugins.user.databases.default.databases_include_value')
                                </a>
                            </td>
                            @php
                            $is_first = false;
                            @endphp
                        @else
                            <td class="{{$column->classname}}">
                                @include('plugins.user.databases.default.databases_include_value')
                            </td>
                        @endif
                    @endif
                @endforeach
            </tr>
            @endforeach
            </tbody>
        </table>

        {{-- ページング処理 --}}
        {{-- アクセシビリティ対応。1ページしかない時に、空navを表示するとスクリーンリーダーに不要な Navigation がひっかかるため表示させない。 --}}
        @if ($inputs->lastPage() > 1)
            <nav class="text-center" aria-label="{{$database_frame->databases_name}}のページ付け">
                {{ $inputs->fragment('frame-' . $frame_id)->links() }}
            </nav>
        @endif

    @endif

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
