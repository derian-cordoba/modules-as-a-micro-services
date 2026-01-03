<?php

namespace Modules\Shared\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Shared\Bus\Command\CommandBus;
use Modules\Shared\Bus\Query\QueryBus;
use Modules\Shared\Contracts\Command\CommandBusInterface;
use Modules\Shared\Contracts\Query\QueryBusInterface;

final class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerCommandQueryBindings();
    }

    // MARK: - Command + Query bindings

    private function registerCommandQueryBindings(): void
    {
        $this->app->singleton(
            abstract: CommandBusInterface::class,
            concrete: CommandBus::class,
        );

        $this->app->singleton(
            abstract: QueryBusInterface::class,
            concrete: QueryBus::class,
        );
    }
}
