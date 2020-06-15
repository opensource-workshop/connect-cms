{{--
 * データベース カード テンプレート
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
            //$_columns = $inputs[0]->getColumnsDort($columns);

            //データがない時に〝$inputs〟が存在しないので function が使えない。
            $_columns = json_decode(json_encode($columns, JSON_UNESCAPED_UNICODE, 10), true);
            $_display_sequence = array_column($_columns, 'display_sequence');
            $_id = array_column($_columns, 'id');
            array_multisort( $_display_sequence, SORT_ASC, $_id, SORT_ASC, $_columns );

            // 表示した項目の ID を保存する配列
            $_show = [];

            // リンク（アイテム ID を追加する）
            $_href = url('/').'/plugin/databases/detail/'.$page->id.'/'.$frame_id.'/';
        @endphp

        @include('plugins.user.databases.default.databases_include_ctrl_head') {{--テーブルのヘッダー部分--}}

        @if (!$default_hide_list)
            <div class="db-card"> {{-- テンプレートを複製するときは、ここのクラスを変更する --}}
            
            @foreach($inputs as $input) {{-- データのループ --}}
                <div class="db-adata">
                @if( $_show[] = $imgid = $input->getNumType($_columns, 'image', 1)) {{-- サムネール --}}
                    <a href="{{$_href.$input->id}}" class="main-image {{$_columns[$imgid]['classname']}}">
                        <img src="{{url('/')}}/file/{{$input->getVolue($input_cols, $_columns[$imgid]['id'], 'value')}}" class="img-fluid">
                    </a>
                @endif

                    @php $_show[] = $txtid = $input->getNumType($_columns, 'text', 1); @endphp
                    <h2 class="{{$_columns[$txtid]['classname']}}"> {{-- 項目のタイトル --}}
                        <a href="{{$_href.$input->id}}">
                            {{$input->getVolue($input_cols, $_columns[$txtid]['id'], 'value')}}
                        </a>
                    </h2>

                    <div class="db-contents"> {{-- 項目のループ --}}
                    @foreach($_columns as $_key => $_column)
                    @if(!in_array($_key ,$_show) && !$_column['list_hide_flag']) {{--表示する項目を選択--}}
                        <div class="{{$_column['classname']}}">
                            <h3>{{$_column['column_name']}}</h3>
                            {!!$input->getTagType( $input_cols, $_column )!!}
                        </div>
                    @endif
                    @endforeach
                    </div>

                    <button type="button" class="btn btn-success" onclick="location.href='{{$_href.$input->id}}'">
                        <span>詳細 </span><i class="fas fa-angle-right"></i>
                    </button>
                </div>
            @endforeach
            </div>

            {{-- ページング処理 --}}
            <div class="text-center">
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
