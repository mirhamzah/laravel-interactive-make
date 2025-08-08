<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

class IViewMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:iview {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model_name = Str::studly($this->argument('model'));
        $modelClass = 'App\\Models\\' . $model_name;

        if (!class_exists($modelClass)) {
            $this->error("Model class {$modelClass} does not exist.");
            return;
        }

        $model = new $modelClass;

        $fields = $this->getFields($model);

        $files = ['Create.blade.jsx', 'Edit.blade.jsx'];

        foreach ($files as $file) {

            // Render the Blade template with the model and fields
            $viewContent = Blade::render($this->files->get(__DIR__ . '/Components/' . $file), [
                'model' => $model_name,
                'fields' => $fields,
            ]);
            // Create the jsx file in the specified path
            $viewPath = resource_path("js/Components/$model_name/$file");
            $this->files->ensureDirectoryExists(dirname($viewPath));
            $this->files->put($viewPath, $viewContent);    
            $this->info("View created successfully at {$viewPath}");
        }

    }

    /**
     * Get the model fields.
     *
     * @param Model $model
     * @return array
     */
    public function getFields($model)
    {
        $casts = $model->getCasts();

        $fields = [];
        foreach ($model->getFillable() as $field) {
            $fields[] = [
                'name' => $field,
                'label' => Str::headline($field),
                'type' => $casts[$field] ?? 'string',
                'type' => $this->getFieldType($field, $casts[$field] ?? null),
            ];
        }
        return $fields;
    }

    /**
     * Get field type for form based on the cast type and field name.
     *
     * @param string $field
     * @param string $type
     * @return string
     */
    protected function getFieldType($field, $type)
    {
        if ($type === 'integer' || $type === 'float') {
            return 'number';
        } elseif ($type === 'boolean') {
            return 'checkbox';
        } elseif (Str::contains($field, 'email')) {
            return 'email';
        } elseif (Str::contains($field, 'password')) {
            return 'password';
        } elseif (Str::contains($field, 'datetime')) {
            return 'datetime-local';
        } elseif (Str::contains($field, 'date')) {
            return 'date';
        } elseif (Str::contains($field, 'time')) {
            return 'time';
        } elseif (Str::contains($field, 'file')) {
            return 'file';
        } elseif (Str::contains($field, 'image')) {
            return 'file'; // For image upload, we can use file input
        }
        return 'text';
    }


    protected function getStub()
    {
        return null;
    }

}
