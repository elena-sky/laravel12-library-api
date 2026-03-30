#!/usr/bin/env php
<?php

/**
 * Smoke-test all requests from docs/postman/Library_API.postman_collection.prefilled.json
 * against a running API (same HTTP semantics as curl).
 *
 * Usage: php scripts/verify_postman_prefilled.php [baseUrl]
 *   POSTMAN_BASE_URL=http://127.0.0.1:8000/api/v1 php scripts/verify_postman_prefilled.php
 *   composer run verify:postman
 *
 * Differences vs raw Postman import: login-or-register bootstrap; execution order fixed so DELETE book
 * runs after rentals; dynamic ids for book / rental / managed user; unique managed email; PATCH /user
 * gets email from GET /user (Form Request requires name+email; prefilled body is name-only).
 *
 * Exit 0 if no 5xx; prints a table of name, method, path, HTTP code.
 */

declare(strict_types=1);

$collectionPath = dirname(__DIR__).'/docs/postman/Library_API.postman_collection.prefilled.json';
$baseUrl = rtrim($argv[1] ?? getenv('POSTMAN_BASE_URL') ?: 'http://localhost:8000/api/v1', '/');

$json = json_decode((string) file_get_contents($collectionPath), true, 512, JSON_THROW_ON_ERROR);
$vars = [];
foreach ($json['variable'] ?? [] as $v) {
    $vars[$v['key']] = (string) ($v['value'] ?? '');
}

function subst(string $s, array $vars): string
{
    return preg_replace_callback('/\{\{(\w+)\}\}/', static fn (array $m): string => $vars[$m[1]] ?? $m[0], $s) ?? $s;
}

/**
 * @return list<array{name:string,method:string,path:string,query:array<string,string>,body:?string,auth:string}>
 */
function collectRequests(array $items): array
{
    $out = [];
    foreach ($items as $it) {
        if (isset($it['item'])) {
            $out = array_merge($out, collectRequests($it['item']));

            continue;
        }
        if (! isset($it['request']['method'], $it['request']['url'])) {
            continue;
        }
        $req = $it['request'];
        $url = $req['url'];
        $path = implode('/', $url['path'] ?? []);
        $query = [];
        foreach ($url['query'] ?? [] as $q) {
            if (! empty($q['disabled'])) {
                continue;
            }
            $query[$q['key']] = (string) ($q['value'] ?? '');
        }
        $pathVars = [];
        foreach ($url['variable'] ?? [] as $pv) {
            if (! empty($pv['disabled'])) {
                continue;
            }
            $pathVars[$pv['key']] = (string) ($pv['value'] ?? '');
        }
        foreach ($pathVars as $k => $v) {
            $path = str_replace(':'.$k, $v, $path);
        }
        $rawBody = null;
        if (($req['body']['mode'] ?? '') === 'raw' && isset($req['body']['raw'])) {
            $rawBody = (string) $req['body']['raw'];
        }
        $auth = $req['auth']['type'] ?? 'none';
        $out[] = [
            'name' => (string) ($it['name'] ?? ''),
            'method' => strtoupper((string) $req['method']),
            'path' => $path,
            'query' => $query,
            'body' => $rawBody,
            'auth' => $auth,
        ];
    }

    return $out;
}

$requests = collectRequests($json['item']);

$pick = static function (array $requests, callable $pred): array {
    return array_values(array_filter($requests, $pred));
};
$health = $pick($requests, static fn (array $r): bool => str_starts_with($r['path'], 'status/'));
usort($health, static fn (array $a, array $b): int => strcmp($a['path'], $b['path']));
$register = $pick($requests, static fn (array $r): bool => $r['path'] === 'register' && $r['method'] === 'POST');
$login = $pick($requests, static fn (array $r): bool => $r['path'] === 'login' && $r['method'] === 'POST');
$logout = $pick($requests, static fn (array $r): bool => $r['path'] === 'logout' && $r['method'] === 'POST');
$rest = array_values(array_filter(
    $requests,
    static fn (array $r): bool => ! str_starts_with($r['path'], 'status/')
        && ! ($r['path'] === 'register' && $r['method'] === 'POST')
        && ! ($r['path'] === 'login' && $r['method'] === 'POST')
        && ! ($r['path'] === 'logout' && $r['method'] === 'POST')
));

