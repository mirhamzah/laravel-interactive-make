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

            $type = $this->choice('Select the field type:', [
                'string', 
                'integer', 
                'boolean', 
                'text', 
                'date', 
                'float'
            ], $this->suggestFieldType($name));

            $this->fillable[] = compact('name', 'type');
        }

        parent::handle();

    }

    /**
     * Suggest field type for given field name
     *
     * @return string
     */
    protected function suggestFieldType($name)
    {

        $fields = [
            'string' => [
                'title',
                'name',
                'username',
                'image',
                'url',
                'phone'
            ],
            'text' => [
                'body',
                'content',
                'text'
            ],
            'boolean' => [
                'status',
                'active',
                'deleted'
            ],
            'date' => [
                'birthdate',
                'dob'
            ],
            'integer' => [
                'quantity'
            ],
            'float' => [
                'price'
            ]
        ];

        foreach ($fields as $type => $names) {

            if (in_array($name, $names)) {
                return $type;
            }

        }

        return NULL;

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

}
