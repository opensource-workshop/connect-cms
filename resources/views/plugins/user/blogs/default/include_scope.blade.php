{{--
 * 表示条件の表示
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
--}}
@if ($blog_frame_setting->scope != BlogFrameScope::all)
    <span class="badge badge-warning">
        <a href="{{url('/')}}/plugin/blogs/settingBlogFrame/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}">
            <i class="fas fa-cog"></i>
        </a>
        表示条件{{ '（' . $blog_frame_setting->scope_value . BlogFrameScope::getDescription($blog_frame_setting->scope) . '）' }}
    </span>
@endif
