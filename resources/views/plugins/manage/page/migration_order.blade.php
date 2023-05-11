{{--
 * 外部ページ移行指示画面のテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
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

        <div class="alert alert-danger" style="margin-top: 10px;">
            <h1>本機能（Webスクレイピング）を利用するに当たっての注意点</h1>
            <ul>
                <li>Webスクレイピングは、対象サイトの利用規約に違反する場合があります。必ず対象サイトの利用規約を確認し、遵守してください。</li>
                <li>スクレイピングによってサイトへ負荷をかけすぎるとサーバに負荷がかかり、サイトがダウンすることがあります。負荷をかけすぎないようにご注意ください。本機能では「{{ $request_interval }}秒に1回まで」の制限を設けています。</li>
                <li>スクレイピング結果を使用する場合、利用規約や著作権に関する法律に従って使用するようにしてください。不正使用は法的な責任を問われることがあります。</li>
                <li>本機能を利用することにより生じたいかなる損害について、Connect-CMS、及び、株式会社オープンソース・ワークショップ、並びに、開発者は一切の責任を負いません。</li>
            </ul>
        </div>

        <div class="alert alert-info" style="margin-top: 10px;">
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
                    <input type="text" name="url" id="page_name" value="{{old('url', '')}}" class="form-control">
                    @if ($errors && $errors->has('url')) <div class="text-danger">{{$errors->first('url')}}</div> @endif
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

            <div class="form-group row mt-3 text-center">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> データ取り込み
                    </button>
                    @if ($errors && $errors->has('request_interval')) <div class="text-danger">{{$errors->first('request_interval')}}</div> @endif
                </div>
            </div>
        </form>
    </div>

    <script type="text/javascript">
        {{-- 移行データ削除用のsubmit JavaScript --}}
        function submit_migration_file_delete(delete_file_page_id) {

            if (confirm("取り込み済みデータを削除します。\nよろしいですか？")) {
                // 続き
            }
            else {
                return false;
            }

            form_migration_file_delete.delete_file_page_id.value = delete_file_page_id;
            form_migration_file_delete.submit();
        }
    </script>
    <form action="{{url('/manage/page/migrationFileDelete')}}/{{$current_page->id}}" method="POST" name="form_migration_file_delete">
        {{ csrf_field() }}
        <input type="hidden" name="delete_file_page_id" value="">
    </form>


    <div class="card-body">
        <form action="{{url('/manage/page/migrationImort')}}/{{$current_page->id}}" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="page_name" class="col-md-3 col-form-label text-md-right pt-0">取り込み済み<br class="d-none d-md-inline" />移行データ<br class="d-none d-md-inline">（取り込み日時）</label>
                <div class="col-md-9">
                    @foreach($migration_pages as $migration_page)
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="{{$migration_page->id}}" id="migration_page_{{$migration_page->id}}" name="migration_page_id" class="custom-control-input">
                        {{-- 取り込み済み移行データ名（＝移行先ページ名） --}}
                        <label class="custom-control-label" for="migration_page_{{$migration_page->id}}">{{$migration_page->page_name}}</label>
                        {{-- ページ毎のディレクトリ更新日時を表示 --}}
                        <span class="ml-2 mr-2">({{ Carbon::createFromTimestamp(Storage::lastModified('migration/import/pages/' . $migration_page->id))->format('Y/m/d H:i:s') }})</span>
                        {{-- 削除ボタン --}}
                        <a href="#" onClick="submit_migration_file_delete({{$migration_page->id}});"><i class="fas fa-trash-alt mt-1 ml-1"></i></a>
                    </div>
                    <br />
                    @endforeach
                    @if ($errors && $errors->has('migration_page_id')) <div class="text-danger">{{$errors->first('migration_page_id')}}</div> @endif
                </div>
            </div>

            <div class="form-group row mt-3 text-center">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> インポート
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body">
        <div class="form-group row text-center">
            <div class="col">
                <a href="{{url('/manage/page/edit')}}/{{$page->id}}" class="btn btn-secondary mr-2">
                    <i class="fas fa-chevron-left"></i> ページ変更へ
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
