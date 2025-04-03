<?php

namespace App\Providers;

use App\Repositories\Statement\StatementRepository;
use App\Repositories\Statement\StatementRepositoryInterface;
use App\Repositories\Transfer\TransferRepository;
use App\Repositories\Transfer\TransferRepositoryInterface;
use App\Services\Authorization\AuthorizationService;
use App\Services\Notification\NotificationService;
use App\Services\Transfer\TransferService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(StatementRepositoryInterface::class, StatementRepository::class);
        $this->app->bind(TransferRepositoryInterface::class, TransferRepository::class);
        $this->app->bind(TransferService::class, function ($app) {
            return new TransferService($app['db'], new NotificationService(), new TransferRepository(), new AuthorizationService());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
