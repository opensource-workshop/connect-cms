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
        {{-- ユーザ指定時に選択したものを表示する --}}
        @if ($searchs_frame->frame_select == 2)
            <div class="input-group-prepend mr-3">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" name="narrow_down" value="1" class="custom-control-input" id="narrow_down" @if(old("narrow_down")) checked=checked @endif>
                    <label class="custom-control-label" id="narrow_down_label" for="narrow_down">{{$searchs_frame->narrow_down_label}}</label>
                </div>
            </div>
        @endif
        <input type="text" name="search_keyword" class="form-control" value="{{old('search_keyword')}}" placeholder="検索はキーワードを入力してください。" title="検索キーワード" />
        <div class="input-group-append">
            <button type="submit" class="btn btn-primary" title="検索" id="button_search">
                <i class="fas fa-search" role="presentation"></i>
            </button>
        </div>
    </div>
    @if ($errors && $errors->has('search_keyword')) <div class="text-danger">{{$errors->first('search_keyword')}}</div> @endif
</form>
