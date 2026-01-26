<?php

namespace Tests\Unit\Mail\Transport;

use App\Mail\Transport\MicrosoftGraphTransport;
use App\Services\Ms365MailOauth2Service;
use App\Models\Core\Configs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Swift_Mime_SimpleMessage;
use Tests\TestCase;

class MicrosoftGraphTransportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var MicrosoftGraphTransport
     */
    private $transport;

    /**
     * @var Ms365MailOauth2Service
     */
    private $oauth2_service;

    /**
     * 初期設定
     */
    protected function setUp(): void
    {
        parent::setUp();

        // OAuth2設定を作成（テスト用のダミーデータ）
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'tenant_id',
            'value' => 'test-tenant-id',
        ]);
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'client_id',
            'value' => 'test-client-id',
        ]);
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'client_secret',
            'value' => encrypt('test-client-secret'),
        ]);
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'mail_from_address',
            'value' => 'sender@example.com',
        ]);
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'access_token',
            'value' => encrypt('test-access-token'),
        ]);
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'token_expires_at',
            'value' => now()->addHours(1)->toDateTimeString(),
        ]);
        Configs::create([
            'category' => 'mail_oauth2_ms365_app',
            'name' => 'is_connected',
            'value' => '1',
        ]);

        // 実際のOAuth2サービスを使用
        $this->oauth2_service = new Ms365MailOauth2Service();

        // トランスポート作成
        $this->transport = new MicrosoftGraphTransport(
            $this->oauth2_service,
            'sender@example.com'
        );
    }

    /**
     * 基本的なメッセージモックを作成
     *
     * @param array $overrides デフォルト値を上書きする設定
     * @return Swift_Mime_SimpleMessage
     */
    private function createBasicMessageMock(array $overrides = []): Swift_Mime_SimpleMessage
    {
        $defaults = [
            'subject' => '件名',
            'contentType' => 'text/plain',
            'body' => '本文',
            'to' => ['to@example.com' => 'To User'],
            'cc' => null,
            'bcc' => null,
            'replyTo' => null,
            'from' => null,
            'children' => [],
        ];

        $config = array_merge($defaults, $overrides);

        $message = $this->createMock(Swift_Mime_SimpleMessage::class);
        $message->method('getSubject')->willReturn($config['subject']);
        $message->method('getContentType')->willReturn($config['contentType']);
        $message->method('getBody')->willReturn($config['body']);
        $message->method('getTo')->willReturn($config['to']);
        $message->method('getCc')->willReturn($config['cc']);
        $message->method('getBcc')->willReturn($config['bcc']);
        $message->method('getReplyTo')->willReturn($config['replyTo']);
        $message->method('getFrom')->willReturn($config['from']);
        $message->method('getChildren')->willReturn($config['children']);

        return $message;
    }

    /**
     * メッセージ変換テスト：基本
     */
    public function testConvertToGraphMessageBasic(): void
    {
        // Swift_Mime_SimpleMessageのモック作成
        $message = $this->createMock(Swift_Mime_SimpleMessage::class);
        $message->method('getSubject')->willReturn('テスト件名');
        $message->method('getContentType')->willReturn('text/plain');
        $message->method('getBody')->willReturn('テスト本文');
        $message->method('getTo')->willReturn(['to@example.com' => 'To User']);
        $message->method('getCc')->willReturn(null);
        $message->method('getBcc')->willReturn(null);
        $message->method('getReplyTo')->willReturn(null);
        $message->method('getChildren')->willReturn([]);

        // リフレクションでprotectedメソッドにアクセス
        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertToGraphMessage');
        $method->setAccessible(true);

        $graph_message = $method->invoke($this->transport, $message);

        // 件名
        $this->assertEquals('テスト件名', $graph_message['subject']);

        // 本文
        $this->assertEquals('Text', $graph_message['body']['contentType']);
        $this->assertEquals('テスト本文', $graph_message['body']['content']);

        // 宛先
        $this->assertCount(1, $graph_message['toRecipients']);
        $this->assertEquals('to@example.com', $graph_message['toRecipients'][0]['emailAddress']['address']);
        $this->assertEquals('To User', $graph_message['toRecipients'][0]['emailAddress']['name']);

        // CC、BCC、Reply-Toがないことを確認
        $this->assertArrayNotHasKey('ccRecipients', $graph_message);
        $this->assertArrayNotHasKey('bccRecipients', $graph_message);
        $this->assertArrayNotHasKey('replyTo', $graph_message);
    }

    /**
     * メッセージ変換テスト：CC、BCC、Reply-To付き
     */
    public function testConvertToGraphMessageWithCcBccReplyTo(): void
    {
        $message = $this->createMock(Swift_Mime_SimpleMessage::class);
        $message->method('getSubject')->willReturn('件名');
        $message->method('getContentType')->willReturn('text/html');
        $message->method('getBody')->willReturn('<p>本文</p>');
        $message->method('getTo')->willReturn(['to@example.com' => 'To User']);
        $message->method('getCc')->willReturn(['cc@example.com' => 'CC User']);
        $message->method('getBcc')->willReturn(['bcc@example.com' => 'BCC User']);
        $message->method('getReplyTo')->willReturn(['reply@example.com' => 'Reply User']);
        $message->method('getChildren')->willReturn([]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertToGraphMessage');
        $method->setAccessible(true);

        $graph_message = $method->invoke($this->transport, $message);

        // HTMLコンテンツタイプ
        $this->assertEquals('HTML', $graph_message['body']['contentType']);

        // CC
        $this->assertCount(1, $graph_message['ccRecipients']);
        $this->assertEquals('cc@example.com', $graph_message['ccRecipients'][0]['emailAddress']['address']);

        // BCC
        $this->assertCount(1, $graph_message['bccRecipients']);
        $this->assertEquals('bcc@example.com', $graph_message['bccRecipients'][0]['emailAddress']['address']);

        // Reply-To
        $this->assertCount(1, $graph_message['replyTo']);
        $this->assertEquals('reply@example.com', $graph_message['replyTo'][0]['emailAddress']['address']);
    }

    /**
     * メッセージ変換テスト：複数宛先
     */
    public function testConvertToGraphMessageWithMultipleRecipients(): void
    {
        $message = $this->createMock(Swift_Mime_SimpleMessage::class);
        $message->method('getSubject')->willReturn('件名');
        $message->method('getContentType')->willReturn('text/plain');
        $message->method('getBody')->willReturn('本文');
        $message->method('getTo')->willReturn([
            'to1@example.com' => 'To User 1',
            'to2@example.com' => 'To User 2',
            'to3@example.com' => 'To User 3',
        ]);
        $message->method('getCc')->willReturn(null);
        $message->method('getBcc')->willReturn(null);
        $message->method('getReplyTo')->willReturn(null);
        $message->method('getChildren')->willReturn([]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertToGraphMessage');
        $method->setAccessible(true);

        $graph_message = $method->invoke($this->transport, $message);

        // 3人の宛先
        $this->assertCount(3, $graph_message['toRecipients']);
        $this->assertEquals('to1@example.com', $graph_message['toRecipients'][0]['emailAddress']['address']);
        $this->assertEquals('to2@example.com', $graph_message['toRecipients'][1]['emailAddress']['address']);
        $this->assertEquals('to3@example.com', $graph_message['toRecipients'][2]['emailAddress']['address']);
    }

    /**
     * アドレス変換テスト：名前なし
     */
    public function testConvertAddressesWithoutName(): void
    {
        $addresses = [
            'user@example.com' => null,
        ];

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertAddresses');
        $method->setAccessible(true);

        $converted = $method->invoke($this->transport, $addresses);

        // 名前がない場合はメールアドレスが名前になる
        $this->assertEquals('user@example.com', $converted[0]['emailAddress']['address']);
        $this->assertEquals('user@example.com', $converted[0]['emailAddress']['name']);
    }

    /**
     * コンテンツタイプ取得テスト：HTML
     */
    public function testGetContentTypeHtml(): void
    {
        $message = $this->createMock(Swift_Mime_SimpleMessage::class);
        $message->method('getContentType')->willReturn('text/html; charset=utf-8');

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('getContentType');
        $method->setAccessible(true);

        $content_type = $method->invoke($this->transport, $message);

        $this->assertEquals('HTML', $content_type);
    }

    /**
     * コンテンツタイプ取得テスト：Plain Text
     */
    public function testGetContentTypePlain(): void
    {
        $message = $this->createMock(Swift_Mime_SimpleMessage::class);
        $message->method('getContentType')->willReturn('text/plain');

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('getContentType');
        $method->setAccessible(true);

        $content_type = $method->invoke($this->transport, $message);

        $this->assertEquals('Text', $content_type);
    }

    /**
     * メール送信テスト：成功
     */
    public function testSendSuccess(): void
    {
        // HTTPレスポンスのモック（成功）
        Http::fake([
            'https://graph.microsoft.com/*' => Http::response(null, 202),
        ]);

        // メッセージのモック
        $message = $this->createMock(Swift_Mime_SimpleMessage::class);
        $message->method('getSubject')->willReturn('件名');
        $message->method('getContentType')->willReturn('text/plain');
        $message->method('getBody')->willReturn('本文');
        $message->method('getTo')->willReturn(['to@example.com' => 'To User']);
        $message->method('getCc')->willReturn(null);
        $message->method('getBcc')->willReturn(null);
        $message->method('getReplyTo')->willReturn(null);
        $message->method('getChildren')->willReturn([]);

        // メール送信（実際のOAuth2サービスを使用）
        $result = $this->transport->send($message);

        // 送信成功数（宛先数）
        $this->assertEquals(1, $result);
    }

    /**
     * メール送信テスト：失敗
     */
    public function testSendFailure(): void
    {
        // HTTPレスポンスのモック（失敗）
        Http::fake([
            'https://graph.microsoft.com/*' => Http::response([
                'error' => [
                    'code' => 'InvalidAuthenticationToken',
                    'message' => 'Access token validation failure.',
                ]
            ], 401),
        ]);

        // メッセージのモック
        $message = $this->createMock(Swift_Mime_SimpleMessage::class);
        $message->method('getSubject')->willReturn('件名');
        $message->method('getContentType')->willReturn('text/plain');
        $message->method('getBody')->willReturn('本文');
        $message->method('getTo')->willReturn(['to@example.com' => 'To User']);
        $message->method('getCc')->willReturn(null);
        $message->method('getBcc')->willReturn(null);
        $message->method('getReplyTo')->willReturn(null);
        $message->method('getChildren')->willReturn([]);

        // メール送信失敗を期待
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('メール送信に失敗しました');

        $this->transport->send($message);
    }

    /**
     * 本文取得テスト：マルチパート（HTML優先）
     */
    public function testGetBodyMultipartHtmlPriority(): void
    {
        // HTMLパートのモック
        $html_part = $this->createMock(\Swift_Mime_MimePart::class);
        $html_part->method('getContentType')->willReturn('text/html');
        $html_part->method('getBody')->willReturn('<p>HTML本文</p>');

        // テキストパートのモック
        $text_part = $this->createMock(\Swift_Mime_MimePart::class);
        $text_part->method('getContentType')->willReturn('text/plain');
        $text_part->method('getBody')->willReturn('テキスト本文');

        // メッセージのモック（マルチパート）
        $message = $this->createMock(Swift_Mime_SimpleMessage::class);
        $message->method('getBody')->willReturn('デフォルト本文');
        $message->method('getChildren')->willReturn([$text_part, $html_part]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('getBody');
        $method->setAccessible(true);

        $body = $method->invoke($this->transport, $message);

        // HTMLパートが優先される
        $this->assertEquals('<p>HTML本文</p>', $body);
    }

    /**
     * 本文取得テスト：マルチパート（テキストのみ）
     */
    public function testGetBodyMultipartTextOnly(): void
    {
        // テキストパートのモック
        $text_part = $this->createMock(\Swift_Mime_MimePart::class);
        $text_part->method('getContentType')->willReturn('text/plain');
        $text_part->method('getBody')->willReturn('テキスト本文');

        // メッセージのモック（マルチパート）
        $message = $this->createMock(Swift_Mime_SimpleMessage::class);
        $message->method('getBody')->willReturn('デフォルト本文');
        $message->method('getChildren')->willReturn([$text_part]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('getBody');
        $method->setAccessible(true);

        $body = $method->invoke($this->transport, $message);

        // テキストパートが返される
        $this->assertEquals('テキスト本文', $body);
    }

    /**
     * 本文取得テスト：シングルパート
     */
    public function testGetBodySinglePart(): void
    {
        // メッセージのモック（シングルパート）
        $message = $this->createMock(Swift_Mime_SimpleMessage::class);
        $message->method('getBody')->willReturn('シングルパート本文');
        $message->method('getChildren')->willReturn([]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('getBody');
        $method->setAccessible(true);

        $body = $method->invoke($this->transport, $message);

        // デフォルト本文が返される
        $this->assertEquals('シングルパート本文', $body);
    }

    /**
     * Reply-To設定テスト：FromがOAuth2設定アドレスと異なる場合
     */
    public function testReplyToSetWhenFromDifferentFromOauth2Address(): void
    {
        // FromアドレスをOAuth2設定アドレス（sender@example.com）と異なるアドレスに設定
        $message = $this->createBasicMessageMock([
            'from' => ['different@example.com' => 'Different User']
        ]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertToGraphMessage');
        $method->setAccessible(true);

        $graph_message = $method->invoke($this->transport, $message);

        // Reply-Toが設定されていることを確認
        $this->assertArrayHasKey('replyTo', $graph_message);
        $this->assertCount(1, $graph_message['replyTo']);
        $this->assertEquals('different@example.com', $graph_message['replyTo'][0]['emailAddress']['address']);
        $this->assertEquals('Different User', $graph_message['replyTo'][0]['emailAddress']['name']);
    }

    /**
     * Reply-To設定テスト：FromがOAuth2設定アドレスと同じ場合
     */
    public function testReplyToNotSetWhenFromSameAsOauth2Address(): void
    {
        // FromアドレスをOAuth2設定アドレス（sender@example.com）と同じに設定
        $message = $this->createBasicMessageMock([
            'from' => ['sender@example.com' => 'Sender User']
        ]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertToGraphMessage');
        $method->setAccessible(true);

        $graph_message = $method->invoke($this->transport, $message);

        // Reply-Toが設定されていないことを確認
        $this->assertArrayNotHasKey('replyTo', $graph_message);
    }

    /**
     * Reply-To設定テスト：明示的なReply-Toが優先される
     */
    public function testExplicitReplyToTakesPriority(): void
    {
        $message = $this->createBasicMessageMock([
            // 明示的なReply-Toを設定
            'replyTo' => ['explicit-reply@example.com' => 'Explicit Reply'],
            // FromアドレスをOAuth2設定アドレスと異なるアドレスに設定
            'from' => ['different@example.com' => 'Different User']
        ]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertToGraphMessage');
        $method->setAccessible(true);

        $graph_message = $method->invoke($this->transport, $message);

        // 明示的なReply-Toが優先されていることを確認
        $this->assertArrayHasKey('replyTo', $graph_message);
        $this->assertCount(1, $graph_message['replyTo']);
        $this->assertEquals('explicit-reply@example.com', $graph_message['replyTo'][0]['emailAddress']['address']);
        $this->assertEquals('Explicit Reply', $graph_message['replyTo'][0]['emailAddress']['name']);
    }

    /**
     * Reply-To設定テスト：Fromがnullの場合
     */
    public function testReplyToNotSetWhenFromIsNull(): void
    {
        // Fromがnull（デフォルト値）
        $message = $this->createBasicMessageMock();

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertToGraphMessage');
        $method->setAccessible(true);

        $graph_message = $method->invoke($this->transport, $message);

        // Reply-Toが設定されていないことを確認
        $this->assertArrayNotHasKey('replyTo', $graph_message);
    }

    /**
     * Reply-To設定テスト：Fromが空配列の場合
     */
    public function testReplyToNotSetWhenFromIsEmpty(): void
    {
        // Fromが空配列
        $message = $this->createBasicMessageMock([
            'from' => []
        ]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertToGraphMessage');
        $method->setAccessible(true);

        $graph_message = $method->invoke($this->transport, $message);

        // Reply-Toが設定されていないことを確認
        $this->assertArrayNotHasKey('replyTo', $graph_message);
    }


    /**
     * 添付ファイル変換テスト：単一ファイル
     */
    public function testConvertAttachmentsWithSingleFile(): void
    {
        // 添付ファイルのモック
        $attachment = $this->createMock(\Swift_Attachment::class);
        $attachment->method('getFilename')->willReturn('test.pdf');
        $attachment->method('getContentType')->willReturn('application/pdf');
        $attachment->method('getBody')->willReturn('PDF content here');

        // メッセージのモック
        $message = $this->createBasicMessageMock([
            'children' => [$attachment]
        ]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertAttachments');
        $method->setAccessible(true);

        $attachments = $method->invoke($this->transport, $message);

        // 1件の添付ファイル
        $this->assertCount(1, $attachments);
        $this->assertEquals('#microsoft.graph.fileAttachment', $attachments[0]['@odata.type']);
        $this->assertEquals('test.pdf', $attachments[0]['name']);
        $this->assertEquals('application/pdf', $attachments[0]['contentType']);
        $this->assertEquals(base64_encode('PDF content here'), $attachments[0]['contentBytes']);
    }

    /**
     * 添付ファイル変換テスト：複数ファイル
     */
    public function testConvertAttachmentsWithMultipleFiles(): void
    {
        // 添付ファイル1
        $attachment1 = $this->createMock(\Swift_Attachment::class);
        $attachment1->method('getFilename')->willReturn('document.pdf');
        $attachment1->method('getContentType')->willReturn('application/pdf');
        $attachment1->method('getBody')->willReturn('PDF content');

        // 添付ファイル2
        $attachment2 = $this->createMock(\Swift_Attachment::class);
        $attachment2->method('getFilename')->willReturn('image.png');
        $attachment2->method('getContentType')->willReturn('image/png');
        $attachment2->method('getBody')->willReturn('PNG content');

        // メッセージのモック
        $message = $this->createBasicMessageMock([
            'children' => [$attachment1, $attachment2]
        ]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertAttachments');
        $method->setAccessible(true);

        $attachments = $method->invoke($this->transport, $message);

        // 2件の添付ファイル
        $this->assertCount(2, $attachments);

        // 1つ目の添付ファイル
        $this->assertEquals('document.pdf', $attachments[0]['name']);
        $this->assertEquals('application/pdf', $attachments[0]['contentType']);

        // 2つ目の添付ファイル
        $this->assertEquals('image.png', $attachments[1]['name']);
        $this->assertEquals('image/png', $attachments[1]['contentType']);
    }

    /**
     * 添付ファイル変換テスト：添付なし
     */
    public function testConvertAttachmentsWithNoAttachment(): void
    {
        // 添付ファイルなしのメッセージ
        $message = $this->createBasicMessageMock([
            'children' => []
        ]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertAttachments');
        $method->setAccessible(true);

        $attachments = $method->invoke($this->transport, $message);

        // 空配列
        $this->assertCount(0, $attachments);
    }

    /**
     * 添付ファイル変換テスト：添付ファイルとMimePartが混在
     */
    public function testConvertAttachmentsWithMixedChildren(): void
    {
        // HTMLパート（添付ファイルではない）
        $html_part = $this->createMock(\Swift_Mime_MimePart::class);
        $html_part->method('getContentType')->willReturn('text/html');
        $html_part->method('getBody')->willReturn('<p>HTML本文</p>');

        // 添付ファイル
        $attachment = $this->createMock(\Swift_Attachment::class);
        $attachment->method('getFilename')->willReturn('file.txt');
        $attachment->method('getContentType')->willReturn('text/plain');
        $attachment->method('getBody')->willReturn('File content');

        // メッセージのモック
        $message = $this->createBasicMessageMock([
            'children' => [$html_part, $attachment]
        ]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertAttachments');
        $method->setAccessible(true);

        $attachments = $method->invoke($this->transport, $message);

        // 添付ファイルのみ（Swift_Attachmentのみ）が返される
        $this->assertCount(1, $attachments);
        $this->assertEquals('file.txt', $attachments[0]['name']);
    }

    /**
     * メッセージ変換テスト：添付ファイル付き統合テスト
     */
    public function testConvertToGraphMessageWithAttachment(): void
    {
        // 添付ファイルのモック
        $attachment = $this->createMock(\Swift_Attachment::class);
        $attachment->method('getFilename')->willReturn('report.xlsx');
        $attachment->method('getContentType')->willReturn('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $attachment->method('getBody')->willReturn('Excel content');

        // メッセージのモック
        $message = $this->createBasicMessageMock([
            'subject' => '添付ファイル付きメール',
            'body' => '添付ファイルをご確認ください。',
            'children' => [$attachment]
        ]);

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertToGraphMessage');
        $method->setAccessible(true);

        $graph_message = $method->invoke($this->transport, $message);

        // 基本的なメッセージ内容
        $this->assertEquals('添付ファイル付きメール', $graph_message['subject']);
        $this->assertEquals('添付ファイルをご確認ください。', $graph_message['body']['content']);

        // 添付ファイルが含まれることを確認
        $this->assertArrayHasKey('attachments', $graph_message);
        $this->assertCount(1, $graph_message['attachments']);
        $this->assertEquals('report.xlsx', $graph_message['attachments'][0]['name']);
        $this->assertEquals('#microsoft.graph.fileAttachment', $graph_message['attachments'][0]['@odata.type']);
    }

    /**
     * メッセージ変換テスト：添付ファイルなし（attachmentsキーが含まれない）
     */
    public function testConvertToGraphMessageWithoutAttachment(): void
    {
        // 添付ファイルなしのメッセージ
        $message = $this->createBasicMessageMock();

        $reflection = new \ReflectionClass($this->transport);
        $method = $reflection->getMethod('convertToGraphMessage');
        $method->setAccessible(true);

        $graph_message = $method->invoke($this->transport, $message);

        // 添付ファイルがない場合はattachmentsキーが存在しない
        $this->assertArrayNotHasKey('attachments', $graph_message);
    }
}
