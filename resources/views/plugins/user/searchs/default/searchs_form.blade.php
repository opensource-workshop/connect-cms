{{--
 * 検索画面
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 検索プラグイン
--}}
<form action="{{url('/')}}/plugin/searchs/search/{{$page->id}}/{{$frame_id}}#frame-{{$frame->id}}" method="post" name="form_approval" class="d-inline" role="search" aria-label="{{$searchs_frame->search_name}}">
    {{ csrf_field() }}

    <div class="input-group">
        <input type="text" name="search_keyword" class="form-control" value="{{old('search_keyword')}}" placeholder="検索はキーワードを入力してください。" title="検索キーワード" />
        <div class="input-group-append">
            <button type="submit" class="btn btn-primary" title="検索">
                <i class="fas fa-search" role="presentation"></i>
            </button>
        </div>
    </div>
    @if ($errors && $errors->has('search_keyword')) <div class="text-danger">{{$errors->first('search_keyword')}}</div> @endif
</form>
