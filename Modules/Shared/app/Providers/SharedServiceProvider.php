<?php

namespace Modules\Shared\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

final class SharedServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->registerCommandQueryBindings();
    }

    // MARK: - Command + Query bindings

    private function registerCommandQueryBindings(): void
    {
        //
    }
}
