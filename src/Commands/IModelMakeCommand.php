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
use Illuminate\Support\Facades\Blade;


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

    protected $relationships = [];

    public function handle()
    {

        $this->handleFillableFields();

        $result = $this->choice('Do you want to add relationships?', [
            'yes',
            'no'
        ], 'yes');

        if ($result == 'yes') {
            $this->handleRelationshipFields();
        }

        parent::handle();

    }

    private function handleFillableFields()
    {

        while (true) {
            $name = $this->ask('Enter the field name (or press ENTER to finish):');
            if ($name == '') {
                break;
            }

            $type = $this->choice('Select the field type:', [
                'string', 
                'text', 
                'integer', 
                'float',
                'boolean', 
                'date', 
                'dateTime',
                'timestamp'
            ], $this->suggestFieldType($name));

            $this->fillable[] = compact('name', 'type');
        }

    }

    private function handleRelationshipFields()
    {

        while (true) {
            $name = $this->ask('Enter Model name (or press ENTER to finish):');
            if ($name == '') {
                break;
            }

            $type = $this->choice('Select relationship type:', [
                'HasOne', 
                'HasMany', 
                'BelongsTo'
            ], NULL);

            $this->relationships[$name] = $type;

        }

    }

    /**
     * Suggest field type for given field name
     *
     * @return string
     */
    protected function suggestFieldType($field_name)
    {

        $field_name = strtolower($field_name);

        $fields = [
            'string' => [
                'title',
                'name',
                'username',
                'image',
                'url',
                'phone',
                'city',
                'state',
                'region',
                'county',
                'token',
                'postcode'
            ],
            'text' => [
                'body',
                'content',
                'text',
                'description'
            ],
            'boolean' => [
                'status',
                'active',
                'deleted',
                'is_'
            ],
            'date' => [
                'birthdate',
                'dob',
                'date'
            ],
            'timestamp' => [
                'created',
                'updated',
                '_at'
            ],
            'integer' => [
                'quantity',
                'qty',
                'count',
                'rating',
                '_id'
            ],
            'float' => [
                'price',
                'weight',
                'length',
                'width',
                'height'
            ]
        ];

        foreach ($fields as $type => $names) {

            if (in_array($field_name, $names)) {
                return $type;
            }

        }

        // if it couldn't find exact match, try finding a partial match
        foreach ($fields as $type => $names) {

            foreach ($names as $name) {
                if (strstr($field_name, $name)) {
                    return $type;
                }
            }

        }

        // returning string, as it is most commonly used.
        return 'string';

    }

    /**
     * Get the view file for the generator.
     *
     * @return string
     */
    protected function getView()
    {
        return $this->resolveViewPath('/views/model.blade.php');
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

        $view = $this->files->get($this->getView());

        $output = Blade::render($view, [
            'class' => $name,
            'fillables' => $this->fillable,
            'relationships' => $this->relationships
        ], deleteCachedView: true);

        return str_replace('@php', '<?php', $output);

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
            '--relationships' => $this->relationships,
            '--fullpath' => false,
        ]);
    }

}
