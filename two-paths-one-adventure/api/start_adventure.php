<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
requireMethod('POST');

$data = inputJson();
$guestName = cleanText($data['guest_name'] ?? '', 60);
$theme = ($data['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';

if (mb_strlen($guestName) < 2) {
    jsonResponse(['success' => false, 'message' => 'Please enter a valid name.'], 422);
}

$adventureCode = createUuidV4();
$pdo = db();

$statement = $pdo->prepare(
    'INSERT INTO doc_proj_adventures
        (adventure_code, guest_name, theme, status, started_at)
     VALUES
        (:adventure_code, :guest_name, :theme, "started", NOW())'
);

$statement->execute([
    'adventure_code' => $adventureCode,
    'guest_name' => $guestName,
    'theme' => $theme
]);

jsonResponse([
    'success' => true,
    'adventure_code' => $adventureCode,
    'guest_name' => $guestName
], 201);
