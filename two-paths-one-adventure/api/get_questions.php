<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
requireMethod('GET');

$pdo = db();

$questions = $pdo->query(
    'SELECT id, question_key, eyebrow, title, description, step_order
     FROM doc_proj_questions
     WHERE is_active = 1
     ORDER BY step_order ASC, id ASC'
)->fetchAll();

$optionStatement = $pdo->prepare(
    'SELECT id, question_id, option_key, title, description, summary_fragment, icon_name, option_order
     FROM doc_proj_options
     WHERE question_id = :question_id
       AND is_active = 1
     ORDER BY option_order ASC, id ASC'
);

foreach ($questions as &$question) {
    $optionStatement->execute(['question_id' => $question['id']]);
    $question['options'] = $optionStatement->fetchAll();
}
unset($question);

jsonResponse([
    'success' => true,
    'questions' => $questions
]);
