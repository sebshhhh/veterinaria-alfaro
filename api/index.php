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

if (getenv('VERCEL')) {
    $sourceDatabase = __DIR__.'/../database/vercel.sqlite';
    $runtimeDatabase = '/tmp/vercel.sqlite';

    if (is_file($sourceDatabase) && (! is_file($runtimeDatabase) || filesize($runtimeDatabase) !== filesize($sourceDatabase))) {
        copy($sourceDatabase, $runtimeDatabase);
    }

    if (is_file($runtimeDatabase)) {
        $runtimeEnv['DB_CONNECTION'] = 'sqlite';
        $runtimeEnv['DB_DATABASE'] = $runtimeDatabase;
        $runtimeEnv['DB_FOREIGN_KEYS'] = 'false';
        $runtimeEnv['SESSION_DRIVER'] = 'cookie';
        $runtimeEnv['CACHE_STORE'] = 'array';
        $runtimeEnv['QUEUE_CONNECTION'] = 'sync';
    }
}

foreach ($runtimeEnv as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

require __DIR__.'/../public/index.php';
