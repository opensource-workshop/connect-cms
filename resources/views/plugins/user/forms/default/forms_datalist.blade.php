{{--
 * 編集画面(データ選択)テンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
 --}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.forms.forms_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")
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

    {{-- ダウンロード用フォーム --}}
    <form action="" method="post" name="form_download" class="d-inline">
        {{ csrf_field() }}
    </form>

    <script type="text/javascript">
        {{-- ダウンロードのsubmit JavaScript --}}
        function submit_download(id) {
            if( !confirm('登録データをダウンロードします。\nよろしいですか？') ) {
                return;
            }
            form_download.action = "{{url('/')}}/download/plugin/forms/downloadCsv/{{$page->id}}/{{$frame_id}}/" + id;
            form_download.submit();
        }
    </script>

    <form action="{{url('/')}}/plugin/{{$frame->plugin_name}}/changeBuckets/{{$page->id}}/{{$frame_id}}" method="POST" class="">
        {{ csrf_field() }}
        <div class="form-group">
            <table class="table table-hover table-responsive">
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
                    <td nowrap><input type="radio" value="{{$plugin->bucket_id}}" name="select_bucket"@if ($plugin_frame->bucket_id == $plugin->bucket_id) checked @endif></input></td>
                    <td nowrap>{{$plugin->plugin_bucket_name}}</td>
                    <td nowrap>
                        <button class="btn btn-primary btn-sm mr-1" type="button" onclick="location.href='{{url('/')}}/plugin/forms/editBuckets/{{$page->id}}/{{$frame_id}}/{{$plugin->id}}'">
                            <i class="far fa-edit"></i> 設定変更
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="javascript:submit_download({{$plugin->id}});">
                            <i class="fas fa-file-download"></i> ダウンロード
                        </button>
                    </td>
                    <td nowrap>{{$plugin->created_at}}</td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>

        <div class="text-center">
            {{ $plugins->links() }}
        </div>

        <div class="form-group text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{URL::to($page->permanent_link)}}'"><i class="fas fa-times"></i><span class="{{$frame->getSettingButtonCaptionClass()}}"> キャンセル</span></button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> 表示{{$frame->plugin_name_full}}変更</button>
        </div>
    </form>
@endauth
@endsection
