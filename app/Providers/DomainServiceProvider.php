<?php

namespace App\Providers;

use App\Repositories\DocumentRepositoryInterface;
use App\Repositories\EloquentDocumentRepository;
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
        $this->app->bind(DocumentRepositoryInterface::class, EloquentDocumentRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
