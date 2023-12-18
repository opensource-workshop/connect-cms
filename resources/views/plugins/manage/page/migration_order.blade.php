{{--
 * 外部ページ移行指示画面のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
--}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.page.page_manage_tab')
    </div>

    {{-- ページ変更関連タブ --}}
    @include('plugins.manage.page.page_edit_tab')

    <div class="card-body">

        {{-- 共通エラーメッセージ 呼び出し --}}
        @include('plugins.common.errors_form_line')
        {{-- 登録後メッセージ表示 --}}
        @include('plugins.common.flash_message')

        <div class="alert alert-danger">
            <h1>本機能（Webスクレイピング）を利用するに当たっての注意点</h1>
            <ul>
                <li>Webスクレイピングは、対象サイトの利用規約に違反する場合があります。必ず対象サイトの利用規約を確認し、遵守してください。</li>
                <li>スクレイピングによってサイトへ負荷をかけすぎるとサーバに負荷がかかり、サイトがダウンすることがあります。負荷をかけすぎないようにご注意ください。本機能では「{{ $request_interval }}秒に1回まで」の制限を設けています。</li>
                <li>スクレイピング結果を使用する場合、利用規約や著作権に関する法律に従って使用するようにしてください。不正使用は法的な責任を問われることがあります。</li>
                <li>本機能を利用することにより生じたいかなる損害について、Connect-CMS、及び、株式会社オープンソース・ワークショップ、並びに、開発者は一切の責任を負いません。</li>
            </ul>
        </div>

        <div class="alert alert-info">
            移行先ページ名：{{$current_page->page_name}}
        </div>

        <form action="{{url('/manage/page/migrationGet')}}/{{$current_page->id}}" method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="destination_page_id" value="{{$current_page->id}}">

            <div class="form-group row">
                <label for="source_system" class="col-md-3 col-form-label text-md-right">移行元システム</label>
                <div class="col-md-9 d-sm-flex align-items-center">

                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="{{ WebsiteType::netcommons3 }}" id="source_system_netcommons3" name="source_system" class="custom-control-input" checked>
                        <label class="custom-control-label" for="source_system_netcommons3">{{ WebsiteType::getDescription(WebsiteType::netcommons3) }}</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="{{ WebsiteType::netcommons2 }}" id="source_system_netcommons2" name="source_system" class="custom-control-input" disabled>
                        <label class="custom-control-label" for="source_system_netcommons2">{{ WebsiteType::getDescription(WebsiteType::netcommons2) }}</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="{{ WebsiteType::html }}" id="source_system_html" name="source_system" class="custom-control-input">
                        <label class="custom-control-label" for="source_system_html">{{ WebsiteType::getDescription(WebsiteType::html) }}</label>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label for="page_name" class="col-md-3 col-form-label text-md-right">移行元URL</label>
                <div class="col-md-9">
                    <input type="text" name="url" id="page_name" value="{{old('url')}}" class="form-control @if ($errors->has('url')) border-danger @endif">
                    @include('plugins.common.errors_inline', ['name' => 'url'])
                </div>
            </div>

            {{-- UI的に、セレクトボックスは不要だったのでとりあえず、コメントアウト
            <div class="form-group row">
                <label for="page_name" class="col-md-3 col-form-label text-md-right">移行先ページ</label>
                <div class="col-md-9">

                    <select name="destination_page_id" class="form-control">
                        <option value="">...</option>
                        @foreach($pages as $page)
                            <option value="{{$page->id}}" @if(old('destination_page_id') == $page->id) selected @endif>
                                @for ($i = 0; $i < $page->depth; $i++)
                                -
                                @endfor
                                {{$page->page_name}}
                            </option>
                        @endforeach
                    </select>
                    @if ($errors && $errors->has('destination_page_id')) <div class="text-danger">{{$errors->first('destination_page_id')}}</div> @endif
                </div>
            </div>
            --}}

            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> データ取り込み
                </button>
                @include('plugins.common.errors_inline', ['name' => 'request_interval'])
            </div>
        </form>
    </div>

    <script type="text/javascript">
        /** 取り込み済みデータ削除 */
        function submit_migration_file_delete(delete_file_page_id_dir) {
            if (confirm("取り込み済みデータを削除します。\nよろしいですか？")) {
                form_migration_file_delete.delete_file_page_id_dir.value = delete_file_page_id_dir;
                form_migration_file_delete.submit();
            }
        }
    </script>
    <form action="{{url('/manage/page/migrationFileDelete')}}/{{$current_page->id}}" method="post" name="form_migration_file_delete">
        {{ csrf_field() }}
        <input type="hidden" name="delete_file_page_id_dir" value="">
    </form>

    <div class="card-body">
        <form action="{{url('/manage/page/migrationImort')}}/{{$current_page->id}}" method="post" class="form-horizontal">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="page_name" class="col-md-3 col-form-label text-md-right pt-0">取り込み済み<br class="d-none d-md-inline" />移行データ<br class="d-none d-md-inline">（取り込み日時）</label>
                <div class="col-md-9">
                    @foreach($migration_pages as $migration_page)
                    <div class="custom-control custom-radio ">
                        <input type="radio" value="{{$migration_page->id}}" id="migration_page_{{$migration_page->id}}" name="migration_page_id" class="custom-control-input">
                        {{-- 取り込み済み移行データ名（＝移行先ページ名） --}}
                        <label class="custom-control-label" for="migration_page_{{$migration_page->id}}">{{$migration_page->page_name}}</label>
                        <br class="d-sm-none">
                        {{-- ページ毎のディレクトリ更新日時 --}}
                        <span class="ml-2 mr-2">({{ $migration_page->migration_directory_timestamp }})</span>
                        {{-- 削除ボタン --}}
                        <button type="button" class="btn btn-link p-0" onClick="submit_migration_file_delete('{{$migration_page->page_id_dir}}');">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    @endforeach
                    @include('plugins.common.errors_inline', ['name' => 'migration_page_id'])
                </div>
            </div>

            <div class="text-center form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> インポート
                </button>
            </div>
        </form>
    </div>

    <div class="text-center form-group">
        <a href="{{url('/manage/page/edit')}}/{{$page->id}}" class="btn btn-secondary mr-2">
            <i class="fas fa-chevron-left"></i> ページ変更へ
        </a>
    </div>
</div>
@endsection
