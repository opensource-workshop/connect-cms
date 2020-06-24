{{--
* データベース メニュー テンプレート
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
            // コラム配列（メニューに表示するデータのみにする）
            $__columns = $dbInputs->getColumnsDort($columns, '');
            $_columns = $dbInputs->getColumnsDort($columns, 'list');

            // メニュー用のリンク（アイテム ID を追加する）
            $_href = $dbInputs->getPageFrameLink($databases_frames, $page->id, $frame->id);
        @endphp

        @if (!$default_hide_list)
            <div class="db-menu">
            @foreach($inputs as $input) {{-- データのループ --}}
                <div class="db-adata{{$_columns['catchcls']}}">

                @if( $_columns['thum'] ) {{-- サムネール --}}
                    <a href="{{$_href.$input->id}}" class="main-image {{$_columns['thum']['classname']}}">
                        <img src="{{url('/')}}/file/{{$input->getVolue($input_cols, $_columns['thum']['id'], 'value')}}" class="img-fluid">
                    </a>
                @endif

                    <dl>
                    @if( $_columns['title'] ) {{-- タイトル --}}
                        <dt class="{{$_columns['title']['classname']}}"> 
                            <a href="{{$_href.$input->id}}">
                                {{$input->getVolue($input_cols, $_columns['title']['id'], 'value')}}
                            </a>
                        </dt>
                    @endif

                    @if( $_columns['catch'] ) {{-- キャッチ --}}
                        <dd class="{{$_columns['catch']['classname']}}">
                            <a href="{{$_href.$input->id}}">
                                {{$input->getVolue($input_cols, $_columns['catch']['id'], 'value')}}
                            </a>
                        </dd>
                    @endif
                    </dl>
                </div>
            @endforeach

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
