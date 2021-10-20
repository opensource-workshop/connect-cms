<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use App\Enums\NoticeJobType;
use App\Enums\NoticeEmbeddedTag;

class BucketsMail extends Model
{
    // firstOrNew で使うためにguarded が必要だった。
    // ない場合は「Illuminate\Database\Eloquent\MassAssignmentException: bucket_id」でエラーになった。
    protected $guarded = ['buckets_id'];

    /**
     * 通知の埋め込みタグ値の配列をマージ
     */
    public static function getNoticeEmbeddedTags($frame, $bucket, $post, array $overwrite_notice_embedded_tags, string $show_method, $notice_method, $delete_comment = null) : array
    {
        $default = [
            NoticeEmbeddedTag::method => NoticeJobType::getDescription($notice_method),
            NoticeEmbeddedTag::title => $post->title,
            NoticeEmbeddedTag::url => url('/') . '/plugin/' . $bucket->plugin_name . '/' . $show_method . '/' . $frame->page_id . '/' . $frame->id . '/' . $post->id . '#frame-' . $frame->id,
            NoticeEmbeddedTag::delete_comment => $delete_comment,
        ];

        // 同じキーがあったら後勝ちで上書きされる。
        return array_merge($default, $overwrite_notice_embedded_tags);
    }

    /**
     * フォーマット済みの投稿通知の本文を取得
     */
    // public function getFormatedNoticeBody($frame, $bucket, $post, $show_method, $notice_method, $delete_comment = null)
    // {
    //     $notice_body = $this->notice_body;

    //     // [[method]]
    //     $notice_body = str_ireplace('[[method]]', NoticeJobType::getDescription($notice_method), $notice_body);

    //     // [[title]]
    //     $notice_body = str_ireplace('[[title]]', $post->title, $notice_body);

    //     // [[url]]
    //     $url = url('/') . '/plugin/' . $bucket->plugin_name . '/' . $show_method . '/' . $frame->page_id . '/' . $frame->id . '/' . $post->id . '#frame-' . $frame->id;
    //     $notice_body = str_ireplace('[[url]]', $url, $notice_body);

    //     // [[delete_comment]]
    //     $notice_body = str_ireplace('[[delete_comment]]', $delete_comment, $notice_body);

    //     return $notice_body;
    // }
    public function getFormatedNoticeBody(array $notice_embedded_tags)
    {
        return $this->replaceEmbeddedTags($this->notice_body, $notice_embedded_tags);
    }

    /**
     * フォーマット済みの関連記事通知の本文を取得
     */
    // public function getFormatedRelateBody($frame, $bucket, $post, $show_method)
    // {
    //     $relate_body = $this->relate_body;

    //     // [[title]]
    //     $relate_body = str_ireplace('[[title]]', $post->title, $relate_body);

    //     // [[url]]
    //     $url = url('/') . '/plugin/' . $bucket->plugin_name . '/' . $show_method . '/' . $frame->page_id . '/' . $frame->id . '/' . $post->id . '#frame-' . $frame->id;
    //     $relate_body = str_ireplace('[[url]]', $url, $relate_body);

    //     return $relate_body;
    // }
    public function getFormatedRelateBody(array $notice_embedded_tags)
    {
        return $this->replaceEmbeddedTags($this->relate_body, $notice_embedded_tags);
    }

    /**
     * フォーマット済みの承認通知の本文を取得
     */
    // public function getFormatedApprovalBody($frame, $bucket, $post, $show_method)
    // {
    //     $approval_body = $this->approval_body;

    //     // [[title]]
    //     $approval_body = str_ireplace('[[title]]', $post->title, $approval_body);

    //     // [[url]]
    //     $url = url('/') . '/plugin/' . $bucket->plugin_name . '/' . $show_method . '/' . $frame->page_id . '/' . $frame->id . '/' . $post->id . '#frame-' . $frame->id;
    //     $approval_body = str_ireplace('[[url]]', $url, $approval_body);

    //     return $approval_body;
    // }
    public function getFormatedApprovalBody(array $notice_embedded_tags)
    {
        return $this->replaceEmbeddedTags($this->approval_body, $notice_embedded_tags);
    }

    /**
     * フォーマット済みの承認済み通知の本文を取得
     */
    // public function getFormatedApprovedBody($frame, $bucket, $post, $show_method)
    // {
    //     $approved_body = $this->approved_body;

    //     // [[title]]
    //     $approved_body = str_ireplace('[[title]]', $post->title, $approved_body);

    //     // [[url]]
    //     $url = url('/') . '/plugin/' . $bucket->plugin_name . '/' . $show_method . '/' . $frame->page_id . '/' . $frame->id . '/' . $post->id . '#frame-' . $frame->id;
    //     $approved_body = str_ireplace('[[url]]', $url, $approved_body);

    //     return $approved_body;
    // }
    public function getFormatedApprovedBody(array $notice_embedded_tags)
    {
        return $this->replaceEmbeddedTags($this->approved_body, $notice_embedded_tags);
    }

    /**
     * 本文の埋め込みタグを置換
     */
    private function replaceEmbeddedTags($body, array $notice_embedded_tags)
    {
        foreach ($notice_embedded_tags as $tag => $value) {
            $body = str_ireplace("[[{$tag}]]", $value, $body);
        }
        return $body;
    }
}
