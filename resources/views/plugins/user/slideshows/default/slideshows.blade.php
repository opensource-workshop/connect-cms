{{--
 * 公開画面
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スライドショー・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

@include('common.errors_form_line')

<fieldset>
    <legend class="sr-only">{{$slideshow->slideshows_name}}</legend>
    {{ csrf_field() }}

</fieldset>
@endsection
