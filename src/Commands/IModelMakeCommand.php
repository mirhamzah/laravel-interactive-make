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

    protected $modelName;

    protected $models = [];

    public function handle()
    {

        $this->modelName = Str::studly($this->argument('name'));

        $this->loadModels();

        $this->handleFillableFields();

        $result = $this->choice('Do you want to add (more) relationships?', [
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

            if (str_ends_with($name, '_id')) {
                if ($this->handleRelationshipField($name)) {
                    continue;
                }
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
                'HasMany',
                'BelongsTo'
            ], 'HasMany');

            $this->relationships[$name] = [
                'field_name' => strtolower($name),
                'field_name_plural' => Str::plural($name),
                'type' => $type,
                'table' => $this->models[strtolower($name)] ?? $name
            ];

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
            'integer' => [
                'quantity',
                'qty',
                'count',
                'rating',
                '_id'
            ],
            'boolean' => [
                'status',
                'active',
                'deleted',
                'is_'
            ],
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
     * Handles relationship field.
     * Could be either HasOne or BelongsTo.
     * Can't be OneToMany, _id is always saved in Many table, not One.
     * If model is already referenced, it is BelongsTo.
     * Else, it is HasOne.
     * E.g: field user_id
     * If User aleady has OneToMany relationship with current model,
     * You'd just need inverse relationship here.
     * If User doesn't have OneToMany, then it'd be HasOne.
     * 
     * @param  string  $field_name
     * @return boolean
     */
    protected function handleRelationshipField($field_name)
    {

        $field_name = strtolower(str_replace('_id', '', $field_name));

        if (isset($this->models[$field_name])) {
            $model = $this->models[$field_name]['name'];
            $default = $this->relationshipExists($field_name, 'HasMany') ? 'BelongsTo' : 'HasOne';
            $type = $this->choice("Create a relationship with $model?", [
                'HasOne', 
                'BelongsTo', 
                'None'
            ], $default);

            if ($type != 'None') {
                $this->relationships[$model] = [
                    'field_name' => $field_name,
                    'field_name_plural' => Str::plural($field_name),
                    'type' => $type,
                    'table' => $this->models[$field_name]['table']
                ];
                return true;
            }

        }

        return false;

    }

    protected function relationshipExists($model, $type)
    {
        return $this->models[$model][$type];
    }

    protected function loadModels()
    {

        $models_path = app_path('Models');
        $model_files = $this->files->allFiles($models_path);
        foreach ($model_files as $model_file) {
            $model_name = $model_file->getFilenameWithoutExtension();
            $this->models[strtolower($model_name)] = [
                'name' => $model_name,
                'table' => app("\\App\\Models\\$model_name")->getTable(),
                'HasMany' => method_exists("\\App\\Models\\$model_name", Str::plural(strtolower($this->modelName))),
                'HasOne' => method_exists("\\App\\Models\\$model_name", strtolower($this->modelName))
            ];
        }

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

        $class = preg_replace('/.*\\\\([\w]+)/', '$1', $name);

        $output = Blade::render($view, [
            'class' => $class,
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
