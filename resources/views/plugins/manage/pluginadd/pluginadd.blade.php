{{--
 * プラグイン追加のメインテンプレート
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン追加
 --}}
{{-- 管理画面ベース画面 --}}
@extends('plugins.manage.manage')

{{-- 管理画面メイン部分のコンテンツ section:manage_content で作ること --}}
@section('manage_content')

<script type="text/javascript">
    function submit_form_select_page(obj) {

        var idx = obj.selectedIndex;
        var value = obj.options[idx].value;
        form_select_page.action = form_select_page.action + "/" + value;
        form_select_page.submit();
    }
</script>

<div class="panel panel-default">
    <div class="panel-heading">
        対象ページ
    </div>
    <div class="panel-body">
        <form action="{{url('/manage/pluginadd/index')}}" method="POST" name="form_select_page" class="form-horizontal">
            {{ csrf_field() }}

            <div class="form-group">
                <label for="page_name" class="col-md-2 control-label">対象ページ</label>
                <div class="col-md-10">
                    @if (isset($page))
                        <span class="form-control">{{$page->page_name}}</span>
                    @else
                        <span class="form-control"></span>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label for="permanent_link" class="col-md-2 control-label">対象変更</label>
                <div class="col-md-10">
                    <select name="select_page" class="form-control" onchange="javascript:submit_form_select_page(this);">
                        <option value="">【ページを選択してください。】</option>
                        @foreach($pages as $page_ojb)
                            @if ($page_ojb->id == $page_id)
                                <option value="{{$page_ojb->id}}" selected>
                            @else
                                <option value="{{$page_ojb->id}}">
                            @endif
                            @for ($i = 0; $i < $page_ojb->depth; $i++)
                            -
                            @endfor
                            {{$page_ojb->page_name}}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

@if (isset($page))
<div class="panel panel-default">
    <div class="panel-heading">
        プラグイン追加
    </div>
    <div class="panel-body">
        <form action="{{url('/manage/pluginadd/add')}}" method="POST" name="form_plugin_add" class="form-horizontal">
            {{ csrf_field() }}

            <div class="panel panel-default">
                <div class="panel-heading">
                    ヘッダー
                </div>
                <table class="table table-bordered">
                    <tr>
                        <td>左</td>
                        <td>メイン</td>
                        <td>右</td>
                    </tr>
                </table>
                <div class="panel-footer">
                    フッター
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@endsection
