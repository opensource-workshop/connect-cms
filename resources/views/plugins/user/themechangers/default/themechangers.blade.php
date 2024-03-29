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

<form action="{{url('/')}}/redirect/plugin/themechangers/select/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="POST" aria-label="テーマチェンジャー">
    <fieldset>
        <legend class="sr-only">一時的にテーマを変更</legend>
        {{csrf_field()}}
        <div class="form-group mb-0">
            <p>テーマを一時的に変更して、サイトの見た目や配色を確認する事ができます。</p>
            <select class="form-control" name="session_theme" title="テーマの選択">
                <option value="session:clear">元に戻す</option>
                @foreach($themes as $theme)
                    @isset($theme['themes'])
                        <optgroup label="{{$theme['name']}}">
                        @foreach($theme['themes'] as $sub_theme)
                            <option value="{{$sub_theme['dir']}}"@if($sub_theme['dir'] == $page_theme['css']) selected @endif>{{$sub_theme['name']}}</option>
                        @endforeach
                        </optgroup>
                    @else
                        <option value="{{$theme['dir']}}"@if($theme['dir'] == $page_theme['css']) selected @endif>{{$theme['name']}}</option>
                    @endisset
                @endforeach
            </select>

            <div class="custom-control custom-checkbox mt-2">
                <input type="checkbox" name="session_header_black" value="1" class="custom-control-input" id="session_header_black" @if(old('session_header_black', $session_header_black)) checked=checked @endif>
                <label class="custom-control-label" for="session_header_black">ヘッダーは黒にする</label>
            </div>

            <div class="text-center mt-2">
                <button type="submit" class="btn btn-primary" id="themechanger_button{{$frame->id}}">
                    <i class="fas fa-check"></i> 変更
                </button>
            </div>

        </div>
    </fieldset>
</form>

@endsection
