<?php

namespace App\Models\Common;

use App\Enums\NoticeJobType;
use App\Enums\NoticeEmbeddedTag;
use App\Enums\UserStatus;
use App\Models\Core\Configs;
use App\Plugins\Manage\UserManage\UsersTool;
use App\User;
use App\UserableNohistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * バケツメールのモデル
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 共通
 * @package Model
 * @property string $plugin_name getPluginNameAttribute()
 */
class BucketsMail extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;
    use HasFactory;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'buckets_id',
        'notice_on',
        'notice_create',
        'notice_update',
        'notice_delete',
        'notice_addresses',
        'notice_everyone',
        'notice_groups',
        'notice_subject',
        'notice_body',
        'relate_on',
        'relate_subject',
        'relate_body',
        'approval_on',
        'approval_addresses',
        'approval_groups',
        'approval_subject',
        'approval_body',
        'approved_on',
        'approved_author',
        'approved_addresses',
        'approved_groups',
        'approved_subject',
        'approved_body',
    ];

    /**
     * バケツから plugin_name 取得
     */
    public function getPluginNameAttribute(): string
    {
        $bucket = Buckets::findOrNew($this->buckets_id);
        return $bucket->plugin_name;
    }

    /**
     * 通知の埋め込みタグ値の配列をマージ
     */
    public static function getNoticeEmbeddedTags($frame, $bucket, $post, array $overwrite_notice_embedded_tags, string $show_method, $notice_method, $delete_comment = null) : array
    {
        $configs = Configs::getSharedConfigs();

        $default = [
            NoticeEmbeddedTag::site_name => Configs::getConfigsValue($configs, 'base_site_name'),
            NoticeEmbeddedTag::method => NoticeJobType::getDescription($notice_method),
            NoticeEmbeddedTag::title => $post->title,
            NoticeEmbeddedTag::url => url('/') . '/plugin/' . $bucket->plugin_name . '/' . $show_method . '/' . $frame->page_id . '/' . $frame->id . '/' . $post->id . '#frame-' . $frame->id,
            NoticeEmbeddedTag::delete_comment => $delete_comment,
            NoticeEmbeddedTag::created_name => $post->created_name,
            NoticeEmbeddedTag::created_at => $post->created_at,
            NoticeEmbeddedTag::updated_name => $post->updated_name,
            NoticeEmbeddedTag::updated_at => $post->updated_at,
        ];
        // post に body が存在すれば、変換対象とする。
        // body が存在するかの判定が、項目を取ってみてのnull かどうかで判定。（他の方法があれば要検討）
        // その際は、HTML 改行タグを改行コードに変換し、その後にタグを取り除くことで、メールの本文に挿入するテキストにできる。
        // html_entity_decode で、引用の > などをdecode する。（DB上は &gt; 等で格納しているため）
        if (!empty($post->body)) {
            $default[NoticeEmbeddedTag::body] = self::stripTagsWysiwyg($post->body);
        }

        // 同じキーがあったら後勝ちで上書きされる。
        return array_merge($default, $overwrite_notice_embedded_tags);
    }

    /**
     * wysiwygをstrip_tags
     */
    public static function stripTagsWysiwyg(?string $body): string
    {
        return strip_tags(preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/i', "\n", html_entity_decode($body)));
    }

    /**
     * フォーマット済みの件名を取得
     *
     * @param string|null $subject
     * @param array $notice_embedded_tags
     * @return void
     */
    public function getFormattedSubject(string|null $subject, array $notice_embedded_tags)
    {
        if ($subject === null || $subject === '') {
            /**
             * 件名が未設定の場合、Laravelのデフォルト値（「Post Notice」等）が設定されてわかりづらい為、【件名未設定】を設定する。
             * ※別途、画面側でバリデーションを追加する為、件名が空の状態は今後、基本的にはありえないが、既存運用で件名が空のデータがある場合の対策として実装する。
             */
            return '【件名未設定】';
        }
        return $this->replaceEmbeddedTags($subject, $notice_embedded_tags);
    }

    /**
     * フォーマット済みの投稿通知の本文を取得
     */
    public function getFormattedNoticeBody(array $notice_embedded_tags)
    {
        return $this->replaceEmbeddedTags($this->notice_body, $notice_embedded_tags);
    }

    /**
     * フォーマット済みの関連記事通知の本文を取得
     */
    public function getFormattedRelateBody(array $notice_embedded_tags)
    {
        return $this->replaceEmbeddedTags($this->relate_body, $notice_embedded_tags);
    }

    /**
     * フォーマット済みの承認通知の本文を取得
     */
    public function getFormattedApprovalBody(array $notice_embedded_tags)
    {
        return $this->replaceEmbeddedTags($this->approval_body, $notice_embedded_tags);
    }

    /**
     * フォーマット済みの承認済み通知の本文を取得
     */
    public function getFormattedApprovedBody(array $notice_embedded_tags)
    {
        return $this->replaceEmbeddedTags($this->approved_body, $notice_embedded_tags);
    }

    /**
     * 本文の埋め込みタグを置換
     */
    private function replaceEmbeddedTags($body, array $notice_embedded_tags)
    {
        return NoticeEmbeddedTag::replaceEmbeddedTags($body, $notice_embedded_tags);
    }

    /**
     * 送信者メールとグループから、通知するメールアドレス取得
     */
    public function getEmailFromAddressesAndGroups(?string $addresses, ?string $notice_groups, ?int $notice_everyone = 0) : array
    {
        // 送信メール
        $notice_addresses = explode(',', $addresses);

        // グループユーザのメール取得
        $group_user_emails = self::getEmailGroups($notice_groups);

        // 全ユーザに通知ONの場合、メール取得
        $all_user_emails = [];
        if ($notice_everyone) {
            $all_user_emails = User::select('users.email')
                ->where('users.status', UserStatus::active)
                ->whereNotNull('users.email')
                ->pluck('users.email')
                ->toArray();
        }

        // [debug]
        // \Log::debug(var_export($notice_addresses, true));
        // \Log::debug(var_export($group_user_emails, true));
        // \Log::debug(var_export($all_user_emails, true));

        $notice_addresses = array_merge($notice_addresses, $group_user_emails, $all_user_emails);
        // array_filter()でarrayの空要素削除
        $notice_addresses = array_filter($notice_addresses);
        // 重複メールアドレス削除
        $notice_addresses = array_unique($notice_addresses);

        return $notice_addresses;
    }

    /**
     * グループから、通知するメールアドレス取得
     */
    public static function getEmailGroups(?string $notice_groups) : array
    {
        // グループ全員のメール取得
        $groups_ids = explode(UsersTool::CHECKBOX_SEPARATOR, $notice_groups);
        // array_filter()でarrayの空要素削除
        $groups_ids = array_filter($groups_ids);

        // グループユーザのメール取得
        $group_user_emails = [];
        if (! empty($groups_ids)) {
            $group_user_emails = GroupUser::select('users.email')
                ->join('users', function ($join) {
                    $join->on('users.id', '=', 'group_users.user_id')
                        ->where('users.status', UserStatus::active)
                        ->whereNotNull('users.email');
                })
                ->whereIn('group_users.group_id', $groups_ids)
                ->pluck('users.email')
                ->toArray();
        }

        // array_filter()でarrayの空要素削除
        $group_user_emails = array_filter($group_user_emails);
        // 重複メールアドレス削除
        $group_user_emails = array_unique($group_user_emails);

        return $group_user_emails;
    }

    /**
     * 承認済み通知の 送信者メール,グループ,投稿者へ通知 から、通知するメールアドレス取得
     */
    public function getApprovedEmailFromAddressesAndGroups(?string $addresses, ?string $approved_groups, $created_id) : array
    {
        // 送信者メールとグループから、通知するメールアドレス取得
        $approved_addresses = $this->getEmailFromAddressesAndGroups($addresses, $approved_groups);

        // 投稿者へ通知する
        $approved_author_email = [];
        if ($this->approved_author) {
            $post_user = User::where('id', $created_id)
                ->where('users.status', UserStatus::active)
                ->whereNotNull('users.email')
                ->first();

            if ($post_user) {
                $approved_author_email[] = $post_user->email;
            }
        }

        $approved_addresses = array_merge($approved_addresses, $approved_author_email);
        // array_filter()でarrayの空要素削除
        $approved_addresses = array_filter($approved_addresses);
        // 重複メールアドレス削除
        $approved_addresses = array_unique($approved_addresses);

        return $approved_addresses;
    }
}
