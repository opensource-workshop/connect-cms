{{--
 * アップロードファイル管理のサムネイル表示テンプレート
 *
 * @author OpenSource-WorkShop Co.,Ltd.
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category アップロードファイル管理
 --}}
<div class="uploadfile-file-content">
    <a href="{{url('/')}}/file/{{$upload->id}}" target="_blank" class="uploadfile-file-name">{{$upload->client_original_name}}</a>
    @if ($upload->is_image)
        {{-- 画像ファイルの場合、サムネイル画像を表示 --}}
        <a href="{{url('/')}}/file/{{$upload->id}}"
           target="_blank"
           class="uploadfile-thumbnail-link"
           data-toggle="popover"
           data-trigger="hover focus"
           data-placement="auto"
           data-container="body"
           data-html="true"
           data-preview-src="{{url('/')}}/file/{{ $upload->id }}"
           data-preview-alt="{{$upload->client_original_name}} の拡大表示"
           title="{{$upload->client_original_name}}">
            <img src="{{url('/')}}/file/{{ $upload->id }}" class="uploadfile-thumbnail" loading="lazy" alt="{{$upload->client_original_name}} のサムネイル">
        </a>
    @endif
</div>
