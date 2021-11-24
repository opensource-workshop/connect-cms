{{--
 * テーマ管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category テーマ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card mb-3">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.theme.theme_manage_tab')
    </div>
</div>

@if ($theme_css)
<!--  テーマジェネレータ確認用CSSエリア  -->
<style>
{!! $theme_css !!}
</style>
@endif

<script>
    $(function() {
        $('select#theme_set').change(function() {
            let select_themeset = $(this).val();
            // PHP変数から取得
            let app = {!! json_encode($theme_set) !!};
            let themeset = app[select_themeset];
            let targets = [
                'menu_horizon',
                'menu_vertical',
                'frame_tittle',
            ];
            let attrs = [
                'color',
                'background',
                'border',
                'background_image',
            ]
            let id = '';
            let attr = '';
            let vals = [];
            $.each(targets, function(i, target) {
                $.each(attrs, function(ii, attr) {
                    vals = themeset[target].split('|');
                    id = '#' + target + '_' + attr;
                    $(id).val(vals[ii]);
                    if ($(id).find('option:selected').length == false){
                        // 値が選択肢にない場合は一番上にする
                        $(id).prop("selectedIndex", 0);
                    }
                })
            })
            $("#btn-confirm").trigger("click");
        });
    });
</script>
<div class="card">
    <div class="card-header">カスタムテーマ生成</div>
    <div class="card-body">
        <form action="{{url('/')}}/manage/theme/generate" method="POST">
            {{csrf_field()}}

            {{-- ディレクトリ名 --}}
            <div class="form-group row">
                <label for="dir_name" class="col-md-2 col-form-label text-md-right">ディレクトリ名</label>
                <div class="col-md-10">
                    @if ($errors || $theme_css)
                    <input type="text" name="dir_name" id="dir_name" value="{{old('dir_name', '')}}" class="form-control">
                    @else
                    <input type="text" name="dir_name" id="dir_name" value="" class="form-control">
                    @endif
                    @if ($errors && $errors->has('dir_name')) <div class="text-danger">{{$errors->first('dir_name')}}</div> @endif
                </div>
            </div>
            {{-- テーマ名 --}}
            <div class="form-group row">
                <label for="theme_name" class="col-md-2 col-form-label text-md-right">テーマ名</label>
                <div class="col-md-10">
                    @if ($errors || $theme_css)
                    <input type="text" name="theme_name" id="theme_name" value="{{old('theme_name', '')}}" class="form-control">
                    @else
                    <input type="text" name="theme_name" id="theme_name" value="" class="form-control">
                    @endif
                    @if ($errors && $errors->has('theme_name')) <div class="text-danger">{{$errors->first('theme_name')}}</div> @endif
                </div>
            </div>
            {{-- テーマセット --}}
            <div class="form-group row">
                    <label for="theme_set" class="col-md-2 col-form-label text-md-right">テーマセット</label>
                    <div class="col-md-10">
                        <select name="theme_set" id="theme_set" class="form-control">
                            <option value=""@if(old('theme_set', $theme_set) == '') selected @endif></option>
                            @foreach($theme_set as $key => $theme)
                            <option value="{{$key}}"@if(old('theme_set', $theme_set) == "$key") selected @endif>{{$key}}</option>
                            @endforeach
                        </select>
                    </div>
            </div>

            <!-- サンプル出力用 -->
            <div class="row">
                <div class="p-0 col-12 frame-4 plugin-menus  menus-dropdown" id="sample_menu_horizon">
                    <div class="">
                        <div class="card mb-3  border-0">
                            <div class="card-body clearfix p-0">
                                <nav aria-label="タブメニュー">
                                    <ul class="nav nav-tabs nav-justified d-none d-md-flex">
                                        <li class="nav-item ">
                                            <a class="nav-link text-nowrap active" href="" aria-current="page">トップページ</a>
                                        </li>
                                        <li class="nav-item ">
                                            <a class="nav-link text-nowrap" href="">セカンドページ</a>
                                        </li>
                                        <li class="nav-item dropdown ">
                                            <a class="nav-link dropdown-toggle" href="">階層ページ<span class="caret"></span></a>
                                        </li>
                                        <li class="nav-item ">
                                            <a class="nav-link text-nowrap" href="">サードページ</a>
                                        </li>
                                        <li class="nav-item ">
                                            <a class="nav-link text-nowrap" href="">フォースページ</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="row col-lg-3">
                    <div class="p-0 col-12 plugin-menus menus-default" id="sample_menu_vertical">
                        <div class="container" style="width:255px;">
                            <div class="card mb-3 border-0 frame-design-none">
                                <div class="card-body clearfix p-0">
                                    <div class="list-group mb-0" role="navigation" aria-label="メニュー">
                                        <a href="" class="list-group-item active" aria-current="page">トップページ</a>
                                        <a href="" class="list-group-item">セカンドページ</a>
                                        <a href="" class="list-group-item depth-0">階層ページ</a>
                                        <a href="" class="list-group-item depth-1"><i class="fas fa-chevron-right"></i>階層ページ１</a>
                                        <a href="" class="list-group-item depth-1"><i class="fas fa-chevron-right"></i>階層ページ２</a>
                                        <a href="" class="list-group-item">サードページ</a>
                                        <a href="" class="list-group-item">フォースページ</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row col-lg-9">
                    <div id="ccMainArea" style="width: 100%;"><!-- CSS確認用に擬似id(ccMainArea)を入力 -->
                        <div class="p-0 col-12 plugin-contents  contents-default" id="sample_frame_tittle">
                            <div class="container">
                                <div class="card mb-3">
                                    <h1 class="card-header bg-default cc-default-font-color">フレームタイトル</h1>
                                    <div class="card-body">
                                        <p><a href="">サンプルリンクです。</a></p>
                                        <p>サンプル固定記事です。</p>
                                        <p>サンプル固定記事です。</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
            <div class="form-group row menu_horizon col-md-4">
                    <div class="col-md-12">横型メニュー</div>
                    <label for="menu_horizon_color" class="col-md-4 col-form-label text-md-right">文字色</label>
                    <div class="col-md-8">
                    <select name="menu_horizon_color" id="menu_horizon_color" class="form-control">
                    @foreach($colors as $color_name => $code)
                        <option value="{{$color_name}}"@if(old('menu_horizon_color', $menu_horizon_color) == $color_name) selected @endif>{{$color_name}}</option>
                    @endforeach
                    </select>
                    </div>
                    <label for="menu_horizon_background" class="col-md-4 col-form-label text-md-right">背景色</label>
                    <div class="col-md-8">
                    <select name="menu_horizon_background" id="menu_horizon_background" class="form-control">
                        <option value="none"@if(old('menu_horizon_background', $menu_horizon_background) == 'none') selected @endif>無し</option>
                    @foreach($colors as $color_name => $code)
                        <option value="{{$color_name}}"@if(old('menu_horizon_background', $menu_horizon_background) == $color_name) selected @endif>{{$color_name}}</option>
                    @endforeach
                    </select>
                    </div>
                    <label for="menu_horizon_border" class="col-md-4 col-form-label text-md-right">枠線</label>
                    <div class="col-md-8">
                    <select name="menu_horizon_border" id="menu_horizon_border" class="form-control">
                    @foreach($borders as $borderval => $border_lang)
                        <option value="{{$borderval}}"@if(old('menu_horizon_border', $menu_horizon_border) == $borderval) selected @endif>{{$borderval}}</option>
                    @endforeach
                    </select>
                    </div>
                    <label for="menu_horizon_background_image" class="col-md-4 col-form-label text-md-right">背景パターン</label>
                    <div class="col-md-8">
                    <select name="menu_horizon_background_image" id="menu_horizon_background_image" class="form-control">
                    <option value="none"@if(old('menu_horizon_background_image', $menu_horizon_background_image) == 'none') selected @endif>非表示</option>
                    <option value="clear"@if(old('menu_horizon_background_image', $menu_horizon_background_image) == 'clear') selected @endif>クリア</option>
                    <option value="craft"@if(old('menu_horizon_background_image', $menu_horizon_background_image) == 'craft') selected @endif>クラフト</option>
                    <option value="ledge"@if(old('menu_horizon_background_image', $menu_horizon_background_image) == 'ledge') selected @endif>レッジ</option>
                    <option value="shiny"@if(old('menu_horizon_background_image', $menu_horizon_background_image) == 'shiny') selected @endif>ピカピカ</option>
                    <option value="stitch"@if(old('menu_horizon_background_image', $menu_horizon_background_image) == 'stitch') selected @endif>ステッチ</option>
                    <option value="washed"@if(old('menu_horizon_background_image', $menu_horizon_background_image) == 'washed') selected @endif>ウォッシュ</option>
                    <option value="underline"@if(old('menu_horizon_background_image', $menu_horizon_background_image) == 'underline') selected @endif>下線</option>
                    </select>
                    </div>
                </div>
                
                <div class="form-group row menu_vertical col-md-4">
                    <div class="col-md-12">縦型メニュー</div>
                    <label for="menu_vertical_color" class="col-md-4 col-form-label text-md-right">文字色</label>
                    <div class="col-md-8">
                    <select name="menu_vertical_color" id="menu_vertical_color" class="form-control">
                    @foreach($colors as $color_name => $code)
                        <option value="{{$color_name}}"@if(old('menu_vertical_color', $menu_vertical_color) == $color_name) selected @endif>{{$color_name}}</option>
                    @endforeach
                    </select>
                    </div>
                    <label for="menu_vertical_background" class="col-md-4 col-form-label text-md-right">背景色</label>
                    <div class="col-md-8">
                    <select name="menu_vertical_background" id="menu_vertical_background" class="form-control">
                        <option value="none"@if(old('menu_vertical_background', $menu_vertical_background) == 'none') selected @endif>無し</option>
                    @foreach($colors as $color_name => $code)
                        <option value="{{$color_name}}"@if(old('menu_vertical_background', $menu_vertical_background) == $color_name) selected @endif>{{$color_name}}</option>
                    @endforeach
                    </select>
                    </div>
                    <label for="menu_vertical_border" class="col-md-4 col-form-label text-md-right">枠線</label>
                    <div class="col-md-8">
                    <select name="menu_vertical_border" id="menu_vertical_border" class="form-control">
                    @foreach($borders as $borderval => $border_lang)
                        <option value="{{$borderval}}"@if(old('menu_vertical_border', $menu_vertical_border) == $borderval) selected @endif>{{$borderval}}</option>
                    @endforeach
                    </select>
                    </div>
                    <label for="menu_vertical_background_image" class="col-md-4 col-form-label text-md-right">背景パターン</label>
                    <div class="col-md-8">
                    <select name="menu_vertical_background_image" id="menu_vertical_background_image" class="form-control">
                    <option value="none"@if(old('menu_vertical_background_image', $menu_vertical_background_image) == 'none') selected @endif>非表示</option>
                    <option value="circle"@if(old('menu_vertical_background_image', $menu_vertical_background_image) == 'circle') selected @endif>サークル</option>
                    <option value="clear"@if(old('menu_vertical_background_image', $menu_vertical_background_image) == 'clear') selected @endif>クリア</option>
                    <option value="craft"@if(old('menu_vertical_background_image', $menu_vertical_background_image) == 'craft') selected @endif>クラフト</option>
                    <option value="shiny"@if(old('menu_vertical_background_image', $menu_vertical_background_image) == 'shiny') selected @endif>ピカピカ</option>
                    <option value="stitch"@if(old('menu_vertical_background_image', $menu_vertical_background_image) == 'stitch') selected @endif>ステッチ</option>
                    <option value="underline"@if(old('menu_vertical_background_image', $menu_vertical_background_image) == 'underline') selected @endif>下線</option>
                    </select>
                    </div>
                </div>

                <div class="form-group row frame_tittle col-md-4">
                    <div class="col-md-12">フレームタイトル</div>
                    <label for="frame_tittle_color" class="col-md-4 col-form-label text-md-right">文字色</label>
                    <div class="col-md-8">
                        <select name="frame_tittle_color" id="frame_tittle_color" class="form-control">
                        @foreach($colors as $color_name => $code)
                            <option value="{{$color_name}}"@if(old('frame_tittle_color', $frame_tittle_color) == $color_name) selected @endif>{{$color_name}}</option>
                        @endforeach
                        </select>
                    </div>
                    <label for="frame_tittle_background" class="col-md-4 col-form-label text-md-right">背景色</label>
                    <div class="col-md-8">
                        <select name="frame_tittle_background" id="frame_tittle_background" class="form-control">
                            <option value="none"@if(old('frame_tittle_background', $frame_tittle_background) == 'none') selected @endif>無し</option>
                        @foreach($colors as $color_name => $code)
                            <option value="{{$color_name}}"@if(old('frame_tittle_background', $frame_tittle_background) == $color_name) selected @endif>{{$color_name}}</option>
                        @endforeach
                        </select>
                    </div>
                    <label for="frame_tittle_border" class="col-md-4 col-form-label text-md-right">枠線</label>
                    <div class="col-md-8">
                        <select name="frame_tittle_border" id="frame_tittle_border" class="form-control">
                        @foreach($borders as $borderval => $border_lang)
                            <option value="{{$borderval}}"@if(old('frame_tittle_border', $frame_tittle_border) == $borderval) selected @endif>{{$borderval}}</option>
                        @endforeach
                        </select>
                    </div>
                    <label for="frame_tittle_background_image" class="col-md-4 col-form-label text-md-right">背景パターン</label>
                    <div class="col-md-8">
                        <select name="frame_tittle_background_image" id="frame_tittle_background_image" class="form-control">
                            <option value="none"@if(old('frame_tittle_background_image', $frame_tittle_background_image) == 'none') selected @endif>非表示</option>
                            <option value="circle"@if(old('frame_tittle_background_image', $frame_tittle_background_image) == 'circle') selected @endif>サークル</option>
                            <option value="rectangle"@if(old('frame_tittle_background_image', $frame_tittle_background_image) == 'rectangle') selected @endif>長方形</option>
                            <option value="craft"@if(old('frame_tittle_background_image', $frame_tittle_background_image) == 'craft') selected @endif>クラフト</option>
                            <option value="shiny"@if(old('frame_tittle_background_image', $frame_tittle_background_image) == 'shiny') selected @endif>ピカピカ</option>
                            <option value="stitch"@if(old('frame_tittle_background_image', $frame_tittle_background_image) == 'stitch') selected @endif>ステッチ</option>
                            <option value="center"@if(old('frame_tittle_background_image', $frame_tittle_background_image) == 'center') selected @endif>中央</option>
                            <option value="ribbon"@if(old('frame_tittle_background_image', $frame_tittle_background_image) == 'ribbon') selected @endif>リボン</option>
                            <option value="balloon"@if(old('frame_tittle_background_image', $frame_tittle_background_image) == 'balloon') selected @endif>吹き出し</option>
                            <option value="emphasis"@if(old('frame_tittle_background_image', $frame_tittle_background_image) == 'emphasis') selected @endif>強調</option>
                            <option value="underline"@if(old('frame_tittle_background_image', $frame_tittle_background_image) == 'underline') selected @endif>下線</option>
                        </select>
                    </div>
                </div>


                <div class="form-group row font_family col-md-4">
                    <div class="col-md-12">書体（font-family）</div>
                    <label for="font_family" class="col-md-4 col-form-label text-md-right">フォント</label>
                    <div class="col-md-8">
                        <select name="font_family" id="font_family" class="form-control">
                        @foreach($fontfamilys as $fontfamilyname => $fontfamilycode)
                            <option value="{{$fontfamilycode}}"@if(old('font_family', $font_family) == $fontfamilycode) selected @endif>{{$fontfamilyname}}</option>
                        @endforeach
                        </select>
                    </div>
                </div>
            </div>


            <div class="form-group row">
                <div class="offset-sm-3 col-sm-6">
　　                <button id="btn-confirm" type="submit" name="confirm" class="btn btn-success form-horizontal"><i class="far fa-edit"></i>確認</button>
                    <button type="submit" name="done" class="btn btn-primary form-horizontal"><i class="fas fa-check"></i> 新規作成</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
