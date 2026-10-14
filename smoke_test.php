<?php
/**
 * Smoke-test suite for UserController attribute routes.
 *
 * Usage:
 *   1. Start the dev server from the project root:
 *        php8.3 -S localhost:8000 -t public/
 *
 *   2. In another terminal:
 *        php8.3 tests/users_smoke_test.php
 *
 *      Or point at a different host:
 *        BASE_URL=http://localhost:8080 php8.3 tests/users_smoke_test.php
 *
 * The script reseeds runtime/users_db.json before running so the
 * suite is fully idempotent — safe to run multiple times in a row.
 */

// ---------------------------------------------------------------------------
// Config
// ---------------------------------------------------------------------------

$BASE     = rtrim(getenv('BASE_URL') ?: 'http://localhost:3000', '/') . '/users';
$DB_PATH  = __DIR__ . '/../runtime/users_db.json';
$DB_SEED  = [
    'next_id' => 4,
    'rows'    => [
        '1' => ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com', 'role' => 'admin'],
        '2' => ['id' => 2, 'name' => 'Bob',   'email' => 'bob@example.com',   'role' => 'user'],
        '3' => ['id' => 3, 'name' => 'Carol', 'email' => 'carol@example.com', 'role' => 'user'],
    ],
];

// Reseed so every run starts from a known state.
file_put_contents($DB_PATH, json_encode($DB_SEED, JSON_PRETTY_PRINT));

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function req(string $method, string $url, ?array $body = null): array
{
    $ch      = curl_init($url);
    $headers = ['Accept: application/json'];

    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => strtoupper($method),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
    ]);

    $raw  = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) {
        echo "\033[31m[CURL ERROR]\033[0m $err\n";
        echo "Is the dev server running?  php8.3 -S localhost:8000 -t public/\n";
        exit(2);
    }

    return [
        'code' => $code,
        'body' => json_decode($raw ?: '{}', true) ?? [],
    ];
}

$pass = $fail = 0;
$log  = [];

function check(string $label, bool $ok, string $detail = ''): void
{
    global $pass, $fail, $log;
    if ($ok) {
        $pass++;
        $log[] = "  \033[32m✓\033[0m  $label";
    } else {
        $fail++;
        $log[] = "  \033[31m✗\033[0m  $label" . ($detail ? "\n       ↳ $detail" : '');
    }
}

// ---------------------------------------------------------------------------
// Suite
// ---------------------------------------------------------------------------

echo "\n\033[1mUserController — smoke tests\033[0m\n";
echo str_repeat('─', 55) . "\n";

// 1 ── GET /users  →  200, non-empty data array
$r = req('GET', "$BASE/");
check(
    'GET /users — 200 with data array',
    $r['code'] === 200
        && ($r['body']['success'] ?? false) === true
        && is_array($r['body']['data'] ?? null)
        && count($r['body']['data']) > 0,
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// 2 ── GET /users/1  →  200, id=1
$r = req('GET', "$BASE/1");
check(
    'GET /users/1 — 200 with correct user',
    $r['code'] === 200 && ($r['body']['data']['id'] ?? null) === 1,
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// 3 ── GET /users/999  →  404
$r = req('GET', "$BASE/999");
check(
    'GET /users/999 — 404 not found',
    $r['code'] === 404 && ($r['body']['success'] ?? true) === false,
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// 4 ── POST /users — valid payload  →  201
$r      = req('POST', "$BASE/", ['name' => 'Dave', 'email' => 'dave@example.com']);
$new_id = $r['body']['data']['id'] ?? null;
check(
    'POST /users — 201 created with id',
    $r['code'] === 201 && $new_id !== null,
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// 5 ── POST /users — empty name  →  422
$r = req('POST', "$BASE/", ['name' => '', 'email' => 'x@x.com']);
check(
    'POST /users — 422 on empty name',
    $r['code'] === 422 && isset($r['body']['errors']['name']),
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// 6 ── POST /users — bad email format  →  422
$r = req('POST', "$BASE/", ['name' => 'Eve', 'email' => 'not-an-email']);
check(
    'POST /users — 422 on invalid email format',
    $r['code'] === 422 && isset($r['body']['errors']['email']),
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// 7 ── POST /users — duplicate email  →  422
$r = req('POST', "$BASE/", ['name' => 'Dup', 'email' => 'alice@example.com']);
check(
    'POST /users — 422 on duplicate email',
    $r['code'] === 422 && isset($r['body']['errors']['email']),
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// 8 ── PUT /users/1 — full update  →  200, name changed
$r = req('PUT', "$BASE/1", ['name' => 'Alice Updated', 'email' => 'alice2@example.com', 'role' => 'admin']);
check(
    'PUT /users/1 — 200 full update',
    $r['code'] === 200 && ($r['body']['data']['name'] ?? '') === 'Alice Updated',
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// 9 ── PUT /users/1 — missing role  →  422
$r = req('PUT', "$BASE/1", ['name' => 'Alice', 'email' => 'alice2@example.com']); // no role
check(
    'PUT /users/1 — 422 missing role',
    $r['code'] === 422 && isset($r['body']['errors']['role']),
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// 10 ── PATCH /users/2 — partial update  →  200, email changed
$r = req('PATCH', "$BASE/2", ['email' => 'bob-new@example.com']);
check(
    'PATCH /users/2 — 200 partial update',
    $r['code'] === 200 && ($r['body']['data']['email'] ?? '') === 'bob-new@example.com',
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// 11 ── PATCH /users/2 — empty body  →  422
$r = req('PATCH', "$BASE/2", []);
check(
    'PATCH /users/2 — 422 on empty body',
    $r['code'] === 422,
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// 12 ── PATCH /users/999 — not found  →  404
$r = req('PATCH', "$BASE/999", ['name' => 'Ghost']);
check(
    'PATCH /users/999 — 404 not found',
    $r['code'] === 404,
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// 13 ── DELETE /users/{new_id}  →  200  (user from test 4)
if ($new_id !== null) {
    $r = req('DELETE', "$BASE/$new_id");
    check(
        "DELETE /users/$new_id — 200 deleted",
        $r['code'] === 200 && ($r['body']['success'] ?? false) === true,
        "HTTP {$r['code']} | " . json_encode($r['body'])
    );
} else {
    check('DELETE /users/{new_id} — skipped (creation failed)', false, 'user was never created in test 4');
}

// 14 ── DELETE /users/999  →  404
$r = req('DELETE', "$BASE/999");
check(
    'DELETE /users/999 — 404 not found',
    $r['code'] === 404,
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// 15 ── GET /users/1 — name must reflect the PUT from test 8
$r = req('GET', "$BASE/1");
check(
    'GET /users/1 — name persisted after PUT',
    $r['code'] === 200 && ($r['body']['data']['name'] ?? '') === 'Alice Updated',
    "HTTP {$r['code']} | " . json_encode($r['body'])
);

// ---------------------------------------------------------------------------
// Report
// ---------------------------------------------------------------------------

echo "\n";
foreach ($log as $line) {
    echo $line . "\n";
}

$total  = $pass + $fail;
$colour = $fail === 0 ? "\033[32m" : "\033[31m";
echo "\n" . str_repeat('─', 55) . "\n";
echo "{$colour}Results: $pass / $total passed";
if ($fail > 0) {
    echo " | $fail FAILED";
}
echo "\033[0m\n\n";

exit($fail > 0 ? 1 : 0);