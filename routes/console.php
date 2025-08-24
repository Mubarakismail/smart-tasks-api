<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Convenience command to generate OpenAPI/Swagger documentation
Artisan::command('docs:generate', function () {
    $this->info('Generating OpenAPI (Swagger) documentation...');

    // Ensure storage path exists
    if (!is_dir(storage_path('api-docs'))) {
        mkdir(storage_path('api-docs'), 0777, true);
    }

    // Regenerate docs using L5 Swagger
    Artisan::call('l5-swagger:generate', [
        '--force' => true,
    ]);

    $output = Artisan::output();
    if (!empty($output)) {
        $this->line($output);
    }

    $uiPath = config('l5-swagger.documentations.default.routes.api', 'api/documentation');
    $this->info("Done. You can view the docs at /{$uiPath}");
})->purpose('Generate Swagger/OpenAPI documentation from annotations');
