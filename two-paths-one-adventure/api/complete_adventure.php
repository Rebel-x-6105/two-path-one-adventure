<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
requireMethod('POST');

$data = inputJson();
$adventureCode = cleanText($data['adventure_code'] ?? '', 36);
$pdo = db();
$adventure = findAdventure($pdo, $adventureCode);

$questionCount = (int) $pdo->query(
    'SELECT COUNT(*) FROM doc_proj_questions WHERE is_active = 1'
)->fetchColumn();

$statement = $pdo->prepare(
    'SELECT
        q.question_key,
        q.step_order,
        o.title,
        o.summary_fragment,
        o.icon_name
     FROM doc_proj_adventure_choices ac
     INNER JOIN doc_proj_questions q ON q.id = ac.question_id
     INNER JOIN doc_proj_options o ON o.id = ac.option_id
     WHERE ac.adventure_id = :adventure_id
       AND q.is_active = 1
       AND o.is_active = 1
     ORDER BY q.step_order ASC'
);
$statement->execute(['adventure_id' => $adventure['id']]);
$choices = $statement->fetchAll();

if (count($choices) !== $questionCount) {
    jsonResponse([
        'success' => false,
        'message' => 'Please complete every choice before opening the final route.'
    ], 422);
}

$fragments = [];
foreach ($choices as $choice) {
    $fragments[$choice['question_key']] = $choice['summary_fragment'];
}

$requiredKeys = ['mood', 'starting_point', 'main_activity', 'food_choice', 'ending'];
foreach ($requiredKeys as $requiredKey) {
    if (!isset($fragments[$requiredKey])) {
        jsonResponse(['success' => false, 'message' => 'One part of the route is missing.'], 422);
    }
}

$summary = sprintf(
    'Your perfect adventure begins with %s. The day carries %s, continues with %s, pauses for %s, and ends with %s.',
    $fragments['starting_point'],
    $fragments['mood'],
    $fragments['main_activity'],
    $fragments['food_choice'],
    $fragments['ending']
);

$update = $pdo->prepare(
    'UPDATE doc_proj_adventures
     SET final_summary = :final_summary,
         status = "completed",
         completed_at = NOW(),
         updated_at = NOW()
     WHERE id = :id'
);
$update->execute([
    'final_summary' => $summary,
    'id' => $adventure['id']
]);

jsonResponse([
    'success' => true,
    'summary' => $summary,
    'choices' => array_map(static fn(array $choice): array => [
        'title' => $choice['title'],
        'icon_name' => $choice['icon_name']
    ], $choices)
]);
