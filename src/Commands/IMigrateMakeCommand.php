<?php

namespace Mirhamzah\LaravelInteractiveMake\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Console\Migrations\TableGuesser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Blade;

class IMigrateMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:imigration {name : The name of the migration}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--fields= : The fields for table}
        {--relationships= : The relationships for this table}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration (Deprecated)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // It's possible for the developer to specify the tables to modify in this
        // schema operation. The developer may also specify if this table needs
        // to be freshly created so we can create the appropriate migrations.
        $name = Str::snake(trim($this->argument('name')));

        $table = $this->input->getOption('table');

        $fields = $this->input->getOption('fields');

        $relationships = $this->input->getOption('relationships') ?: [];

        $create = $this->input->getOption('create') ?: false;

        // If no table was given as an option but a create option is given then we
        // will use the "create" option as the table name. This allows the devs
        // to pass a table name into this option as a short-cut for creating.
        if (! $table && is_string($create)) {
            $table = $create;

            $create = true;
        }

        // Next, we will attempt to guess the table name if this the migration has
        // "create" in the name. This will allow us to provide a convenient way
        // of creating migrations that create new tables for the application.
        if (! $table) {
            [$table, $create] = TableGuesser::guess($name);
        }

        // Now we are ready to write the migration out to disk. Once we've written
        // the migration out, we will dump-autoload for the entire framework to
        // make sure that the migrations are registered by the class loaders.
        $view = $this->files->get($this->getStub());

        $filename = $this->getPath($name);

        $output = Blade::render($view, [
            'table' => $table,
            'fields' => $fields,
            'relationships' => $relationships
        ], deleteCachedView: true);

        $this->files->put(
            $filename, str_replace('@php', '<?php', $output)
        );

/*
        $this->files->put(
            $filename, $this->populateStub($stub, $table, $fields)
        );
*/
        $this->components->info(sprintf('Migration [%s] created successfully.', $filename));

    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string  $stub
     * @param  string|null  $table
     * @return string
     */
    protected function populateStub($stub, $table, $fields)
    {
        // Here we will replace the table place-holders with the table specified by
        // the developer, which is useful for quickly creating a tables creation
        // or update migration from the console instead of typing it manually.
        if (! is_null($table)) {
            $stub = str_replace(
                ['DummyTable', '{{ table }}', '{{table}}'],
                $table, $stub
            );
        }

        $content = [];
        if ($fields) {
            foreach ($fields as $field) {

                [$name, $type] = array_values($field);

                switch ($type) {

                    case 'string':
                        $content[] = "            \$table->string('{$name}', 50)->nullable();";
                        break;

                    default:
                        $content[] = "            \$table->{$type}('{$name}')->nullable();";
                        break;

                }

            }
            $stub = str_replace(['{{fields}}', '{{ fields }}'], implode(PHP_EOL, $content), $stub);
        }

        return $stub;
    }

    /**
     * Get the full path to the migration.
     *
     * @param  string  $name
     * @param  string  $path
     * @return string
     */
    protected function getPath($name)
    {
        return base_path('database/migrations/' . $this->getDatePrefix().'_'.$name.'.php');
    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    /**
     * Get the view file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveViewPath('/views/migration.create.blade.php');
    }

    /**
     * Resolve the fully-qualified path to the view.
     *
     * @param  string  $view
     * @return string
     */
    protected function resolveViewPath($view)
    {
        return __DIR__.$view;
    }

}
