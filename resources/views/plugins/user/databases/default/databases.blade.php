{{--
 * データベース デフォルト テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author よたか <info@hanamachi.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @copyright Hanamachi All Rights Reserved
 * @category データベース・プラグイン
 --}}

@extends('core.cms_frame_base')
@section("plugin_contents_$frame->id")
    @if (empty($setting_error_messages))
        @php
            // コラム配列（DB へのアクセスを減らすために 配列にしておく）
            $_columns = $inputs[0]->getColumnsDort($columns);

            // リンク（アイテム ID を追加して使用する）
            $_href = url('/').'/plugin/databases/detail/'.$page->id.'/'.$frame_id.'/';
        @endphp

        @include('plugins.user.databases.default.databases_include_ctrl_head') {{--テーブルのヘッダー部分--}}

        @if (!$default_hide_list)
        {{-- データのループ --}}
        <div class="db-default container">
            <div class="d-md-table">
                <dl class="d-none d-md-table-row text-center"> {{--テーブルのタイトル--}}
                    @foreach($_columns as $_column) {{--項目を繰り返してタイトルをつける--}}
                    @if(!$_column['list_hide_flag']) {{--表示する項目を選択--}}
                        <dt class="d-table-cell text-nowrap p-2 {{$_column['classname']}}">
                            {{$_column['column_name']}}
                        </dt>
                    @endif
                    @endforeach
                </dl>

                @foreach($inputs as $input)
                <dl class="d-md-table-row"> {{--テーブルのコンテンツ--}}
                    @php $_first_flag = 1; @endphp {{--最初の項目を選ぶフラグ--}}
                    
                    @foreach($_columns as $_column){{--項目を繰り返す--}}
                        @if($_column['list_hide_flag']) {{--表示しない項目--}}

                        @elseif($_first_flag) {{--最初の項目--}}
                        <dt class="d-md-table-cell p-2 type-{{$_column['column_type']}} {{$_column['classname']}}">
                            <a href="'{{$_href.$input->id}}">
                                {{$input->getTagType( $input_cols, $_column, 1)}}
                            </a>
                        </dt>
                        @php $_first_flag=0; @endphp {{--フラグを倒す--}}

                        @elseif( $_column['column_type'] == 'image' ) {{--イメージ項目--}}
                        <dd class="d-md-table-cell p-2 type-{{$_column['column_type']}} {{$_column['classname']}}">
                            <a href="'{{$_href.$input->id}}">
                                {!!$input->getTagType( $input_cols, $_column, 1)!!}
                            </a>
                        </dd>

                        @elseif( $_column['column_type'] == 'video') {{--ビデオ項目--}}
                        <dd class="d-md-table-cell p-2 type-{{$_column['column_type']}} {{$_column['classname']}}">
                            <h3 class="d-md-none">{{$_column['column_name']}}</h3>
                            {!!$input->getTagType( $input_cols, $_column, 1)!!}
                        </dd>

                        @else {{--テキスト項目--}}
                        <dd class="d-md-table-cell p-2 type-{{$_column['column_type']}} {{$_column['classname']}}">
                            <h3 class="d-md-none">{{$_column['column_name']}}</h3>
                            <p>{!!$input->getTagType( $input_cols, $_column, 1)!!}</p>
                        </dd>
                        @endif
                    @endforeach
                </dl>
                @endforeach
            </div>
        </div>
        
        <div class="text-center"> {{-- ページング処理 --}}
            {{ $inputs->links() }}
        </div>
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
