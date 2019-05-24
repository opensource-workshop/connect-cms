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
@if (empty($plugins) || count($plugins)==0)
    <div class="alert alert-warning" style="margin-top: 10px;">
        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
        {{$frame->plugin_name_full}}選択画面から選択するか、{{$frame->plugin_name_full}}新規作成で作成してください。
    </div>
@else

<form action="/plugin/{{$frame->plugin_name}}/change/{{$page->id}}/{{$frame_id}}" method="POST" class="">
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
                <td><input type="radio" value="{{$plugin->bucket_id}}" name="select_bucket"@if ($plugin_frame->bucket_id == $plugin->bucket_id) checked @endif></input></td>
                <td>{{$plugin->plugin_bucket_name}}</td>
                <th><button class="btn btn-primary btn-sm" type="button" onclick="location.href='{{url('/')}}/plugin/{{$frame->plugin_name}}/editPlugin/{{$page->id}}/{{$frame_id}}/{{$plugin->id}}'"><span class="glyphicon glyphicon-edit"></span> 設定変更</button></th>
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
        <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span> 表示{{$frame->plugin_name_full}}変更</button>
        <button type="button" class="btn btn-default" style="margin-left: 10px;" onclick="location.href='{{URL::to($page->permanent_link)}}'"><span class="glyphicon glyphicon-remove"></span> キャンセル</button>
    </div>
</form>
@endif
@endauth
