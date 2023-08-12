<?php

namespace Mirhamzah\LaravelInteractiveMake;

use Illuminate\Support\ServiceProvider;
use Mirhamzah\LaravelInteractiveMake\Commands\IModelMakeCommand;
use Mirhamzah\LaravelInteractiveMake\Commands\IMigrateMakeCommand;
use Mirhamzah\LaravelInteractiveMake\Commands\MakeRelationshipCommand;


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
            IMigrateMakeCommand::class,
            MakeRelationshipCommand::class
        ]);
    }
}