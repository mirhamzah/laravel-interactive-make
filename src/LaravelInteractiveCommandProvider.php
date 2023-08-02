<?php

namespace Mirhamzah\LaravelInteractiveMake;

use Illuminate\Support\ServiceProvider;
use Mirhamzah\LaravelInteractiveMake\Commands\IModelMakeCommand;
use Mirhamzah\LaravelInteractiveMake\Commands\IMigrateMakeCommand;


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
            IModelMakeCommand::class,
            IMigrateMakeCommand::class
        ]);
    }
}