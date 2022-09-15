{{--
 * フォトアルバム画面テンプレート（フォルダ）
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
--}}
{{-- データ一覧にアルバムが含まれる場合 --}}
@if ($photoalbum_contents->where('is_folder', 1)->isNotEmpty())
<div class="row">
    @foreach($photoalbum_contents->where('is_folder', 1) as $photoalbum_content)
    <div class="col-sm-4 mt-3">
        <div class="card sm-4">
            <a href="{{url('/')}}/plugin/photoalbums/changeDirectory/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}/#frame-{{$frame->id}}" class="text-center">
                {{-- カバー画像が指定されていれば使用し、指定されていなければ、グレーのカバーを使用 --}}
                @if ($covers->where('parent_id', $photoalbum_content->id)->first())
                    <img src="{{url('/')}}/file/{{$covers->where('parent_id', $photoalbum_content->id)->first()->getCoverFileId()}}?size=small"
                         id="cover_{{$loop->iteration}}"
                         style="max-height: 200px; object-fit: scale-down; cursor:pointer; border-radius: 3px;"
                         class="img-fluid"
                    >
                @else
                    <svg class="bd-placeholder-img card-img-top" width="100%" height="150" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" role="img" aria-label="Placeholder: Image cap">
                        <title>{{$photoalbum_content->name}}</title>
                        <rect fill="#868e96" width="100%" height="100%"></rect>
                        <text fill="#dee2e6"x="50%" y="50%" text-anchor="middle" dominant-baseline="central">{{$photoalbum_content->name}}</text>
                    </svg>
                @endif
            </a>
        </div>
    </div>
    <div class="col-sm-8 mt-3">
        <div class="d-flex">
            @if ($download_check)
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="customCheck_{{$photoalbum_content->id}}" name="photoalbum_content_id[]" value="{{$photoalbum_content->id}}" data-name="{{$photoalbum_content->displayName}}">
                <label class="custom-control-label" for="customCheck_{{$photoalbum_content->id}}"></label>
            </div>
            @endif
            <h5 class="card-title">{{$photoalbum_content->name}}</h5>
        </div>
        <p class="card-text">{!!nl2br(e($photoalbum_content->description))!!}</p>
        <div class="d-flex justify-content-between align-items-center">
            @can('posts.update', [[$photoalbum_content, $frame->plugin_name, $buckets]])
            <a href="{{url('/')}}/plugin/photoalbums/edit/{{$page->id}}/{{$frame_id}}/{{$photoalbum_content->id}}#frame-{{$frame->id}}" class="btn btn-sm btn-success">
                <i class="far fa-edit"></i> 編集
            </a>
            @endcan
        </div>
    </div>
    @endforeach
</div>
@endif
