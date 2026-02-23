<?php
namespace App\Core;

/**
 * Base Controller
 * Every controller extends this. It provides helpers for auth, input, and model loading.
 */
abstract class Controller
{
    protected ?array $currentUser = null;

    /**
     * Load a model by class name (cached per request)
     */
    protected function model(string $className)
    {
        static $cache = [];
        $fqcn = "App\\Models\\{$className}";
        if (!isset($cache[$fqcn])) {
            $cache[$fqcn] = new $fqcn();
        }
        return $cache[$fqcn];
    }

    /**
     * Set current authenticated user (injected by Router)
     */
    public function setUser(?array $user): void
    {
        $this->currentUser = $user;
    }

    /**
     * Get JSON body input
     */
    protected function input(): array
    {
        return Response::getInput();
    }

    /**
     * Get query parameter
     */
    protected function query(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Require POST method
     */
    protected function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
        }
    }

    /**
     * Pagination helpers
     */
    protected function getPage(): int
    {
        return max(1, (int)($this->query('page', 1)));
    }

    protected function getPerPage(int $max = 100): int
    {
        return min($max, max(1, (int)($this->query('per_page', 20))));
    }
}