/** Postman folder order runs Delete book before Update and before rentals; reorder so the book exists for PATCH and POST /rentals. */
$flowRank = [
    'List and search books' => [0, 0],
    'Create book' => [0, 1],
    'Get book by id' => [0, 2],
    'Update book' => [0, 3],
    'List my rentals' => [1, 0],
    'Rent a book' => [1, 1],
    'Get my rental' => [1, 2],
    'Extend active rental' => [1, 3],
    'Get reading progress' => [1, 4],
    'Update reading progress (active only)' => [1, 5],
    'Finish rental and return copy' => [1, 6],
    'Delete book (forbidden if active rentals exist)' => [2, 0],
    'Current user profile' => [3, 0],
    'Update current user profile' => [3, 1],
    'Update current user password' => [3, 2],
    'List users (paginated)' => [4, 0],
    'Create user' => [4, 1],
    'Get user by id' => [4, 2],
    'Update user' => [4, 3],
    'Delete user (not self; not if rentals exist)' => [4, 4],
];
usort($rest, static function (array $a, array $b) use ($flowRank): int {
    $ra = $flowRank[$a['name']] ?? [9, 0];
    $rb = $flowRank[$b['name']] ?? [9, 0];

    return $ra <=> $rb;
});

$requests = array_merge($health, $register, $login, $rest, $logout);

/** @param array<string, string> $query */
function httpJson(
    string $method,
    string $baseUrl,
    string $path,
    array $query,
    ?string $body,
    ?string $bearerToken,
    array $vars,
): array {
    $url = $baseUrl.'/'.ltrim($path, '/');
    if ($query !== []) {
        $url .= '?'.http_build_query($query);
    }
    $headers = [
        'Accept: application/json',
    ];
    if ($body !== null && $body !== '') {
        $headers[] = 'Content-Type: application/json';
    }
    if ($bearerToken !== null && $bearerToken !== '') {
        $headers[] = 'Authorization: Bearer '.$bearerToken;
    }
    $ctx = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $body !== null ? subst($body, $vars) : null,
            'ignore_errors' => true,
            'timeout' => 30,
        ],
    ]);
    $bodyOut = @file_get_contents($url, false, $ctx);
    $code = 0;
    if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) {
        $code = (int) $m[1];
    }
    if ($bodyOut === false) {
        return ['code' => 0, 'json' => null, 'raw' => ''];
    }
    $j = json_decode($bodyOut, true);

    return ['code' => $code, 'json' => is_array($j) ? $j : null, 'raw' => $bodyOut];
}

$rows = [];
$had5xx = false;

$loginBody = json_encode([
    'email' => $vars['demoEmail'],
    'password' => $vars['demoPassword'],
], JSON_THROW_ON_ERROR);
$loginRes = httpJson('POST', $baseUrl, 'login', [], $loginBody, null, $vars);
$bearerToken = '';
if ($loginRes['code'] === 200 && isset($loginRes['json']['data']['token'])) {
    $bearerToken = (string) $loginRes['json']['data']['token'];
    $rows[] = ['name' => 'Bootstrap: login (collection credentials)', 'method' => 'POST', 'path' => 'login', 'code' => $loginRes['code']];
} else {
    $vars['demoEmail'] = 'postman_verify_'.bin2hex(random_bytes(4)).'@example.com';
    $regBody = subst(
        '{"name":"{{demoName}}","email":"{{demoEmail}}","password":"{{demoPassword}}","password_confirmation":"{{demoPassword}}"}',
        $vars
    );
    $regRes = httpJson('POST', $baseUrl, 'register', [], $regBody, null, $vars);
    $rows[] = ['name' => 'Bootstrap: register (unique email)', 'method' => 'POST', 'path' => 'register', 'code' => $regRes['code']];
    if ($regRes['code'] === 201 && isset($regRes['json']['data']['token'])) {
        $bearerToken = (string) $regRes['json']['data']['token'];
    }
}

