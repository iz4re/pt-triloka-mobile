<?php

return [
    'default' => env('FIREBASE_PROJECT', 'app'),
    'projects' => [
        'app' => [
            'credentials' => storage_path('app/firebase_credentials.json'),
        ],
    ],
];
