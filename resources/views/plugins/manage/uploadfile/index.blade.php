{{--
 * アップロードファイル管理のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category アップロードファイル管理
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<div class="card">
    <div class="card-header p-0">
        {{-- 機能選択タブ --}}
        @include('plugins.manage.uploadfile.uploadfile_manage_tab')
    </div>
    <div class="card-body">

        <div class="accordion" id="search_accordion">
            <div class="card">
                <button class="btn btn-link p-0 text-left collapsed" type="button" data-toggle="collapse" data-target="#search_collapse" aria-expanded="false" aria-controls="search_collapse">
                    <div class="card-header" id="search_condition">
                        絞り込み条件 <i class="fas fa-angle-down"></i>
                   </div>
                </button>
                @if (Session::has('search_condition.client_original_name') || (Session::has('search_condition.sort')))
                <div id="search_collapse" class="collapse show" aria-labelledby="search_condition" data-parent="#search_accordion">
                @else
                <div id="search_collapse" class="collapse" aria-labelledby="search_condition" data-parent="#search_accordion">
                @endif
                    <div class="card-body">

                        <form name="form_search" id="form_search" class="form-horizontal" method="post" action="{{url('/')}}/manage/uploadfile/search">
                            {{ csrf_field() }}

                            {{-- ファイル名 --}}
                            <div class="form-group row">
                                <label for="search_condition_client_original_name" class="col-md-3 col-form-label text-md-right">ファイル名</label>
                                <div class="col-md-9">
                                    <input type="text" name="search_condition[client_original_name]" id="search_condition_client_original_name" value="{{Session::get('search_condition.client_original_name')}}" class="form-control">
                                </div>
                            </div>

                            {{-- 並べ替え --}}
                            <div class="form-group row">
                                <label for="sort" class="col-md-3 col-form-label text-md-right">並べ替え</label>
                                <div class="col-md-9">
                                    <select name="search_condition[sort]" id="sort" class="form-control">
                                        <option value="id_asc"@if(Session::get('search_condition.sort') == "id_asc") selected @endif>ID 昇順</option>
                                        <option value="id_desc"@if(Session::get('search_condition.sort') == "id_desc" || !Session::has('search_condition.sort')) selected @endif>ID 降順</option>
                                        <option value="client_original_name_asc"@if(Session::get('search_condition.sort') == "client_original_name_asc") selected @endif>ファイル名 昇順</option>
                                        <option value="client_original_name_desc"@if(Session::get('search_condition.sort') == "client_original_name_desc") selected @endif>ファイル名 降順</option>
                                        <option value="size_asc"@if(Session::get('search_condition.sort') == "size_asc") selected @endif>サイズ 昇順</option>
                                        <option value="size_desc"@if(Session::get('search_condition.sort') == "size_desc") selected @endif>サイズ 降順</option>
                                        <option value="created_at_asc"@if(Session::get('search_condition.sort') == "created_at_asc") selected @endif>アップロード日時 昇順</option>
                                        <option value="created_at_desc"@if(Session::get('search_condition.sort') == "created_at_desc") selected @endif>アップロード日時 降順</option>
                                        <option value="download_count_desc"@if(Session::get('search_condition.sort') == "download_count_desc") selected @endif>ダウンロード数 降順</option>
                                    </select>
                                </div>
                            </div>

                            {{-- ボタンエリア --}}
                            <div class="form-group text-center">
                                <div class="row">
                                    <div class="mx-auto">
                                        <button type="button" class="btn btn-secondary mr-2" onclick="location.href='{{url('/manage/uploadfile/clearSearch')}}'">
                                            <i class="fas fa-times"></i> クリア
                                        </button>
                                        <button type="submit" class="btn btn-primary form-horizontal">
                                            <i class="fas fa-check"></i> 絞り込み
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table text-nowrap">
            <thead>
                <th nowrap></th>
                <th nowrap>ID</th>
                <th nowrap>ファイル名</th>
                <th nowrap>サイズ</th>
                <th nowrap>アップロード日時</th>
                <th nowrap>プラグイン</th>
                <th nowrap>ダウンロード数</th>
                <th nowrap>ページ</th>
                {{-- <th nowrap>private</th> --}}
                <th nowrap>一時保存フラグ</th>
            </thead>
            <tbody>
                @foreach($uploads as $upload)
                <tr>
                    <td><a href="{{url('/')}}/manage/uploadfile/edit/{{$upload->id}}" id="edit_{{$loop->iteration}}"><i class="far fa-edit"></i></a></td>
                    <td>{{$upload->id}}</td>
                    <td><a href="{{url('/')}}/file/{{$upload->id}}" target="_blank">{{$upload->client_original_name}}</a></td>
                    <td>{{$upload->getFormatSize()}}</td>
                    <td>{{$upload->created_at}}</td>
                    <td>{{$upload->getPluginNameFull()}}</td>
                    <td>{{$upload->download_count}}</td>
                    <td>{!!$upload->getPageLinkTag('_blank')!!}</td>
                    {{-- <td>{{$upload->private}}</td> --}}
                    <td>{{$upload->getTemporaryFlagStr()}}</td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>

        {{-- ページング処理 --}}
        @if($uploads)
        <div class="text-center">
            {{$uploads->links()}}
        </div>
        @endif
    </div>
</div>
@endsection
