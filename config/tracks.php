<?php
return [
    'use_cache' => env('USE_CACHE', true),
    'use_debugbar' => env('USE_DEBUGBAR', true),
    'track' => [
        'import' => [
            'modulo'    => env('TRACK_IMPORT_MODULO', 60),
        ]
    ]
];
