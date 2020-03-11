{{--
 * 詳細表示画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>, 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category データベース・プラグイン
 --}}
@extends('core.cms_frame_base')

@section("plugin_contents_$frame->id")

<table class="table table-bordered">
    @foreach($columns as $column)
    @if($column->detail_hide_flag == 0)
    <tr>
        <th style="background-color: #e9ecef;">{{$column->column_name}}</th>
        <td>
            @include('plugins.user.databases.default.databases_include_detail_value')
        </td>
    </tr>
    @endif
    @endforeach
</table>

{{-- 一覧へ戻る --}}
<div class="row">
    <div class="col-12 text-center mt-3">
        <a href="{{url('/')}}{{$page->getLinkUrl()}}">
            <span class="btn btn-info"><i class="fas fa-list"></i> <span class="hidden-xs">{{__('messages.to_list')}}</span></span>
        </a>
    </div>
</div>

<br />
@can("role_article")
<button type="button" class="btn btn-success" onclick="location.href='{{url('/')}}/plugin/databases/input/{{$page->id}}/{{$frame_id}}/{{$inputs->id}}'">
    <i class="far fa-edit"></i> 変更
</button>

<a data-toggle="collapse" href="#collapse{{$inputs->id}}" class="ml-3">
    <span class="btn btn-danger"><i class="fas fa-trash-alt"></i><span class="{{$frame->getSettingButtonCaptionClass('md')}}"> 削除</span></span>
</a>

<div id="collapse{{$inputs->id}}" class="collapse" style="margin-top: 8px;">
    <div class="card border-danger">
        <div class="card-body">
            <span class="text-danger">データを削除します。<br>元に戻すことはできないため、よく確認して実行してください。</span>

            <div class="text-center">
                {{-- 削除ボタン --}}
                <form action="{{url('/')}}/plugin/databases/delete/{{$page->id}}/{{$frame_id}}/{{$inputs->id}}" method="POST">
                    {{csrf_field()}}
                    <button type="submit" class="btn btn-danger" onclick="javascript:return confirm('データを削除します。\nよろしいですか？')"><i class="fas fa-check"></i> 本当に削除する</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endcan

@endsection
