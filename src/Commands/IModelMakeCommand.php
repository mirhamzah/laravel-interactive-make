<?php

namespace Mirhamzah\LaravelInteractiveMake\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand;
#use Illuminate\Support\Facades\Artisan;
#use Illuminate\Support\Facades\File;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(name: 'make:imodel')]
class IModelMakeCommand extends ModelMakeCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:imodel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a model interactively by asking for fields.';

    protected $fillable = [];

    public function handle()
    {

        while (true) {
            $name = $this->ask('Enter the field name (or press ENTER to finish):');
            if ($name == '') {
                break;
            }

            $type = $this->choice('Select the field type:', ['string', 'integer', 'boolean', 'text', 'date', 'float']);

            $this->fillable[] = compact('name', 'type');
        }

        parent::handle();

    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/model.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return __DIR__.$stub;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        $stub = $this->replaceFillable($stub);

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Replace the fillables for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceFillable($stub)
    {

        if (count($this->fillable)) {

            $fields = "'" . implode("', '", array_column($this->fillable, 'name')) . "'";
            return str_replace(['{{ fillable }}', '{{fillable}}'], $fields, $stub);
        }

    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));

        if ($this->option('pivot')) {
            $table = Str::singular($table);
        }

        $this->call('make:imigration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
            '--fields' => $this->fillable,
            '--fullpath' => false,
        ]);
    }

    public function handleOld()
    {
        $name = $this->ask('Enter the name of the model (e.g., Post):');
        $tableName = $this->ask('Enter the table name (optional, default will be used if empty):');

        $fields = [];
        while (true) {
            $fieldName = $this->ask('Enter the field name (or type "exit" to finish):');
            if ($fieldName === 'exit') {
                break;
            }

            $fieldType = $this->choice('Select the field type:', ['string', 'integer', 'boolean', 'text', 'date', 'float']);

            $fields[] = compact('fieldName', 'fieldType');
        }

        // Create migration
        $migrationName = 'create_' . strtolower($name) . 's_table';
        $this->call('make:migration', [
            'name' => $migrationName,
            '--create' => $tableName ?? strtolower($name) . 's',
        ]);

        // Generate model
        $this->call('make:model', [
            'name' => $name,
        ]);

        // Fill model with fillable fields
        $fillableFields = implode(',', array_column($fields, 'fieldName'));
        $modelFilePath = app_path("Models/{$name}.php");
        $modelFileContents = $this->files->get($modelFilePath);
        $fillableReplacement = "protected \$fillable = [{$fillableFields}];";
        $modelFileContents = str_replace('protected $fillable = [];', $fillableReplacement, $modelFileContents);
        $this->files->put($modelFilePath, $modelFileContents);

        $this->info("Model {$name} created successfully with migration and fillable fields.");
    }
}
