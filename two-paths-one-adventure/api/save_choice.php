<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
requireMethod('POST');

$data = inputJson();
$adventureCode = cleanText($data['adventure_code'] ?? '', 36);
$questionId = filter_var($data['question_id'] ?? null, FILTER_VALIDATE_INT);
$optionId = filter_var($data['option_id'] ?? null, FILTER_VALIDATE_INT);

if (!$questionId || !$optionId) {
    jsonResponse(['success' => false, 'message' => 'Please choose a valid option.'], 422);
}

$pdo = db();
$adventure = findAdventure($pdo, $adventureCode);

$optionStatement = $pdo->prepare(
    'SELECT o.id, o.question_id
     FROM doc_proj_options o
     INNER JOIN doc_proj_questions q ON q.id = o.question_id
     WHERE o.id = :option_id
       AND o.question_id = :question_id
       AND o.is_active = 1
       AND q.is_active = 1
     LIMIT 1'
);
$optionStatement->execute([
    'option_id' => $optionId,
    'question_id' => $questionId
]);

if (!$optionStatement->fetch()) {
    jsonResponse(['success' => false, 'message' => 'This option is not available.'], 422);
}

$statement = $pdo->prepare(
    'INSERT INTO doc_proj_adventure_choices
        (adventure_id, question_id, option_id, selected_at)
     VALUES
        (:adventure_id, :question_id, :option_id, NOW())
     ON DUPLICATE KEY UPDATE
        option_id = VALUES(option_id),
        selected_at = NOW()'
);

$statement->execute([
    'adventure_id' => $adventure['id'],
    'question_id' => $questionId,
    'option_id' => $optionId
]);

$update = $pdo->prepare(
    'UPDATE doc_proj_adventures
     SET status = "in_progress", updated_at = NOW()
     WHERE id = :id'
);
$update->execute(['id' => $adventure['id']]);

jsonResponse(['success' => true]);
