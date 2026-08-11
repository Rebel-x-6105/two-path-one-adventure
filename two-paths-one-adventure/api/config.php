<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'doctor_project';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('TPOA_DB_HOST') ?: DB_HOST;
    $port = getenv('TPOA_DB_PORT') ?: DB_PORT;
    $name = getenv('TPOA_DB_NAME') ?: DB_NAME;
    $user = getenv('TPOA_DB_USER') ?: DB_USER;
    $pass = getenv('TPOA_DB_PASS') !== false ? (string) getenv('TPOA_DB_PASS') : DB_PASS;

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        jsonResponse([
            'success' => false,
            'message' => 'Database connection failed. Import the SQL file and check api/config.php.'
        ], 500);
    }

    return $pdo;
}

function inputJson(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        jsonResponse(['success' => false, 'message' => 'Invalid request data.'], 400);
    }

    return $data;
}

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requireMethod(string $method): void
{
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method)) {
        jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
    }
}

function cleanText(mixed $value, int $maxLength): string
{
    $text = trim((string) $value);
    $text = preg_replace('/\s+/u', ' ', $text) ?? '';
    return mb_substr($text, 0, $maxLength);
}

function validUuid(string $value): bool
{
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
}

function createUuidV4(): string
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
}

function findAdventure(PDO $pdo, string $adventureCode): array
{
    if (!validUuid($adventureCode)) {
        jsonResponse(['success' => false, 'message' => 'Invalid adventure code.'], 422);
    }

    $statement = $pdo->prepare(
        'SELECT id, adventure_code, guest_name, status
         FROM doc_proj_adventures
         WHERE adventure_code = :adventure_code
         LIMIT 1'
    );
    $statement->execute(['adventure_code' => $adventureCode]);
    $adventure = $statement->fetch();

    if (!$adventure) {
        jsonResponse(['success' => false, 'message' => 'Adventure not found.'], 404);
    }

    return $adventure;
}
