<?php

namespace App\Providers;

use App\Services\Upload\FileUploaderInterface;
use App\Services\Upload\LocalFileUploader;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // DIP: Od teraz, gdy zażądamy FileUploaderInterface, Laravel wstrzyknie LocalFileUploader.
        $this->app->bind(FileUploaderInterface::class, LocalFileUploader::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
