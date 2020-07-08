{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
 --}}
<ul class="nav nav-tabs">
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.' . $frame->plugin_name . '.' . $frame->plugin_name . '_frame_edit_tab')

    {{-- コア側のフレームメニュー --}}
    @include('core.cms_frame_edit_tab')
</ul>

@auth

    {{-- 選択されているBucketがあるかの判定 --}}
    @php
        $plugin_selected = false;
    @endphp

    @foreach($plugins as $plugin)
        @if ($plugin_frame->bucket_id == $plugin->bucket_id)
            @php
                $plugin_selected = true;
            @endphp
        @endif
    @endforeach

    @if (!$plugin_selected)
        <div class="alert alert-warning" style="margin-top: 10px;">
            <i class="fas fa-exclamation-circle"></i>
            {{$frame->plugin_name_full}}を選択するか、{{$frame->plugin_name_full}}作成で作成してください。
        </div>
    @endif

    <form action="{{url('/')}}/plugin/{{$frame->plugin_name}}/change/{{$page->id}}/{{$frame_id}}" method="POST" class="">
        {{ csrf_field() }}
        <div class="form-group">
            <table class="table table-hover" style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th></th>
                    <th>{{$frame->plugin_name_full}}名</th>
                    <th>詳細</th>
                    <th>作成日</th>
                </tr>
            </thead>
            <tbody>
            @foreach($plugins as $plugin)
                <tr @if ($plugin->id == $plugin_frame->id) class="active"@endif>
                    <td><input type="radio" value="{{$plugin->bucket_id}}" name="select_bucket"@if ($plugin_frame->bucket_id == $plugin->bucket_id) checked @endif></td>
                    <td>{{$plugin->plugin_bucket_name}}</td>
                    <th><button class="btn btn-primary btn-sm" type="button" onclick="location.href='{{url('/')}}/plugin/{{$frame->plugin_name}}/editBuckets/{{$page->id}}/{{$frame_id}}/{{$plugin->id}}'"><i class="far fa-edit"></i> 設定変更</button></th>
                    <td>{{$plugin->created_at}}</td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>

        <div class="text-center">
            {{ $plugins->links() }}
        </div>

        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary mr-3"><i class="fas fa-check"></i> 表示{{$frame->plugin_name_full}}変更</button>
            <button type="button" class="btn btn-secondary" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i> キャンセル</button>
        </div>
    </form>
@endauth
