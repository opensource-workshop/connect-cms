<?php

namespace App\Plugins\User\Learningtasks\Providers;

use Illuminate\Support\ServiceProvider;
use App\Plugins\User\Learningtasks\Contracts\RowProcessorInterface;
use App\Plugins\User\Learningtasks\DataProviders\ReportCsvDataProvider;
use App\Plugins\User\Learningtasks\Handlers\ReportExceptionHandler;
use App\Plugins\User\Learningtasks\Services\LearningtaskEvaluationRowProcessor;
use App\Plugins\User\Learningtasks\Repositories\LearningtaskUserRepository;

class LearningtasksServiceProvider extends ServiceProvider
{
    /**
     * サービスコンテナへの登録を定義する
     *
     * @return void
     */
    public function register()
    {
        // Learningtasks プラグイン固有のサービス登録を行う

        // インターフェースと実装の紐付け
        $this->app->bind(
            RowProcessorInterface::class,
            LearningtaskEvaluationRowProcessor::class,
        );
        $this->app->bind(ReportExceptionHandler::class);
        $this->app->bind(ReportCsvDataProvider::class);

        // ステートレスなリポジトリをシングルトンとして登録
        $this->app->singleton(LearningtaskUserRepository::class);
    }

    /**
     * サービスの起動処理を定義する
     *
     * @return void
     */
    public function boot()
    {
        // ルート定義、ビューコンポーザ、設定ファイルの公開などが必要な場合
    }
}
