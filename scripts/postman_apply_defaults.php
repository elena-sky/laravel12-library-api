<?php

/**
 * After openapi-to-postmanv2, normalize collection variables for local import.
 */
declare(strict_types=1);

$path = dirname(__DIR__).'/docs/postman/Library_API.postman_collection.json';

if (! is_file($path)) {
    fwrite(STDERR, "Missing {$path}; run composer postman:generate first.\n");
    exit(1);
}

$json = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

$vars = $json['variable'] ?? [];
$byKey = [];

foreach ($vars as $v) {
    if (isset($v['key'])) {
        $byKey[$v['key']] = $v;
    }
}

$byKey['baseUrl'] = [
    'key' => 'baseUrl',
    'value' => 'http://localhost:8000/api/v1',
];
$byKey['bearerToken'] = [
    'key' => 'bearerToken',
    'value' => $byKey['bearerToken']['value'] ?? '',
];

$json['variable'] = array_values($byKey);

file_put_contents(
    $path,
    json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n"
);

echo "Patched Postman variables: baseUrl, bearerToken in {$path}\n";
