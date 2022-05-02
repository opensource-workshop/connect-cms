{{--
 * メール設定画面の説明-設定blade
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category プラグイン共通
 *
 * copy by resources\views\plugins\common\description_frame_mails.blade.php
--}}
@php
use App\Models\User\Databases\DatabasesColumns;
@endphp

<div class="card bg-light mt-1">
    <div class="card-body px-2 pt-0 pb-0">
        <div class="small">
            埋め込みタグを記述すると件名、本文の該当部分に対応した内容が入ります。<br />
            <table class="table table-striped table-sm table-bordered">
                <thead>
                    <tr>
                        <th style="width: 50%;">埋め込みタグ</th>
                        <th>内容</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(DatabaseNoticeEmbeddedTag::getDescriptionEmbeddedTags() as $embedded_tag)
                        <tr>
                            <td><code>{{$embedded_tag[0]}}</code></td>
                            <td>{{$embedded_tag[1]}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            データベース毎に埋め込みタグが利用できます。<br />
            @if($database)
                データベース：{{$database->databases_name}}<br />
                <table class="table table-striped table-sm table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 50%;">埋め込みタグ</th>
                            <th>内容</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($columns as $column)
                            @if (DatabasesColumns::isNotEmbeddedTagsColumnType($column->column_type))
                                @continue
                            @endif
                            <tr>
                                <td><code>[[X-{{$column->column_name}}]]</code></td>
                                <td>{{$column->column_name}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                データベース：なし<br />
            @endforelse
        </div>
    </div>
</div>
