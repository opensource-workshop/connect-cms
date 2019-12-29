{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category サイト内検索プラグイン
 --}}

<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.searchs.searchs_frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

<form action="/plugin/searchs/changeBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
    {{ csrf_field() }}

    <div class="form-group">
        <table class="table table-hover" style="margin-bottom: 0;">
        <thead>
            <tr>
                <th></th>
                <th>サイト内検索名</th>
                <th>作成日</th>
            </tr>
        </thead>
        <tbody>
        @foreach($searchs as $search)
            <tr @if ($searchs_frame->frames_id == $search->id) class="active"@endif>
                <td><input type="radio" value="{{$search->bucket_id}}" name="select_bucket"@if ($searchs_frame->bucket_id == $search->bucket_id) checked @endif></input></td>
                <td>{{$search->search_name}}</td>
                <td>{{$search->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <div class="text-center">
        {{ $searchs->links() }}
    </div>

    <div class="form-group text-center">
        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i> キャンセル</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示サイト内検索変更</button>
    </div>
</form>
