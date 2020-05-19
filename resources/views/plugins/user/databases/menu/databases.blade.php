{{--
 * 登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>, よたか <info@hanamachi.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @copyright Hanamachi All Rights Reserved
 * @category データベース・プラグイン
 --}}

@php $hnm = array(); @endphp {{-- プラグイン用配列 --}}
@extends('core.cms_frame_base')
@section("plugin_contents_$frame->id")
    @if (empty($setting_error_messages))
        @if (!$default_hide_list)
            <div class="db-menu">

            {{-- データのループ --}}
            @foreach($inputs as $input)
                @php
                    $hnm['uri'] = url('/').'/plugin/databases/detail/';
                    $_id = $input->id; //データ ID
                @endphp

                {{-- 項目のループ --}}
                @foreach($columns as $column)
                    @php
                        $_data = array( 'type'=>'', 'title'=>'', 'value'=>'', 'class'=>'', 'orgnm'=>'', 'link'=>'' );

                        if($column->column_name == 'pageid'){ //表示させるページ ID
                            $hnm['pageid'] = $column->classname;
                        }elseif($column->column_name == 'frameid'){ //表示させる フレーム ID
                            $hnm['frameid'] = $column->classname;
                        }

                        if($column->list_hide_flag == 0){
                            $obj = $input_cols->
                                where('databases_inputs_id', $input->id)->
                                where('databases_columns_id', $column->id)->first();

                            if (empty($obj)) { break; } //オブジェクトが存在しない時はブレイク

                            switch( $column->column_type ){ //データのタイプ
                                case 'image':
                                    if( !isset($hnm[$_id]['image']) ){
                                        $_data['type'] = $column->column_type;
                                        $_data['title'] = $column->column_name;
                                        $_data['value'] = '<img src="'.url('/').'/file/'.$obj->value.'" class="img-fluid" />';
                                        if($column->classname){
                                            $_data['class'] = ' class="main-image '.$column->classname.'"';
                                        }else{
                                            $_data['class'] = ' class="main-image"';
                                        }
                                        $_data['orgnm'] = $obj->client_original_name;
                                        $hnm[$_id]['image'] = $_data;
                                    }
                                    break;

                                case 'file':
                                case 'video':
                                    break;

                                case 'text': //文字列を処理する：あとからタイプを追加する
                                case 'textarea': 
                                default:
                                    $_data['type'] = $column->column_type;
                                    $_data['title'] = $column->column_name;
                                    $_data['value'] = $obj->value;
                                    $_data['class'] = 'class="'.$column->classname.'"';

                                    if($column->classname){ $_data['class'] = ' class="'.$column->classname.'"'; }
                                    $_data['orgnm'] = $obj->client_original_name;

                                    if( !isset($hnm[$_id]['title']) ){ //最初のテキストをタイトルとして扱う
                                        $hnm[$_id]['title'] = $_data;
                                        $_data = array();

                                    }elseif( !isset($hnm[$_id]['text']) ){ //２番目のテキストを説明として扱う
                                        $hnm[$_id]['text'] = $_data;
                                        $_data = array();
                                    }
                                    break;
                            }
                        }
                    @endphp
                @endforeach

                @php
                    $_href = 'href="'.$hnm['uri'].$hnm['pageid'].'/'.$hnm['frameid'].'/'.$_id.'"';
                @endphp

                @if(isset($hnm[$_id]['image']))
                <div class="db-adata">
                    <a {!!$_href!!}{!!$hnm[$_id]['image']['class']!!}>{!!$hnm[$_id]['image']['value']!!}</a>
                @else
                 <div class="db-adata no-image">
                @endif
                    <dl>
                        <dt {!!$hnm[$_id]['title']['class']!!}>
                            <a {!!$_href!!}>{!!$hnm[$_id]['title']['value']!!}</a>
                        </dt>
                        <dd {!!$hnm[$_id]['text']['class']!!}>
                            <a {!!$_href!!}>{!!$hnm[$_id]['text']['value']!!}</a>
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
