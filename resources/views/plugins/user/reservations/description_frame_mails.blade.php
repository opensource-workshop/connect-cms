{{--
 * メール設定画面の説明blade
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設予約プラグイン
 *
 * copy by resources\views\plugins\common\description_frame_mails_common.blade.php
--}}
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
                    @foreach(ReservationNoticeEmbeddedTag::getDescriptionEmbeddedTags() as $embedded_tag)
                        <tr>
                            <td><code>{{$embedded_tag[0]}}</code></td>
                            <td>{{$embedded_tag[1]}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            施設毎に埋め込みタグが利用できます。<br />
            @forelse($grouped_facilities_columns_set_ids as $columns_set_id => $grouped_facilities)
                施設：{{$grouped_facilities->pluck('facility_name')->implode(',')}}<br />
                @php
                    $columns = $columns_set_columns->where('columns_set_id', $columns_set_id);
                @endphp
                <table class="table table-striped table-sm table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 50%;">埋め込みタグ</th>
                            <th>内容</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($columns as $column)
                            @if ($column->isNotEmbeddedTagsColumnType())
                                @continue
                            @endif
                            <tr>
                                <td><code>[[X-{{$column->column_name}}]]</code></td>
                                <td>{{$column->column_name}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @empty
                施設：なし<br />
            @endforelse
        </div>
    </div>
</div>

