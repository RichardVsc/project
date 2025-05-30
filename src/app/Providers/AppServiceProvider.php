<?php

namespace App\Providers;

use App\Repositories\Statement\StatementRepository;
use App\Repositories\Statement\StatementRepositoryInterface;
use App\Repositories\Transfer\TransferRepository;
use App\Repositories\Transfer\TransferRepositoryInterface;
use App\Services\Authorization\AuthorizationService;
use App\Services\Transfer\RecipientResolver;
use App\Services\Transfer\TransferOrchestrator;
use App\Services\Transfer\TransferProcessor;
use App\Services\Transfer\TransferService;
use App\Services\Transfer\TransferValidator;
use App\Validators\Transfer\BalanceValidator;
use App\Validators\Transfer\PayerTypeValidator;
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
            return new TransferService(
                new TransferOrchestrator(
                    new TransferValidator(new AuthorizationService(), new PayerTypeValidator(), new BalanceValidator()),
                    new TransferProcessor($app['db'], $app->make(TransferRepositoryInterface::class)),
                    new RecipientResolver($app->make(TransferRepositoryInterface::class)),
                    $app->make(TransferRepositoryInterface::class)
                )
            );
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