$vars['bearerToken'] = $bearerToken;

if ($bearerToken === '') {
    fwrite(STDERR, "Could not obtain bearer token (login and register both failed).\n");

    exit(2);
}

$requests = array_values(array_filter(
    $requests,
    static fn (array $r): bool => ! (($r['path'] === 'register' && $r['method'] === 'POST')
        || ($r['path'] === 'login' && $r['method'] === 'POST'))
));

$dynamicBookId = null;
$dynamicRentalId = null;
$dynamicManagedUserId = null;

foreach ($requests as $r) {
    $path = $r['path'];
    if ($dynamicBookId !== null) {
        $path = preg_replace('#^books/1($|/)#', 'books/'.$dynamicBookId.'$1', $path) ?? $path;
    }
    if ($dynamicRentalId !== null) {
        $path = preg_replace('#^rentals/1($|/)#', 'rentals/'.$dynamicRentalId.'$1', $path) ?? $path;
    }
    if ($dynamicManagedUserId !== null) {
        $path = preg_replace('#^users/1($|/)#', 'users/'.$dynamicManagedUserId.'$1', $path) ?? $path;
    }

    $body = $r['body'];
    if ($body !== null) {
        $body = subst($body, $vars);
        if ($r['name'] === 'Create user') {
            $body = str_replace(
                'managed.user@example.com',
                'managed_'.bin2hex(random_bytes(4)).'@example.com',
                $body
            );
        }
    }

    if ($r['name'] === 'Update current user profile' && $body !== null) {
        $me = httpJson('GET', $baseUrl, 'user', [], null, $bearerToken, $vars);
        if ($me['code'] === 200 && isset($me['json']['data']['email'])) {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            $decoded['email'] = $me['json']['data']['email'];
            $body = json_encode($decoded, JSON_THROW_ON_ERROR);
        }
    }

    $res = httpJson(
        $r['method'],
        $baseUrl,
        $path,
        $r['query'],
        $body,
        $r['auth'] === 'bearer' ? $bearerToken : null,
        $vars,
    );
    $code = $res['code'];
    if ($code >= 500) {
        $had5xx = true;
    }

    if ($r['method'] === 'POST' && $r['path'] === 'books' && $code === 201 && isset($res['json']['data']['id'])) {
        $dynamicBookId = (int) $res['json']['data']['id'];
        $vars['rentalBookId'] = (string) $dynamicBookId;
    }
    if ($r['method'] === 'POST' && $r['path'] === 'rentals' && $code === 201 && isset($res['json']['data']['id'])) {
        $dynamicRentalId = (int) $res['json']['data']['id'];
    }
    if ($r['method'] === 'POST' && $r['path'] === 'users' && $code === 201 && isset($res['json']['data']['id'])) {
        $dynamicManagedUserId = (int) $res['json']['data']['id'];
    }

    $rows[] = [
        'name' => $r['name'],
        'method' => $r['method'],
        'path' => $path.($r['query'] !== [] ? '?'.http_build_query($r['query']) : ''),
        'code' => $code,
    ];
}

$nameLens = array_map(strlen(...), array_column($rows, 'name'));
$pathLens = array_map(strlen(...), array_column($rows, 'path'));
$wName = max(8, ...$nameLens);
$wPath = max(12, ...$pathLens);

echo "Base: {$baseUrl}\n\n";
printf("%-{$wName}s %-7s %-{$wPath}s %s\n", 'Request', 'Method', 'Path', 'HTTP');
echo str_repeat('-', $wName + $wPath + 20)."\n";
foreach ($rows as $row) {
    printf("%-{$wName}s %-7s %-{$wPath}s %d\n", $row['name'], $row['method'], $row['path'], $row['code']);
}

exit($had5xx ? 1 : 0);
