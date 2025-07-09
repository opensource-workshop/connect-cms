<?php

namespace Tests\Unit\Models\Common;

use App\Enums\NoticeEmbeddedTag;
use App\Models\Common\BucketsMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BucketsMailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 初期設定
     */
    protected function setUp(): void
    {
        $this->refreshApplication();
        // $this->refreshDatabase();

        parent::setUp();
    }

    /**
     * getFormattedSubject()テスト
     */
    public function testGetFormattedSubject(): void
    {
        $subject = '【[[site_name]]】通知 [[body]] [[url]] [[delete_comment]]';
        $notice_embedded_tags = [
            NoticeEmbeddedTag::site_name => 'サンプルサイト',
            // NoticeEmbeddedTag::method => NoticeJobType::getDescription($notice_method),
            NoticeEmbeddedTag::title => 'テストタイトル',
            NoticeEmbeddedTag::body => 'HTMLを除いた本文',
            NoticeEmbeddedTag::url => 'http://localhost/plugin/xxxx',
            NoticeEmbeddedTag::delete_comment => '削除時コメント',
            NoticeEmbeddedTag::created_name => '一般',
            // NoticeEmbeddedTag::created_at => $post->created_at,
            NoticeEmbeddedTag::updated_name => 'モデレータ',
            // NoticeEmbeddedTag::updated_at => $post->updated_at,
        ];

        $mail = new BucketsMail();
        $subject = $mail->getFormattedSubject($subject, $notice_embedded_tags);

        // [debug]
        // var_dump($subject);

        // $this->assertStringContainsString('[[body]]', $subject, '件名の[[body]]は置換されない事');
        // $this->assertStringContainsString('[[url]]', $subject, '件名の[[url]]は置換されない事');
        // $this->assertStringContainsString('[[delete_comment]]', $subject, '件名の[[delete_comment]]は置換されない事');
        $this->assertStringContainsString('【サンプルサイト】', $subject, '件名の[[site_name]]は置換される事');
    }

    /**
     * getFormattedSubject()でnull値を渡した場合のテスト
     */
    public function testGetFormattedSubjectWithNull(): void
    {
        $notice_embedded_tags = [
            NoticeEmbeddedTag::site_name => 'サンプルサイト',
            NoticeEmbeddedTag::title => 'テストタイトル',
            NoticeEmbeddedTag::body => 'HTMLを除いた本文',
            NoticeEmbeddedTag::url => 'http://localhost/plugin/xxxx',
        ];

        $mail = new BucketsMail();
        $result = $mail->getFormattedSubject(null, $notice_embedded_tags);

        $this->assertEquals('【件名未設定】', $result, 'null値の場合はデフォルトメッセージが返される事');
    }

    /**
     * getFormattedSubject()で空文字列を渡した場合のテスト
     */
    public function testGetFormattedSubjectWithEmptyString(): void
    {
        $notice_embedded_tags = [
            NoticeEmbeddedTag::site_name => 'サンプルサイト',
            NoticeEmbeddedTag::title => 'テストタイトル',
            NoticeEmbeddedTag::body => 'HTMLを除いた本文',
            NoticeEmbeddedTag::url => 'http://localhost/plugin/xxxx',
        ];

        $mail = new BucketsMail();
        $result = $mail->getFormattedSubject('', $notice_embedded_tags);

        $this->assertEquals('【件名未設定】', $result, '空文字列の場合はデフォルトメッセージが返される事');
    }
}
