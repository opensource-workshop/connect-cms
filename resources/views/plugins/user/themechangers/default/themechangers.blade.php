{{--
 * テーマチェンジャー表示画面
 *
 * @param obj $pages ページデータの配列
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category テーマチェンジャー・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<form action="/redirect/plugin/themechangers/select/{{$page->id}}/{{$frame_id}}" method="POST">
    {{csrf_field()}}
    <div class="form-group">
        <select class="form-control" name="session_theme" class="form-control" onchange="javascript:submit(this.form);">
            <option value="session:clear">元に戻す</option>
            @foreach($themes as $theme)
                @isset($theme['themes'])
                    <optgroup label="{{$theme['dir']}}">
                    @foreach($theme['themes'] as $sub_theme)
                        <option value="{{$sub_theme['dir']}}"@if($sub_theme['dir'] == $page_theme['css']) selected @endif>{{$sub_theme['name']}}</option>
                    @endforeach
                    </optgroup>
                @else
                    <option value="{{$theme['dir']}}"@if($theme['dir'] == $page_theme['css']) selected @endif>{{$theme['name']}}</option>
                @endisset
            @endforeach
        </select>
    </div>
</form>

@endsection
