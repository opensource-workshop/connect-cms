{{--
 * メール設定画面の説明blade
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ブログプラグイン
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
                    @foreach(BlogNoticeEmbeddedTag::getDescriptionEmbeddedTags() as $embedded_tag)
                        <tr>
                            <td><code>{{$embedded_tag[0]}}</code></td>
                            <td>{{$embedded_tag[1]}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

