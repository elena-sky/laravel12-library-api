<?php

/**
 * Builds docs/postman/Library_API.postman_collection.prefilled.json from the
 * OpenAPI-generated collection: demo variables, sample JSON bodies, sane URL
 * :params, and GET query noise disabled where placeholders were used.
 */
declare(strict_types=1);

$src = dirname(__DIR__).'/docs/postman/Library_API.postman_collection.json';
$dst = dirname(__DIR__).'/docs/postman/Library_API.postman_collection.prefilled.json';

if (! is_file($src)) {
    fwrite(STDERR, "Missing {$src}; run composer postman:generate first.\n");
    exit(1);
}

/** @var array<string, mixed> $json */
$json = json_decode(file_get_contents($src), true, 512, JSON_THROW_ON_ERROR);

$json['info']['name'] = 'Library API (prefilled)';
$json['info']['description'] = [
    'content' => implode("\n", [
        '**Ready-to-send defaults** for local development.',
        '',
        '1. **Register** or **Login** (folder `register` / `login`) — bodies use `{{demoName}}`, `{{demoEmail}}`, `{{demoPassword}}`.',
        '2. Copy `data.token` from the response into collection variable **`bearerToken`** (no `Bearer ` prefix).',
        '3. Call protected routes (`books`, `users`, `rentals`, …).',
        '',
        'Adjust **`baseUrl`** if the app is not on port 8000. Regenerate the base collection with `composer postman:generate`, then re-run `php scripts/postman_build_prefilled.php` (or use the same Composer script).',
    ]),
    'type' => 'text/markdown',
];

$json['variable'] = [
    ['key' => 'baseUrl', 'value' => 'http://localhost:8000/api/v1'],
    ['key' => 'bearerToken', 'value' => ''],
    ['key' => 'demoName', 'value' => 'Demo Reader'],
    ['key' => 'demoEmail', 'value' => 'demo.reader@example.com'],
    ['key' => 'demoPassword', 'value' => 'Password123!'],
    ['key' => 'rentalBookId', 'value' => '1'],
    ['key' => 'rentalDueDate', 'value' => '2099-12-31T12:00:00Z'],
];

$bodyBySignature = [
    'POST/register' => json_encode([
        'name' => '{{demoName}}',
        'email' => '{{demoEmail}}',
        'password' => '{{demoPassword}}',
        'password_confirmation' => '{{demoPassword}}',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    'POST/login' => json_encode([
        'email' => '{{demoEmail}}',
        'password' => '{{demoPassword}}',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    'POST/books' => json_encode([
        'title' => 'The Prefilled Book',
        'author' => 'Jane Demo',
        'genre' => 'Fiction',
        'total_copies' => 3,
        'description' => 'Sample body from prefilled Postman collection.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    // book_id must stay unquoted so Postman substitutes {{rentalBookId}} as a number
    'POST/rentals' => "{\n  \"book_id\": {{rentalBookId}},\n  \"due_date\": \"{{rentalDueDate}}\"\n}",
    'POST/users' => json_encode([
        'name' => 'Managed User',
        'email' => 'managed.user@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    'PATCH/user' => json_encode([
        'name' => 'Updated display name',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    'PUT/user/password' => json_encode([
        'current_password' => '{{demoPassword}}',
        'password' => 'NewPassword456!',
        'password_confirmation' => 'NewPassword456!',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    'PATCH/books/:book' => json_encode([
        'title' => 'Updated title (prefilled)',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    'PATCH/users/:user' => json_encode([
        'name' => 'Patched managed user name',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    'PATCH/rentals/:bookRent/extend' => json_encode([
        'due_date' => '2099-12-31T23:59:59Z',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
    'PATCH/rentals/:bookRent/reading-progress' => json_encode([
        'reading_progress' => 42,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
];

/**
 * @param  array<int, mixed>  $nodes
 */
function walkItems(array &$nodes, array $bodyBySignature): void
{
    foreach ($nodes as &$node) {
        if (isset($node['request']) && is_array($node['request'])) {
            prefillRequest($node['request'], $bodyBySignature);
        }
        if (isset($node['item']) && is_array($node['item'])) {
            walkItems($node['item'], $bodyBySignature);
        }
    }
}

/**
 * @param  array<string, mixed>  $request
 * @param  array<string, string>  $bodyBySignature
 */
function prefillRequest(array &$request, array $bodyBySignature): void
{
    $method = $request['method'] ?? '';
    $path = $request['url']['path'] ?? [];
    if (! is_array($path)) {
        return;
    }

    $sig = $method.'/'.implode('/', $path);

    if (isset($request['url']['variable']) && is_array($request['url']['variable'])) {
        foreach ($request['url']['variable'] as &$var) {
            if (! is_array($var) || ! isset($var['key'])) {
                continue;
            }
            $val = $var['value'] ?? '';
            if (is_string($val) && (str_starts_with($val, '<') || $val === '')) {
                $var['value'] = match ($var['key']) {
                    'book', 'user', 'bookRent' => '1',
                    default => '1',
                };
            }
        }
        unset($var);
    }

    if (($request['body']['mode'] ?? '') === 'raw' && isset($bodyBySignature[$sig])) {
        $request['body']['raw'] = $bodyBySignature[$sig];
    }

    if ($method === 'GET' && isset($request['url']['query']) && is_array($request['url']['query'])) {
        $simplifyList = match (true) {
            $path === ['books'] => ['title', 'author', 'genre', 'available_only'],
            $path === ['users'] => [],
            $path === ['rentals'] => [],
            default => null,
        };
        if ($simplifyList !== null) {
            foreach ($request['url']['query'] as &$q) {
                if (! is_array($q) || ! isset($q['key'])) {
                    continue;
                }
                $v = $q['value'] ?? '';
                $isPlaceholder = is_string($v) && str_starts_with($v, '<') && str_ends_with($v, '>');
                if ($path === ['books'] && $isPlaceholder && in_array($q['key'], $simplifyList, true)) {
                    $q['disabled'] = true;

                    continue;
                }
                if ($isPlaceholder && $q['key'] === 'page') {
                    $q['value'] = '1';
                    $q['disabled'] = false;
                }
                if ($isPlaceholder && $q['key'] === 'per_page') {
                    $q['value'] = '15';
                    $q['disabled'] = false;
                }
            }
            unset($q);
        }
    }
}

walkItems($json['item'], $bodyBySignature);

file_put_contents(
    $dst,
    json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n"
);

echo "Wrote prefilled collection: {$dst}\n";
