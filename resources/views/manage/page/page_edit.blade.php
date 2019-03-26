{{--
 * Page 編集画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('manage.manage')

{{-- 管理画面メイン部分への挿入 --}}
@section('manage_content')

{{-- 編集画面(入力フォーム) --}}
@include('manage.page.page_form')

{{-- 削除画面(入力フォーム) --}}
<div class="panel panel-danger">
	<div class="panel-heading">
		ページ削除
	</div>
	<div class="panel-body">

		<form action="{{url('/manage/page/destroy')}}/{{$page->id}}" method="POST" class="form-horizontal">
			{{ csrf_field() }}
			ページを削除します。<br />
			元に戻すことはできないため、よく確認して実行してください。<br />
			<div class="form-group pull-right col-md-2">
				<button type="submit" class="btn btn-danger form-horizontal" onclick="javascript:return confirm('ページを削除します。\nよろしいですか？')">
					ページ削除
				</button>
			</div>
		</form>
	</div>
</div>

@endsection
