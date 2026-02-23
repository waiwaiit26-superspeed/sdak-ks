<?php
namespace App\Core;

/**
 * JSON Response Helper
 */
class Response
{
    public static function success($data = null, string $message = 'สำเร็จ', int $code = 200): void
    {
        http_response_code($code);
        self::setHeaders();
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error(string $message = 'เกิดข้อผิดพลาด', int $code = 400, $errors = null): void
    {
        http_response_code($code);
        self::setHeaders();
        $res = ['success' => false, 'message' => $message];
        if ($errors !== null) $res['errors'] = $errors;
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function paginated(array $data, int $total, int $page, int $perPage): void
    {
        http_response_code(200);
        self::setHeaders();
        echo json_encode([
            'success' => true,
            'message' => 'สำเร็จ',
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int)ceil($total / $perPage)
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function getInput(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        return $input ?? [];
    }

    private static function setHeaders(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Auth-Token');
    }

    public static function handleCORS(): void
    {
        self::setHeaders();
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
