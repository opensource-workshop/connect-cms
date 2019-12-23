{{--
 * 検索画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 検索プラグイン
--}}
<form action="{{url('/')}}/plugin/searchs/search/{{$page->id}}/{{$frame_id}}" method="post" name="form_approval" class="d-inline">
    {{ csrf_field() }}

    <div class="input-group">
        <input type="text" name="search_keyword" class="form-control" value="{{old('search_keyword')}}" placeholder="キーワードを入力してください。" />
        <div class="input-group-append">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
    @if ($errors && $errors->has('search_keyword')) <div class="text-danger">{{$errors->first('search_keyword')}}</div> @endif
</form>
