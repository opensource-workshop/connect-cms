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

            // メニュー用のリンク（アイテム ID を追加する）
            //$_href = $inputs[0]->getPageFrameLink($databases_frames, $page->id, $frame->id);

            //データがない時に〝$inputs〟が存在しないので function が使えない。
            $_obj = $databases_frames->where( 'frames_id', $frame->id )
                ->select( 'view_page_id', 'view_frame_id' )->first();
                
            $pageid = $page->id;
            $frameid = $frame->id;

            if( isset($_obj->view_page_id) && isset($_obj->view_frame_id) && $_obj->view_page_id && $_obj->view_frame_id ){
                if( $_obj->view_page_id != $pageid ){
                    $pageid = $_obj->view_page_id;
                }
                if( $_obj->view_frame_id != $frameid ){
                    $frameid = $_obj->view_frame_id;
                }
            }
            $_href = url('/').'/plugin/databases/detail/'.$pageid.'/'.$frameid.'/';
        @endphp

        @if (!$default_hide_list)
            <div class="db-menu">
            
            @foreach($inputs as $input) {{-- データのループ --}}
                @if( $_show[] = $imgid = $input->getNumType($_columns, 'image', 1))
                <div class="db-adata"> {{-- サムネール付メニュー --}}
                    <a href="{{$_href.$input->id}}" class="main-image {{$_columns[$imgid]['classname']}}">
                        <img src="{{url('/')}}/file/{{$input->getVolue($input_cols, $_columns[$imgid]['id'], 'value')}}" class="img-fluid">
                    </a>
                @else
                <div class="db-adata no-image"> {{-- テキストメニュー --}}
                @endif
                    <dl>
                        @php $_show[] = $txtid = $input->getNumType($_columns, 'text', 1) @endphp
                        <dt class="{{$_columns[$txtid]['classname']}}">
                            <a href="{{$_href.$input->id}}">
                                {{$input->getVolue($input_cols, $_columns[$txtid]['id'], 'value')}}
                            </a>
                        </dt>

                        @php $_show[] = $txtid = $input->getNumType($_columns, 'text', 2) @endphp
                        <dd class="{{$_columns[$txtid]['classname']}}">
                            <a href="{{$_href.$input->id}}">
                                {{$input->getVolue($input_cols, $_columns[$txtid]['id'], 'value')}}
                            </a>
                        </dd>

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
