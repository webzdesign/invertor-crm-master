<?php

namespace App\Console\Commands;

use App\Http\Controllers\AutoTranslateAttributesController;
use Illuminate\Console\Command;

class AutoTranslateAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-translate-attributes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically translate fields value in multiple language';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model = 'Product';
        $fields = ['name','slider_content'];

        $fullModelClass = 'App\\Models\\' . $model;

        if (!class_exists($fullModelClass)) {
            $this->error("Model {$fullModelClass} not found.");
            return;
        }

        AutoTranslateAttributesController::AutoTranslateAttributes($model, $fields, $this);

    }
}
