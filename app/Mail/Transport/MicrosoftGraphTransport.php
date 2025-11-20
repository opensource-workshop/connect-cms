<?php

namespace App\Mail\Transport;

use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Ms365MailOauth2Service;

/**
 * Microsoft Graph API メールトランスポート
 *
 * OAuth2認証を使用してMicrosoft Graph API経由でメール送信
 *
 * @author OpenSource-WorkShop Co.,Ltd.
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メールトランスポート
 * @package Mail\Transport
 */
class MicrosoftGraphTransport extends Transport
{
    /**
     * コンテンツタイプ定数
     */
    private const CONTENT_TYPE_HTML = 'text/html';
    private const CONTENT_TYPE_PLAIN = 'text/plain';
    private const GRAPH_CONTENT_TYPE_HTML = 'HTML';
    private const GRAPH_CONTENT_TYPE_TEXT = 'Text';

    /**
     * Microsoft Graph API エンドポイント
     */
    private const GRAPH_API_ENDPOINT = 'https://graph.microsoft.com/v1.0/users';

    /**
     * @var Ms365MailOauth2Service
     */
    protected $oauth2_service;

    /**
     * @var string 送信者メールアドレス
     */
    protected $from_address;

    /**
     * コンストラクタ
     */
    public function __construct(Ms365MailOauth2Service $oauth2_service, string $from_address)
    {
        $this->oauth2_service = $oauth2_service;
        $this->from_address = $from_address;
    }

    /**
     * メール送信
     *
     * @param Swift_Mime_SimpleMessage $message
     * @param string[] $failed_recipients
     * @return int 送信成功数
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failed_recipients = null): int
    {
        $this->beforeSendPerformed($message);

        try {
            // アクセストークン取得
            $access_token = $this->oauth2_service->getValidAccessToken();

            // Microsoft Graph API用のメッセージ形式に変換
            $graph_message = $this->convertToGraphMessage($message);

            // Microsoft Graph API でメール送信
            $response = Http::withToken($access_token)
                ->post(self::GRAPH_API_ENDPOINT . "/{$this->from_address}/sendMail", [
                    'message' => $graph_message,
                    'saveToSentItems' => true
                ]);

            if ($response->successful()) {
                $this->sendPerformed($message);
                return $this->numberOfRecipients($message);
            } else {
                // セキュリティ上、詳細なエラーレスポンスはログにのみ記録し、ユーザーには表示しない
                Log::error('Microsoft Graph API mail send error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('メール送信に失敗しました。システム管理者にお問い合わせください。');
            }
        } catch (\Exception $e) {
            Log::error('Microsoft Graph Transport error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Swift_Mime_SimpleMessage を Microsoft Graph API 形式に変換
     *
     * @param Swift_Mime_SimpleMessage $message
     * @return array
     */
    protected function convertToGraphMessage(Swift_Mime_SimpleMessage $message): array
    {
        $graph_message = [
            'subject' => $message->getSubject(),
            'body' => [
                'contentType' => $this->getContentType($message),
                'content' => $this->getBody($message)
            ],
            'toRecipients' => $this->convertAddresses($message->getTo()),
        ];

        // CC
        if ($message->getCc()) {
            $graph_message['ccRecipients'] = $this->convertAddresses($message->getCc());
        }

        // BCC
        if ($message->getBcc()) {
            $graph_message['bccRecipients'] = $this->convertAddresses($message->getBcc());
        }

        // From (Reply-To)
        if ($message->getReplyTo()) {
            $graph_message['replyTo'] = $this->convertAddresses($message->getReplyTo());
        }

        return $graph_message;
    }

    /**
     * メールアドレス配列をGraph API形式に変換
     *
     * @param array $addresses
     * @return array
     */
    protected function convertAddresses($addresses): array
    {
        $converted = [];
        foreach ($addresses as $email => $name) {
            $converted[] = [
                'emailAddress' => [
                    'address' => $email,
                    'name' => $name ?: $email
                ]
            ];
        }
        return $converted;
    }

    /**
     * コンテンツタイプを取得
     *
     * @param Swift_Mime_SimpleMessage $message
     * @return string
     */
    protected function getContentType(Swift_Mime_SimpleMessage $message): string
    {
        $content_type = $message->getContentType();

        if (strpos($content_type, self::CONTENT_TYPE_HTML) !== false) {
            return self::GRAPH_CONTENT_TYPE_HTML;
        }

        return self::GRAPH_CONTENT_TYPE_TEXT;
    }

    /**
     * メール本文を取得
     *
     * @param Swift_Mime_SimpleMessage $message
     * @return string
     */
    protected function getBody(Swift_Mime_SimpleMessage $message): string
    {
        $body = $message->getBody();

        // マルチパートの場合、HTMLパートを優先
        if ($message->getChildren()) {
            foreach ($message->getChildren() as $child) {
                if (strpos($child->getContentType(), self::CONTENT_TYPE_HTML) !== false) {
                    return $child->getBody();
                }
            }
            // HTMLが見つからなければテキストパート
            foreach ($message->getChildren() as $child) {
                if (strpos($child->getContentType(), self::CONTENT_TYPE_PLAIN) !== false) {
                    return $child->getBody();
                }
            }
        }

        return $body;
    }
}
