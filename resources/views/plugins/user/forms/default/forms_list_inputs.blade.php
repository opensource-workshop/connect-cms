{{--
 * 登録一覧画面テンプレート
--}}
@extends('core.cms_frame_base_setting')

@section("core.cms_frame_edit_tab_$frame->id")
    {{-- プラグイン側のフレームメニュー --}}
    @include('plugins.user.forms.forms_frame_edit_tab')
@endsection

@section("plugin_setting_$frame->id")

@if (session('flash_message'))
    <div class="alert alert-success">
        {{ session('flash_message') }}
    </div>
@endif

{{-- データのループ --}}
<table class="table table-bordered table-responsive table-sm">
    <thead class="thead-light">
        <tr>
            <th style="width:50px">状態</th>
            @foreach($columns as $column)
                <th>{{$column->column_name}}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
    @foreach($inputs as $input)

        @if ($input->status == FormStatusType::temporary)
        {{-- 仮登録 --}}
        <tr class="table-warning">
        @elseif ($input->status == FormStatusType::delete)
        {{-- 削除 --}}
        <tr class="table-danger">
        @else
        {{-- 本登録 --}}
        <tr>
        @endif

            <td>
                <a href="{{url('/')}}/plugin/forms/editInput/{{$page->id}}/{{$frame_id}}/{{$input->id}}#frame-{{$frame_id}}" title="編集">
                    <i class="far fa-edit"></i> {{$input->status}}
                </a>
            </td>

            @foreach($columns as $column)
                <td>
                    @include('plugins.user.forms.default.forms_include_value')
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>

<table class="table-bordered table-sm">
    <tbody>
    <tr>
        <td>状態:0 = 本登録</td>
        <td class="table-warning">状態:1 = 仮登録</td>
        <td class="table-danger">状態:9 = 削除</td>
    </tr>
    </tbody>
</table>

{{-- ページング処理 --}}
{{-- アクセシビリティ対応。1ページしかない時に、空navを表示するとスクリーンリーダーに不要な Navigation がひっかかるため表示させない。 --}}
@if ($inputs->lastPage() > 1)
    <nav class="text-center mt-3" aria-label="{{$form->forms_name}}のページ付け">
        {{ $inputs->fragment('frame-' . $frame_id)->links() }}
    </nav>
@endif

{{-- ボタン --}}
<div class="form-group text-center mt-3">
    <div class="row">
        <div class="col">
            <a href="{{url('/')}}/plugin/{{$frame->plugin_name}}/listBuckets/{{$page->id}}/{{$frame->id}}#frame-{{$frame->id}}">
                <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">フォーム選択へ</span></span>
            </a>
        </div>
    </div>
</div>

@endsection
