<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use AzureOss\FlysystemAzureBlobStorage\AzureBlobStorageAdapter;
use AzureOss\Storage\Blob\BlobServiceClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Storage::extend('azure-blob', function ($app, $config) {
            $client = BlobServiceClient::fromConnectionString($config['connection_string']);
            $containerClient = $client->getContainerClient($config['container']);
            
            $adapter = new AzureBlobStorageAdapter($containerClient);

            return new Filesystem($adapter);
        });
    }
}