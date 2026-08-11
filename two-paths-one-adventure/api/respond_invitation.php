<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
requireMethod('POST');

$data = inputJson();
$adventureCode = cleanText($data['adventure_code'] ?? '', 36);
$response = cleanText($data['response'] ?? '', 20);

if (!in_array($response, ['accepted', 'restart'], true)) {
    jsonResponse(['success' => false, 'message' => 'Invalid response.'], 422);
}

$pdo = db();
$adventure = findAdventure($pdo, $adventureCode);

$pdo->beginTransaction();

try {
    $insert = $pdo->prepare(
        'INSERT INTO doc_proj_invitation_responses
            (adventure_id, response, responded_at)
         VALUES
            (:adventure_id, :response, NOW())'
    );
    $insert->execute([
        'adventure_id' => $adventure['id'],
        'response' => $response
    ]);

    $status = $response === 'accepted' ? 'accepted' : 'restarted';
    $update = $pdo->prepare(
        'UPDATE doc_proj_adventures
         SET status = :status, updated_at = NOW()
         WHERE id = :id'
    );
    $update->execute([
        'status' => $status,
        'id' => $adventure['id']
    ]);

    $pdo->commit();
} catch (Throwable $exception) {
    $pdo->rollBack();
    jsonResponse(['success' => false, 'message' => 'The response could not be saved.'], 500);
}

jsonResponse(['success' => true]);
