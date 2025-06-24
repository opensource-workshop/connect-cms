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
                <td>{!!nl2br(e($user_status->comment))!!}
                {{-- 文字数、単語数の表示 --}}
                @php
                    $should_show_word_count = $tool->isUseFunction($user_status->task_status, 'show_word_count');
                    $should_show_char_count = $tool->isUseFunction($user_status->task_status, 'show_char_count');
                    $word_count = $user_status->word_count;
                    $char_count = $user_status->char_count;
                @endphp
                @if ($should_show_word_count || $should_show_char_count)
                    <div><small class="text-muted">
                        @if ($should_show_word_count)
                            {{ $word_count }}単語
                        @endif
                        @if ($should_show_word_count && $should_show_char_count)
                            /
                        @endif
                        @if ($should_show_char_count)
                            {{ $char_count }}文字
                        @endif
                    </small></div>
                @endif
                </td>
            </tr>
        @endif
    </tbody>
</table>
