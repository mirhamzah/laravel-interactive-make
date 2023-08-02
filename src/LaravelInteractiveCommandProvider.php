<?php

namespace Mirhamzah\LaravelInteractiveMake;

use Illuminate\Support\ServiceProvider;
use Mirhamzah\LaravelInteractiveMake\IModelMakeCommand;
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
            IModelMakeCommand::class,
            IMigrateMakeCommand::class
        ]);
    }
}