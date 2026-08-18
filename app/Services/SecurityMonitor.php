<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class SecurityMonitor
{
    public function authenticationFailure(string $channel, string $email, ?Request $request = null): void
    {
        Log::channel('structured')->warning('taskflow.authentication_failed', [
            'channel' => $channel,
            'identity_hash' => hash('sha256', mb_strtolower(trim($email))),
            'ip' => $request?->ip(),
            'correlation_id' => $request?->attributes->get('correlation_id'),
        ]);
    }
}
