<?php

namespace App\Plugins\User\Learningtasks\Services;

use App\Models\User\Learningtasks\LearningtasksPosts;

/**
 * 課題管理の設定が有効かどうかを判定するサービスクラス
 *
 * 課題投稿固有の設定を優先し、なければ課題管理全体の設定を参照する。
 */
class LearningtaskSettingChecker
{
    /**
     * 設定判定の対象となる課題投稿
     * @var \App\Models\User\Learningtasks\LearningtasksPosts
     */
    private LearningtasksPosts $learningtask_post; // プロパティ名は snake_case

    /**
     * コンストラクタ
     *
     * @param LearningtasksPosts $learningtask_post 設定を判定する対象の課題投稿
     */
    public function __construct(LearningtasksPosts $learningtask_post)
    {
        $this->learningtask_post = $learningtask_post;
    }

    /**
     * 指定された機能設定が有効かどうかを判定する
     *
     * @param string $setting_name 判定したい設定名 (LearningtaskUseFunction enum の値)
     * @return bool 有効であれば true、無効であれば false
     */
    public function isEnabled(string $setting_name): bool
    {
        // 1. 課題投稿固有の設定 (post_settings) を確認
        $post_setting = $this->learningtask_post->post_settings
                            ->where('use_function', $setting_name)
                            ->first();

        if ($post_setting) {
            // 設定が存在し、値が 'on' なら有効
            return $post_setting->value === 'on';
        }

        // 2. 課題投稿固有の設定がなければ、課題管理全体の設定 (learningtask_settings) を確認
        //    learningtask リレーションが存在しない、または null の可能性を考慮し、
        //    Null合体演算子 (?->) を使用する
        $task_setting = $this->learningtask_post->learningtask?->learningtask_settings
                            ->where('use_function', $setting_name)
                            ->first();

        if ($task_setting) {
            // 設定が存在し、値が 'on' なら有効
            return $task_setting->value === 'on';
        }

        // どちらにも設定が見つからなければ無効と判断
        return false;
    }
}
