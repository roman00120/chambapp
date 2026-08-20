<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AdminAuditService
{
    public function record(User $admin, string $action, ?Model $subject = null, array $metadata = [], ?Request $request = null): AdminAuditLog
    {
        return AdminAuditLog::create([
            'admin_id' => $admin->getKey(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'metadata' => $this->sanitize($metadata),
            'ip_address' => $request?->ip() ?? request()->ip(),
        ]);
    }

    private function sanitize(array $metadata): array
    {
        $blocked = ['token', 'secret', 'password', 'cvv', 'card', 'authorization'];

        return collect($metadata)->reject(function ($value, $key) use ($blocked): bool {
            return collect($blocked)->contains(fn (string $term): bool => str_contains(strtolower((string) $key), $term));
        })->map(function ($value) {
            if (is_array($value)) {
                return $this->sanitize($value);
            }

            return is_scalar($value) || $value === null ? $value : (string) $value;
        })->all();
    }
}
