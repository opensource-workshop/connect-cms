<?php

namespace Tests\Unit\Plugins\User\Learningtasks\Services; // 実装に合わせた名前空間

use App\Models\User\Learningtasks\Learningtasks;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Plugins\User\Learningtasks\Services\LearningtaskSettingChecker;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use stdClass;
use Tests\TestCase;

/**
 * LearningtaskSettingChecker のユニットテストクラス
 *
 * @coversDefaultClass \App\Plugins\User\Learningtasks\Services\LearningtaskSettingChecker
 */
class LearningtaskSettingCheckerTest extends TestCase
{
    /**
     * テスト用の設定オブジェクト(stdClass)を生成するヘルパー
     */
    private function createMockSetting(string $use_function, string $value): object
    {
        $setting = new stdClass(); // 実際のモデルでなくても、プロパティがあればOK
        $setting->use_function = $use_function;
        $setting->value = $value;
        return $setting;
    }

    /**
     * ->where()->first() をシミュレートする Collection のモックを生成するヘルパー
     *
     * @param object|null $setting_to_return first() で返すべき設定オブジェクト、または null
     * @param string $expected_setting_name where() で期待される設定名
     * @return MockInterface Collection のモック
     */
    private function mockSettingCollection(?object $setting_to_return, string $expected_setting_name): MockInterface
    {
        $mock_collection = Mockery::mock(Collection::class);
        // where('use_function', $name) が呼ばれたら自分自身を返す (->first() をチェーンするため)
        $mock_collection->shouldReceive('where')
            ->with('use_function', $expected_setting_name)
            ->andReturnSelf();
        // first() が呼ばれたら、指定された設定オブジェクトまたは null を返す
        $mock_collection->shouldReceive('first')
            ->andReturn($setting_to_return);
        return $mock_collection;
    }

    /**
     * 投稿設定がonならtrueを返す
     * @test
     * @covers ::isEnabled
     * @group learningtasks
     */
    public function isEnabledReturnsTrueWhenPostSettingIsOn(): void
    {
        // Arrange
        $setting_name = 'test_setting_on';
        // 模擬する設定データ ('on')
        $target_setting = $this->createMockSetting($setting_name, 'on');
        // post_settings コレクションのモック (指定の設定名を where したら $target_setting を返す)
        $mock_post_settings = $this->mockSettingCollection($target_setting, $setting_name);
        // LearningtasksPosts モデルのモック
        /** @var LearningtasksPosts|MockInterface $mock_post */
        $mock_post = Mockery::mock(LearningtasksPosts::class);
        // ->post_settings が呼ばれたら $mock_post_settings を返すように設定
        $mock_post->shouldReceive('getAttribute')->with('post_settings')->andReturn($mock_post_settings);
        // ->learningtask は呼ばれないはず

        // Act
        // テスト対象クラスをモックオブジェクトで初期化
        $checker = new LearningtaskSettingChecker($mock_post);
        $result = $checker->isEnabled($setting_name);

        // Assert
        $this->assertTrue($result, '投稿設定が on なら true が返るべき');
    }

    /**
     * 投稿設定がoffならfalseを返す
     * @test
     * @covers ::isEnabled
     * @group learningtasks
     */
    public function isEnabledReturnsFalseWhenPostSettingIsOff(): void
    {
        // Arrange
        $setting_name = 'test_setting_off';
        // 模擬する設定データ ('off')
        $target_setting = $this->createMockSetting($setting_name, 'off');
        $mock_post_settings = $this->mockSettingCollection($target_setting, $setting_name);
        /** @var LearningtasksPosts|MockInterface $mock_post */
        $mock_post = Mockery::mock(LearningtasksPosts::class);
        $mock_post->shouldReceive('getAttribute')->with('post_settings')->andReturn($mock_post_settings);

        // Act
        $checker = new LearningtaskSettingChecker($mock_post);
        $result = $checker->isEnabled($setting_name);

        // Assert
        $this->assertFalse($result, '投稿設定が off なら false が返るべき');
    }

