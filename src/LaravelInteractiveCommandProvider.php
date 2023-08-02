<?php

namespace Mirhamzah\LaravelInteractiveMake;

use Illuminate\Support\ServiceProvider;
use Mirhamzah\LaravelInteractiveMake\IMigrateMakeCommand;
use Mirhamzah\LaravelInteractiveMake\IMigrateMakeCommand;


class LaravelInteractiveCommandProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            IMigrateMakeCommand::class,
            IMigrateMakeCommand::class
        ]);
    }
}