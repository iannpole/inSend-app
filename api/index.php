<?php

try {
    require __DIR__ . "/../vendor/autoload.php";
    $app = require_once __DIR__ . "/../bootstrap/app.php";
    $app->useStoragePath($_ENV["APP_STORAGE"] ?? "/tmp/storage");
    $storagePath = $app->storagePath();
    
    foreach (["app", "framework/views", "framework/cache", "framework/sessions", "logs"] as $dir) {
        if (!is_dir("{$storagePath}/{$dir}")) {
            mkdir("{$storagePath}/{$dir}", 0777, true);
        }
    }

    $app->handleRequest(Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine(),
        "trace" => $e->getTraceAsString(),
    ]);
}
