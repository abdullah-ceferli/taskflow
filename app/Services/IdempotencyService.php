<?php

namespace App\Services;

use App\Exceptions\IdempotencyConflict;
use App\Models\IdempotencyRecord;
use App\Models\User;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Throwable;

final class IdempotencyService
{
    public function __construct(private readonly CurrentWorkspace $current) {}

    /** @param Closure(): JsonResponse $operation */
    public function execute(Request $request, User $actor, Closure $operation): JsonResponse
    {
        $key = trim((string) $request->header('Idempotency-Key'));
        if ($key === '') {
            return $operation();
        }
        if (mb_strlen($key) > 100 || ! preg_match('/^[A-Za-z0-9._:-]+$/', $key)) {
            throw ValidationException::withMessages(['idempotency_key' => ['Use at most 100 letters, numbers, dots, underscores, colons, or dashes.']]);
        }

        $workspaceId = $this->current->idFor($actor);
        if (! $workspaceId) {
            throw new IdempotencyConflict('A current workspace is required.');
        }
        $fingerprint = hash('sha256', $request->method().'|'.$request->path().'|'.json_encode(Arr::sortRecursive($request->all()), JSON_THROW_ON_ERROR));
        $record = IdempotencyRecord::query()->where('workspace_id', $workspaceId)->where('user_id', $actor->id)->where('key', $key)->first();
        if ($record) {
            return $this->replay($record, $fingerprint);
        }

        try {
            $record = IdempotencyRecord::query()->create(['workspace_id' => $workspaceId, 'user_id' => $actor->id, 'key' => $key, 'request_fingerprint' => $fingerprint, 'expires_at' => now()->addDay()]);
        } catch (UniqueConstraintViolationException) {
            $record = IdempotencyRecord::query()->where('workspace_id', $workspaceId)->where('user_id', $actor->id)->where('key', $key)->firstOrFail();

            return $this->replay($record, $fingerprint);
        }

        try {
            $response = $operation();
            $record->update(['response_status' => $response->getStatusCode(), 'response_body' => json_encode($response->getData(true), JSON_THROW_ON_ERROR)]);

            return $response->header('Idempotency-Replayed', 'false');
        } catch (Throwable $exception) {
            $record->delete();

            throw $exception;
        }
    }

    private function replay(IdempotencyRecord $record, string $fingerprint): JsonResponse
    {
        if (! hash_equals($record->request_fingerprint, $fingerprint)) {
            throw new IdempotencyConflict('The idempotency key was already used with a different request.');
        }
        if ($record->response_body === null || $record->response_status === null) {
            throw new IdempotencyConflict('The original idempotent request is still processing.');
        }

        return response()->json(json_decode($record->response_body, true, 512, JSON_THROW_ON_ERROR), $record->response_status, ['Idempotency-Replayed' => 'true']);
    }
}
