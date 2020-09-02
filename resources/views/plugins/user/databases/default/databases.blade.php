{{--
 * 登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
--}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")
@if (empty($setting_error_messages))

    {{-- ヘッダー部分 --}}
    @include('plugins.user.databases.default.databases_include_ctrl_head')

    @if ($default_hide_list)
    @else
        @foreach($inputs as $input)
            <div class="container @if(! $loop->first) mt-4 @endif">
                {{-- 行グループ ループ --}}
                @foreach($group_rows_cols_columns as $group_row_cols_columns)
                    <div class="row border-left border-right border-bottom @if($loop->first) border-top @endif">
                    {{-- 列グループ ループ --}}
                    @foreach($group_row_cols_columns as $group_col_columns)
                        <div class="col-sm">
                        {{-- カラム ループ --}}
                        @foreach($group_col_columns as $column)
                            <div class="row pt-2 pb-2">
                                <div class="col">
                                    <small><b>{{$column->column_name}}</b></small><br>
                                    @include('plugins.user.databases.default.databases_include_value')
                                </div>
                            </div>
                        @endforeach
                        </div>
                    @endforeach
                    </div>
                @endforeach
            </div>

            {{-- 詳細 --}}
            <div class="row mt-2">
                <div class="col">
                    <div class="text-right">
                        @if ($input->status == 2)
                            @can('role_update_or_approval',[[$input, $frame->plugin_name, $buckets]])
                                <span class="badge badge-warning align-bottom">承認待ち</span>
                            @endcan
                            @can('posts.approval',[[$input, $frame->plugin_name, $buckets]])
                                <form action="{{url('/')}}/plugin/databases/approval/{{$page->id}}/{{$frame_id}}/{{$input->id}}#frame-{{$frame_id}}" method="post" name="form_approval" class="d-inline">
                                    {{ csrf_field() }}
                                    <button type="submit" class="btn btn-primary btn-sm" onclick="javascript:return confirm('承認します。\nよろしいですか？');">
                                        <i class="fas fa-check"></i> <span class="hidden-xs">承認</span>
                                    </button>
                                </form>
                            @endcan
                        @endif
                        @can('posts.update',[[$input, $frame->plugin_name, $buckets]])
                            @if ($input->status == 1)
                                <span class="badge badge-warning align-bottom">一時保存</span>
                            @endif
                        @endcan

                        <button type="button" class="btn btn-success btn-sm ml-2" onclick="location.href='{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}/{{$input->id}}#frame-{{$frame_id}}'">
                            <i class="far fa-edit"></i> 編集
                        </button>

                        <a href="{{url('/')}}/plugin/databases/detail/{{$page->id}}/{{$frame_id}}/{{$input->id}}#frame-{{$frame_id}}" class="ml-2">
                            <span class="btn btn-success btn-sm">詳細 <i class="fas fa-angle-right"></i></span>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- ページング処理 --}}
        <div class="text-center mt-2">
            {{ $inputs->fragment('frame-' . $frame_id)->links() }}
        </div>
    @endif

@else
    {{-- フレームに紐づくコンテンツがない場合等、表示に支障がある場合は、データ登録を促す等のメッセージを表示 --}}
    <div class="card border-danger">
        <div class="card-body">
            @foreach ($setting_error_messages as $setting_error_message)
                <p class="text-center cc_margin_bottom_0">{{ $setting_error_message }}</p>
            @endforeach
        </div>
    </div>
@endif
@endsection
