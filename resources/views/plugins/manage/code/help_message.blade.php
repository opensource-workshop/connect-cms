{{--
 * 注釈一覧画面のメインテンプレート
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.code.code_manage_tab')
    </div>
    <div class="card-body">

        {{-- 一覧エリア --}}
        <div class="text-right mt-3"><span class="badge badge-pill badge-light">{{ $codes_help_messages->total() }} 件</span></div>
        <table class="table table-bordered table_border_radius table-hover cc-font-90">
            <tbody>
                <tr class="bg-light d-none d-sm-table-row">
                    <th class="d-block d-sm-table-cell text-break">注釈名</th>
                    <th class="d-block d-sm-table-cell text-break">注釈キー</th>
                    <th class="d-block d-sm-table-cell text-break">表示順</th>
                </tr>

                @foreach($codes_help_messages as $codes_help_message)
                <tr>
                    <th class="d-block d-sm-table-cell bg-light">
                        <a href="{{url('/')}}/manage/code/helpMessageEdit/{{$codes_help_message->id}}?page={{$paginate_page}}&search_words={{$search_words}}"><i class="far fa-edit"></i></a>
                        <span class="d-sm-none">注釈名：</span>{{$codes_help_message->name}}
                    </th>
                    <td class="d-block d-sm-table-cell"><span class="d-sm-none">注釈キー：</span>{{$codes_help_message->alias_key}}</td>
                    <td class="d-block d-sm-table-cell"><span class="d-sm-none">表示順：</span>{{$codes_help_message->display_sequence}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $codes_help_messages->links() }}

    </div>
</div>

@endsection
