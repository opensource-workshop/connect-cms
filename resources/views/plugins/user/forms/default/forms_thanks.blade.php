{{--
 * 登録完了画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
{!! nl2br($after_message) !!}
@endsection
