{{--
 * 課題管理記事詳細のレポート履歴テンプレート。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 石垣　佑樹 <ishigaki@opensource-workshop.co.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
--}}
<table class="table table-bordered table-sm report_table">
    <tbody>
        <tr>
            <th>{{$user_status->getStstusPostTimeName()}}</th>
            <td>{{$user_status->created_at}}</td>
        </tr>
        @if ($tool->isUseFunction($user_status->task_status, 'file'))
            <tr>
                <th>{{$user_status->getUploadFileName()}}</th>
                @if (empty($user_status->upload_id))
                    <td>なし</td>
                @else
                    <td><a href="{{url('/')}}/file/{{$user_status->upload_id}}" target="_blank">{{$user_status->upload->client_original_name}}</a></td>
                @endif
            </tr>
        @endif
        @if ($user_status->hasGrade())
            <tr>
                <th>評価</th>
                <td><span class="text-danger font-weight-bold">{{$user_status->grade}}</span></td>
            </tr>
        @endif
            @if ($tool->isUseFunction($user_status->task_status, 'comment'))
            <tr>
                <th>コメント</th>
                <td>{!!nl2br(e($user_status->comment))!!}</td>
            </tr>
        @endif
    </tbody>
</table>
