<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

use App\Enums\NoticeJobType;

class BucketsMail extends Model
{
    // firstOrNew で使うためにguarded が必要だった。
    // ない場合は「Illuminate\Database\Eloquent\MassAssignmentException: bucket_id」でエラーになった。
    protected $guarded = ['buckets_id'];

    /**
     * フォーマット済みの本文を取得
     */
    public function getFormatedNoticeBody($frame, $bucket, $id, $show_method, $notice_method, $delete_comment = null)
    {
        $notice_body = $this->notice_body;

        // {X-METHOD}
        $notice_body = str_ireplace('{X-METHOD}', NoticeJobType::getDescription($notice_method), $notice_body);

        // {X-URL}
        $url = url('/') . '/plugin/' . $bucket->plugin_name . '/' . $show_method . '/' . $frame->page_id . '/' . $frame->id . '/' . $id . '#frame-' . $frame->id;
        $notice_body = str_ireplace('{X-URL}', $url, $notice_body);

        // {X-DELETE-COMMENT}
        $notice_body = str_ireplace('{X-DELETE-COMMENT}', $delete_comment, $notice_body);

        return $notice_body;
    }
}
