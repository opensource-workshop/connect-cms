{{--
 * Page 編集画面(入力フォーム)
 *
 * 新規登録画面と変更画面を共有して使用しています。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 --}}
<div class="panel panel-default">
	<div class="panel-heading">
		@if ($page->id)ページ更新 @else ページ追加 @endif
	</div>
	<div class="panel-body">

		<!-- Display Validation Errors -->
		@include('common.errors')

		@if ($page->id)
			<form action="{{url('/manage/page/update')}}/{{$page->id}}" method="POST" class="form-horizontal">
		@else
			<form action="{{url('/manage/page/store')}}" method="POST" class="form-horizontal">
		@endif
			{{ csrf_field() }}

			<!-- Page form  -->
			<div class="form-group">
				<label for="page_name" class="col-md-3 control-label">ページ名</label>
				<div class="col-md-9">
					<input type="text" name="page_name" id="page_name" value="{{$page->page_name}}" class="form-control">
				</div>
			</div>
			<div class="form-group">
				<label for="permanent_link" class="col-md-3 control-label">固定リンク</label>
				<div class="col-md-9">
					<input type="text" name="permanent_link" id="permanent_link" value="{{$page->permanent_link}}" class="form-control">
				</div>
			</div>

			<!-- Add or Update Page Button -->
			<div class="form-group">
				<div class="col-sm-offset-3 col-sm-6">
					<button type="submit" class="btn btn-primary form-horizontal">
						@if ($page->id)ページ更新 @else ページ追加 @endif
					</button>
					@if ($page->id)
						<button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{url('/manage/page')}}'">Cancel</button>
					@else
						<button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{url('/')}}'">Cancel</button>
					@endif
				</div>
			</div>
		</form>
	</div>
</div>
