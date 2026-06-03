<?php

$runtimePaths = [
    '/tmp/views',
    '/tmp/cache',
    '/tmp/sessions',
];

foreach ($runtimePaths as $path) {
    if (! is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

$runtimeEnv = [
    'APP_ENV' => getenv('APP_ENV') ?: 'production',
    'APP_DEBUG' => getenv('APP_DEBUG') ?: 'false',
    'LOG_CHANNEL' => getenv('LOG_CHANNEL') ?: 'stderr',
    'VIEW_COMPILED_PATH' => getenv('VIEW_COMPILED_PATH') ?: '/tmp/views',
    'APP_SERVICES_CACHE' => getenv('APP_SERVICES_CACHE') ?: '/tmp/cache/services.php',
    'APP_PACKAGES_CACHE' => getenv('APP_PACKAGES_CACHE') ?: '/tmp/cache/packages.php',
    'APP_CONFIG_CACHE' => getenv('APP_CONFIG_CACHE') ?: '/tmp/cache/config.php',
    'APP_ROUTES_CACHE' => getenv('APP_ROUTES_CACHE') ?: '/tmp/cache/routes.php',
    'APP_EVENTS_CACHE' => getenv('APP_EVENTS_CACHE') ?: '/tmp/cache/events.php',
];

foreach ($runtimeEnv as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

require __DIR__.'/../public/index.php';
