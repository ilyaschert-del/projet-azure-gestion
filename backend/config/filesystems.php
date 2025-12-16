<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        /*
        |----------------------------------------------------------
        | Azure Blob Storage - Documents container
        |----------------------------------------------------------
        */
        'azure' => [
            'driver' => 'azure-blob',
            'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),
            'container' => env('AZURE_STORAGE_CONTAINER', 'documents'),
        ],

        /*
        |----------------------------------------------------------
        | Azure Blob Storage - Data container (JSON files)
        |----------------------------------------------------------
        */
        'azure_data' => [
            'driver' => 'azure-blob',
            'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING'),
            'container' => 'data',
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];