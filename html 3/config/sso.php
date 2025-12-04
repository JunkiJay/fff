<?php

return [
    'secret' => env('SSO_SHARED_SECRET', 'asw@dasS34sadasd13sDazSJNJhd82jdMsl'),
    'issuer' => env('SSO_ISSUER', 'external-service'),
    'audience' => env('SSO_AUDIENCE', 'laravel-app'),
    'ttl' => env('SSO_TTL', 60), // в секундах
];