    /**
     * 投稿設定がなく課題設定がonならtrueを返す
     * @test
     * @covers ::isEnabled
     * @group learningtasks
     */
    public function isEnabledReturnsTrueWhenPostSettingAbsentAndTaskSettingIsOn(): void
    {
        // Arrange
        $setting_name = 'test_setting_fallback_on';
        $task_setting = $this->createMockSetting($setting_name, 'on');
        $mock_post_settings = $this->mockSettingCollection(null, $setting_name);
        $mock_task_settings = $this->mockSettingCollection($task_setting, $setting_name);
        /** @var Learningtasks|MockInterface $mock_learningtask */
        $mock_learningtask = Mockery::mock(Learningtasks::class);
        $mock_learningtask->shouldReceive('getAttribute')->with('learningtask_settings')->andReturn($mock_task_settings);
        /** @var LearningtasksPosts|MockInterface $mock_post */
        $mock_post = Mockery::mock(LearningtasksPosts::class);
        $mock_post->shouldReceive('getAttribute')->with('post_settings')->andReturn($mock_post_settings);
        $mock_post->shouldReceive('getAttribute')->with('learningtask')->andReturn($mock_learningtask);

        // Act
        $checker = new LearningtaskSettingChecker($mock_post);
        $result = $checker->isEnabled($setting_name);

        // Assert
        $this->assertTrue($result, '投稿設定がなく課題設定が on なら true が返るべき');
    }
    /**
     * 投稿設定がなく課題設定がoffならfalseを返す
     * @test
     * @covers ::isEnabled
     * @group learningtasks
     */
    public function isEnabledReturnsFalseWhenPostSettingAbsentAndTaskSettingIsOff(): void
    {
        // Arrange
        $setting_name = 'test_setting_fallback_off';
        $task_setting = $this->createMockSetting($setting_name, 'off');
        $mock_post_settings = $this->mockSettingCollection(null, $setting_name);
        $mock_task_settings = $this->mockSettingCollection($task_setting, $setting_name);
         /** @var Learningtasks|MockInterface $mock_learningtask */
        $mock_learningtask = Mockery::mock(Learningtasks::class);
        $mock_learningtask->shouldReceive('getAttribute')->with('learningtask_settings')->andReturn($mock_task_settings);
        // $mock_learningtask->shouldReceive('__get')->with('learningtask_settings')->andReturn($mock_task_settings);
         /** @var LearningtasksPosts|MockInterface $mock_post */
        $mock_post = Mockery::mock(LearningtasksPosts::class);
        $mock_post->shouldReceive('getAttribute')->with('post_settings')->andReturn($mock_post_settings);
        $mock_post->shouldReceive('getAttribute')->with('learningtask')->andReturn($mock_learningtask);

        // Act
        $checker = new LearningtaskSettingChecker($mock_post);
        $result = $checker->isEnabled($setting_name);

        // Assert
        $this->assertFalse($result, '投稿設定がなく課題設定が off なら false が返るべき');
    }

    /**
     * 投稿設定も課題設定もなければfalseを返す
     * @test
     * @covers ::isEnabled
     * @group learningtasks
     */
    public function isEnabledReturnsFalseWhenBothSettingsAreAbsent(): void
    {
        // Arrange
        $setting_name = 'test_setting_absent';
        $mock_post_settings = $this->mockSettingCollection(null, $setting_name);
        $mock_task_settings = $this->mockSettingCollection(null, $setting_name);
        /** @var Learningtasks|MockInterface $mock_learningtask */
        $mock_learningtask = Mockery::mock(Learningtasks::class);
        $mock_learningtask->shouldReceive('getAttribute')->with('learningtask_settings')->andReturn($mock_task_settings);
        /** @var LearningtasksPosts|MockInterface $mock_post */
        $mock_post = Mockery::mock(LearningtasksPosts::class);
        $mock_post->shouldReceive('getAttribute')->with('post_settings')->andReturn($mock_post_settings);
        $mock_post->shouldReceive('getAttribute')->with('learningtask')->andReturn($mock_learningtask);

        // Act
        $checker = new LearningtaskSettingChecker($mock_post);
        $result = $checker->isEnabled($setting_name);

        // Assert
        $this->assertFalse($result, '両方の設定がなければ false が返るべき');
    }
    /**
     * リレーションがnullなら課題設定を見ずにfalseを返す
     * @test
     * @covers ::isEnabled
     * @group learningtasks
     */
    public function isEnabledReturnsFalseWhenLearningtaskRelationIsNull(): void
    {
        // Arrange
        $setting_name = 'test_setting_null_relation';

        $mock_post_settings = $this->mockSettingCollection(null, $setting_name); // 投稿設定なし
         /** @var LearningtasksPosts|MockInterface $mock_post */
        $mock_post = Mockery::mock(LearningtasksPosts::class);
        $mock_post->shouldReceive('getAttribute')->with('post_settings')->andReturn($mock_post_settings);
        $mock_post->shouldReceive('getAttribute')->with('learningtask')->andReturnNull();

        // Act
        $checker = new LearningtaskSettingChecker($mock_post);
        $result = $checker->isEnabled($setting_name);

        // Assert
        $this->assertFalse($result, 'learningtask 関係が null なら false が返るべき'); // 課題設定を読みに行かずに false になるはず
    }
}
