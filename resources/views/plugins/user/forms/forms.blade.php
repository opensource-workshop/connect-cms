{{--
 * 登録画面テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォーム・プラグイン
 --}}
@if ($form && $forms_columns)
{{--    <form class="form-horizontal" method="post" action="/plugin/forms/confirm/{{$page->id}}/{{$frame_id}}"> --}}
    <form action="{{URL::to($page->permanent_link)}}?action=confirm&frame_id={{$frame_id}}" name="form_add_column{{$frame_id}}" method="POST" class="form-horizontal">
        {{ csrf_field() }}
        @foreach($forms_columns as $form_column)
        <div class="form-group">
            <label class="col-lg-2 control-label">{{$form_column->column_name}}</label>
            <div class="col-lg-10">
            @switch($form_column->column_type)
            @case("group")
                <div class="form-inline">
                @foreach($form_column->group as $group_row)
                    <label class="control-label" style="vertical-align: top;">{{$group_row->column_name}}</label>@if ($group_row->required)<label class="label label-danger">必須</label> @endif
                        <input name="forms_columns_value[{{$group_row->id}}]" class="form-control" type="{{$group_row->column_type}}" />
                @endforeach
                </div>
                @break
            @case("text")
                <input name="forms_columns_value[{{$form_column->id}}]" class="form-control" type="{{$form_column->column_type}}">
                @break
            @case("textarea")
                <textarea name="forms_columns_value[{{$form_column->id}}]" class="form-control"></textarea>
                @break
            @case("radio")
                <input name="forms_columns_value[{{$form_column->id}}]" type="{{$form_column->column_type}}"> あああ
                @break
            @case("checkbox")
                <input name="forms_columns_value[{{$form_column->id}}]" type="{{$form_column->column_type}}"> いいい
                @break
            @endswitch
            </div>
        </div>
        @endforeach
        <div class="form-group">
            <div class="col-lg-2 col-lg-push-2">
                <button class="btn btn-default">送信</button>
            </div>
        </div>
    </form>
@else
    フォームが定義されていません。
@endif
@auth
<p class="text-right">
    {{-- 設定画面へのリンク --}}
    <a href="{{$page->permanent_link}}?action=edit&frame_id={!!$frame_id!!}#{!!$frame_id!!}"><span class="glyphicon glyphicon-edit"></a>
</p>
@endauth
