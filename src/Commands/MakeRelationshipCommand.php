<?php

namespace Mirhamzah\LaravelInteractiveMake\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeRelationshipCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:relationship
                            {source}
                            {type}
                            {destination}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds relationship (to existing Models)';

    /**
     * Create a new controller creator command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $source = $this->argument('source');
        $type = Str::studly($this->argument('type'));
        $destination = $this->argument('destination');

        $sourceModel = $this->loadModel($source);

        if (!$sourceModel) return;

        if (method_exists($sourceModel['model'], $this->getFunctionName($destination, $type))) {
            $this->components->error('Relationship already exists');
            return;
        }

        // check relationship class
        if (!$this->classExists($sourceModel['content'], $type)) {

            $sourceModel['content'] = $this->addClass($sourceModel['content'], $type);

        }

        $sourceModel['content'] = $this->addRelationship($sourceModel['content'], $destination, $type);
        $this->save($sourceModel);

        $this->components->info(sprintf('[%s] updated successfully.', $sourceModel['filename']));


    }

    private function loadModel($name)
    {
        $filename = $this->getFileName($name);

        if (!$this->files->exists($filename)) {
            $this->components->error("Error loading file: [$filename]");
            return false;
        }

        $data = [
            'filename' => $filename,
            'content' => trim($this->files->get($filename)),
            'model' => app("\\App\\Models\\$name")
        ];

        return $data;

    }

    private function getFilename($model)
    {
        return app_path('Models/' . $model . '.php');
    }

    private function classExists($content, $type)
    {

        return preg_match('/\n[ \t]*use Illuminate\\\\Database\\\\Eloquent\\\\Relations\\\\' . $type . '/', $content);

    }

    private function addClass($content, $type)
    {

        $replace = 'use Illuminate\\Database\\Eloquent\\Relations\\' . $type . ';' . PHP_EOL;
        $content = preg_replace('/(\nclass [\w]+ extends )/', $replace . '$1', $content);

        return $content;

    }

    private function getFunctionName($model, $type)
    {
        $fname = strtolower($model);
        if ($type == 'HasMany') $fname = Str::plural($fname);
        return $fname;
    }

    private function addRelationship($content, $model, $type)
    {

        $fname = $this->getFunctionName($model, $type);
        $rfname = Str::camel($type);

        $function = "
    public function $fname(): ?$type
    {
        return \$this->$rfname($model::class);
    }
    " . PHP_EOL;

        /** find last occurance of closing curly bracket
         * insert $function just before closing bracket
         */
        $pos = strrpos($content, '}');
        $content_before = substr($content, 0, $pos);
        $content_end = substr($content, $pos);

        return $content_before . $function . $content_end;
    }


    private function save($model)
    {
        $this->files->put($model['filename'], $model['content']);
    }

}
