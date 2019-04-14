{{--
	管理画面のメインテンプレート
 --}}
{{-- ベース画面 --}}
@extends('layouts.app')

{{-- 管理画面メイン部分への挿入 --}}
@section('content')

<div class="container">
	<div class="row">
		<div class="col-sm-9 col-sm-push-3">

<?php
//	PHPでクラスを呼ぶ際のサンプル
//	$class_name = "App\ManagePlugins\PageManager\PageManager";


//	$class_name = "App\ManagePlugins\\" . $manage_class . "\\" . $manage_class;
//	$PageManager = new $class_name;
//	$method = "init";
//	echo $PageManager->$method(app('request'));

?>

			{{-- 管理画面各プラグインの画面内容 --}}
			@yield('manage_content')

		</div>

		{{-- 管理メニュー --}}
		<div class="col-sm-3 col-sm-pull-9">
			@include('plugins.manage.menus')
		</div>

	</div>{{-- /row --}}
</div>{{-- /container --}}
@endsection
