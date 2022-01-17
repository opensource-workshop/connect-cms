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
    <div class="card-body">

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
                        <input type="radio" value="netcommons3" id="source_system_netcommons3" name="source_system" class="custom-control-input" checked>
                        <label class="custom-control-label" for="source_system_netcommons3">NetCommons3</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="netcommons2" id="source_system_netcommons2" name="source_system" class="custom-control-input" disabled>
                        <label class="custom-control-label" for="source_system_netcommons2">NetCommons2</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="html" id="source_system_html" name="source_system" class="custom-control-input" disabled>
                        <label class="custom-control-label" for="source_system_html">HTML</label>
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
                <label for="page_name" class="col-md-3 col-form-label text-md-right pt-0">取り込み済み<br class="d-none d-md-inline" />移行データ</label>
                <div class="col-md-9">
                    @foreach($migration_pages as $migration_page)
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" value="{{$migration_page->id}}" id="migration_page_{{$migration_page->id}}" name="migration_page_id" class="custom-control-input">
                        <label class="custom-control-label" for="migration_page_{{$migration_page->id}}">{{$migration_page->page_name}}</label>
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
