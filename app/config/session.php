<?php

return [
    'driver' => 'native', // native, file. coming soon: database, redis
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => storage_path('frame/sessions'),
    'connection' => null,
    'table' => 'sessions', // in case of database driver
    'lottery' => [2, 100],
    'cookie' => 'frame_session',
    'path' => '/',
    'domain' => 'localhost',
    'secure' => true,
    'http_only' => true,
    'same_site' => 'lax', // none, lax, strict, or ''
];