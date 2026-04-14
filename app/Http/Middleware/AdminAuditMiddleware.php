<?php

namespace App\Http\Middleware;

use App\Models\AdminAuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class AdminAuditMiddleware
{
    private static ?bool $hasAuditTable = null;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $this->persistAuditLog($request, $response);

        return $response;
    }

    private function persistAuditLog(Request $request, Response $response): void
    {
        if (! $this->auditTableExists()) {
            return;
        }

        $user = $request->user();
        if (! $user || $user->role !== 'admin') {
            return;
        }

        $routeParams = $request->route()?->parameters() ?? [];
        $payload = $request->except(['password', 'password_confirmation']);

        AdminAuditLog::create([
            'user_id' => $user->id,
            'action' => $this->resolveAction($request),
            'method' => $request->method(),
            'path' => $request->path(),
            'entity_type' => $this->resolveEntityType($request->path()),
            'entity_id' => $this->resolveEntityId($routeParams),
            'status_code' => $response->getStatusCode(),
            'success' => $response->isSuccessful(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $payload,
        ]);
    }

    private function resolveAction(Request $request): string
    {
        $path = str_replace('/', '.', $request->path());

        return strtolower($request->method()).'.'.$path;
    }

    private function resolveEntityType(string $path): ?string
    {
        $segments = explode('/', $path);

        return $segments[2] ?? null;
    }

    private function resolveEntityId(array $routeParams): ?string
    {
        foreach ($routeParams as $key => $value) {
            if (str_contains((string) $key, 'id') || str_contains((string) $key, '_id')) {
                return (string) $value;
            }
        }

        return null;
    }

    private function auditTableExists(): bool
    {
        if (self::$hasAuditTable !== null) {
            return self::$hasAuditTable;
        }

        self::$hasAuditTable = Schema::hasTable('admin_audit_logs');

        return self::$hasAuditTable;
    }
}
