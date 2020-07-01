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
        @inject('dbInputs', 'App\Models\User\Databases\DatabasesInputs')
        @php
            // コラム配列（リストで表示するデータのみにする）
            $_columns = $dbInputs->getColumns($columns, 'list');

            // リンク（アイテム ID を追加する）
            $_href = url('/').'/plugin/databases/detail/'.$page->id.'/'.$frame_id.'/';
        @endphp

        @include('plugins.user.databases.single_sheet.databases_include_ctrl_head') {{--テーブルのヘッダー部分--}}

        @if(!$default_hide_list)
            {{-- テンプレートを複製するときは、ここのクラスを変更する --}}
            <div class="db-card"> 
            
            @foreach($inputs as $input) {{-- データのループ --}}
                <div class="db-adata{{$_columns['cls']}}">

                @if( $_columns['thum'] ) {{-- サムネール --}}
                    <a href="{{$_href.$input->id}}" class="main-image {{$_columns['thum']['classname']}}">
                        <img src="{{url('/')}}/file/{{$input->getVolue($input_cols, $_columns['thum']['id'], 'value')}}" class="img-fluid">
                    </a>
                @endif

                @if( $_columns['title'] ) {{-- タイトル --}}
                    <h2 class="{{$_columns['title']['classname']}}"> 
                        <a href="{{$_href.$input->id}}">
                            {{$input->getVolue($input_cols, $_columns['title']['id'], 'value')}}
                        </a>
                    </h2>
                @endif

                    <div class="db-contents"> {{-- 項目のループ --}}
                    @foreach($_columns['item'] as $_key => $_column)
                        <div class="{{$_column['classname']}}">
                            <h3>{{$_column['column_name']}}</h3>
                            {!!$input->getTagType( $input_cols, $_column )!!}
                        </div>
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
